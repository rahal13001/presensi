<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DailyOtherWork;
use App\Models\DailyReportV2;
use App\Models\DailyScopeCheck;
use App\Models\MonthlyReportV2;
use App\Models\Scope;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

class MonthlyReportPdfController extends Controller
{
    /**
     * Indonesian month names (1-indexed).
     */
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function download($id)
    {
        $report = MonthlyReportV2::with(['user.position', 'teamLeader', 'photos.dailyPhoto'])
            ->findOrFail($id);
            
        // Get dynamic position name (default to 'PRAMUBAKTI' if null)
        $positionName = $report->user->position->position_name ?? 'PRAMUBAKTI';
        
        // Get Team Name and Team Leader Name from the report record
        $teamName = $report->team_name ?? '............';
        $teamLeaderName = $report->team_leader_name ?? '........................';

        $month = (int) $report->month;
        $year  = (int) $report->year;
        $userId = $report->user_id;
        $monthName = self::MONTH_NAMES[$month] ?? $month;

        // ── 1. Build day columns ─────────────────────────────────────
        $dailyReports = DailyReportV2::where('user_id', $userId)
            ->whereMonth('report_date', $month)
            ->whereYear('report_date', $year)
            ->get()
            ->keyBy(fn ($r) => (int) Carbon::parse($r->report_date)->day);

        $reportDates = $dailyReports->keys()->toArray();

        // All weekdays (Mon-Fri) in the month
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $dayColumns = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create($year, $month, $d);
            $isWeekend = $date->isWeekend();

            if (!$isWeekend) {
                // Always include weekdays
                $dayColumns[] = $d;
            } elseif (in_array($d, $reportDates)) {
                // Include weekend days only if a daily report exists
                $dayColumns[] = $d;
            }
        }

        sort($dayColumns);

        // ── 2. Determine cell colors ─────────────────────────────────
        $attendances = Attendance::where('user_id', $userId)
            ->whereMonth('start_date', $month)
            ->whereYear('start_date', $year)
            ->get()
            ->keyBy(fn ($a) => (int) Carbon::parse($a->start_date)->day);

        $dayColors = [];
        foreach ($dayColumns as $d) {
            $hasReport     = isset($dailyReports[$d]);
            $hasAttendance = isset($attendances[$d]);

            if ($hasReport) {
                $dayColors[$d] = '#FFFFFF'; // white
            } elseif ($hasAttendance) {
                $dayColors[$d] = '#FFFFCC'; // yellow
            } else {
                $dayColors[$d] = '#FFCCCC'; // red
            }
        }

        // ── 3. Group days into weeks ─────────────────────────────────
        $weeks = [];
        $weekLabels = ['Ke-1', 'Ke-2', 'Ke-3', 'Ke-4', 'Ke-5'];
        $weekRanges = [[1, 7], [8, 14], [15, 21], [22, 28], [29, 31]];

        foreach ($weekRanges as $i => [$start, $end]) {
            $weekDays = array_filter($dayColumns, fn ($d) => $d >= $start && $d <= $end);
            if (!empty($weekDays)) {
                $weeks[] = [
                    'label' => $weekLabels[$i],
                    'days'  => array_values($weekDays),
                ];
            }
        }

        // ── 4. Build scope × day matrix ──────────────────────────────
        $dailyReportIds = $dailyReports->pluck('id')->toArray();

        // Get all scope checks for this user's daily reports in this month
        $scopeChecks = DailyScopeCheck::whereIn('daily_report_id', $dailyReportIds)
            ->with('scope')
            ->get();

        // Collect unique scopes (ordered by scope.order if available)
        $scopeIds = $scopeChecks->pluck('scope_id')->unique()->toArray();
        $scopes = Scope::whereIn('id', $scopeIds)->orderBy('order')->get();

        // Build the matrix: $matrix[scopeId][dayNumber] = true/false
        $matrix = [];
        foreach ($scopes as $scope) {
            $matrix[$scope->id] = [];
            foreach ($dayColumns as $d) {
                $matrix[$scope->id][$d] = false;
            }
        }

        foreach ($scopeChecks as $check) {
            if (!$check->is_checked || !$check->scope) {
                continue;
            }
            // Find the day number from the daily report
            $dr = $dailyReports->firstWhere('id', $check->daily_report_id);
            if ($dr) {
                $day = (int) Carbon::parse($dr->report_date)->day;
                if (isset($matrix[$check->scope_id][$day])) {
                    $matrix[$check->scope_id][$day] = true;
                }
            }
        }

        // ── 5. Collect "Lain-lain" (Other Work) ──────────────────────
        $otherWorks = DailyOtherWork::whereIn('daily_report_id', $dailyReportIds)
            ->with('otherWorkOption')
            ->get();

        $otherWorkNames = $otherWorks
            ->pluck('otherWorkOption.name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // ── 6. Convert images to base64 ──────────────────────────────
        $logoPath = public_path('img/logokkp.jpg');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        // Photos (from monthly report selection, ordered)
        $photos = $report->photos()->orderBy('order')->with('dailyPhoto')->get();
        $photoData = [];
        foreach ($photos as $photo) {
            if ($photo->dailyPhoto && $photo->dailyPhoto->photo_path) {
                $photoPath = storage_path('app/public/' . $photo->dailyPhoto->photo_path);
                if (file_exists($photoPath)) {
                    // Fix EXIF orientation — phone cameras embed rotation metadata
                    // that DomPDF ignores, causing portrait photos to appear rotated
                    $imageData = $this->fixExifOrientation($photoPath);
                    $photoData[] = [
                        'base64'  => $imageData['base64'],
                        'mime'    => $imageData['mime'],
                        'caption' => $photo->dailyPhoto->caption ?? '',
                    ];
                }
            }
        }

        // Signatures — already base64 data URIs from SignaturePad
        $employeeSign = $report->employee_sign;
        $leaderSign   = $report->leader_sign;

        // ── 6. Signature Dates ───────────────────────────────────────
        $dateSource = $report->signed_date ?? $report->leader_signed_at ?? now();
        $signDate = \Carbon\Carbon::parse($dateSource);
        
        $signDay = $signDate->day;
        $signMonthName = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$signDate->month] ?? $signDate->month;
        $signYear = $signDate->year;
        
        $city = $report->city ?? 'Sorong';

        // ── 7. Render both PDFs and merge ────────────────────────────
        $data = compact(
            'report', 'month', 'year', 'monthName', 'userId', 'positionName', 'teamName', 'teamLeaderName',
            'city', 'signDay', 'signMonthName', 'signYear',
            'dayColumns', 'dayColors', 'weeks',
            'scopes', 'matrix',
            'otherWorkNames',
            'logoBase64', 'photoData',
            'employeeSign', 'leaderSign'
        );

        // Generate landscape PDF (Page 1)
        $pdf1 = Pdf::loadView('pdf.monthly_report_pdf_p1', $data);
        $pdf1->setPaper('a4', 'landscape');
        $output1 = $pdf1->output();

        // Generate portrait PDF (Page 2)
        $pdf2 = Pdf::loadView('pdf.monthly_report_pdf_p2', $data);
        $pdf2->setPaper('a4', 'portrait');
        $output2 = $pdf2->output();

        // Write to temp files
        $tmp1 = tempnam(sys_get_temp_dir(), 'rpt_p1_') . '.pdf';
        $tmp2 = tempnam(sys_get_temp_dir(), 'rpt_p2_') . '.pdf';
        file_put_contents($tmp1, $output1);
        file_put_contents($tmp2, $output2);

        // Merge using FPDI
        $fpdi = new Fpdi();

        // Import all pages from Page 1 (landscape)
        $pageCount1 = $fpdi->setSourceFile($tmp1);
        for ($i = 1; $i <= $pageCount1; $i++) {
            $tpl = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        // Import all pages from Page 2 (portrait)
        $pageCount2 = $fpdi->setSourceFile($tmp2);
        for ($i = 1; $i <= $pageCount2; $i++) {
            $tpl = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tpl);
            $fpdi->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        // Get merged output
        $merged = $fpdi->Output('S');

        // Clean up temp files
        @unlink($tmp1);
        @unlink($tmp2);

        $filename = "laporan-bulanan-{$report->user->name}-{$monthName}-{$year}.pdf";

        return response($merged, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Read EXIF orientation from a photo and rotate it accordingly using GD.
     * Phone cameras store photos with EXIF metadata indicating rotation,
     * but DomPDF ignores this, causing images to appear rotated in the PDF.
     *
     * @param string $filePath Absolute path to the image file
     * @return array{base64: string, mime: string}
     */
    private function fixExifOrientation(string $filePath): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Only JPEG files have EXIF orientation data
        if (!in_array($ext, ['jpg', 'jpeg'])) {
            $mime = match ($ext) {
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
            return [
                'base64' => base64_encode(file_get_contents($filePath)),
                'mime'   => $mime,
            ];
        }

        // Read EXIF data
        $exif = @exif_read_data($filePath);
        $orientation = $exif['Orientation'] ?? 1;

        // If orientation is normal (1), no processing needed
        if ($orientation === 1) {
            return [
                'base64' => base64_encode(file_get_contents($filePath)),
                'mime'   => 'image/jpeg',
            ];
        }

        // Load image with GD
        $image = @imagecreatefromjpeg($filePath);
        if (!$image) {
            // Fallback: return raw bytes if GD fails
            return [
                'base64' => base64_encode(file_get_contents($filePath)),
                'mime'   => 'image/jpeg',
            ];
        }

        // Apply rotation/flip based on EXIF orientation
        // See: https://exiftool.org/TagNames/EXIF.html (Orientation)
        $image = match ($orientation) {
            2 => tap($image, fn ($img) => imageflip($img, IMG_FLIP_HORIZONTAL)),
            3 => imagerotate($image, 180, 0),
            4 => tap($image, fn ($img) => imageflip($img, IMG_FLIP_VERTICAL)),
            5 => tap(imagerotate($image, -90, 0), fn ($img) => imageflip($img, IMG_FLIP_HORIZONTAL)),
            6 => imagerotate($image, -90, 0),
            7 => tap(imagerotate($image, 90, 0), fn ($img) => imageflip($img, IMG_FLIP_HORIZONTAL)),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        // Output rotated image to buffer as JPEG
        ob_start();
        imagejpeg($image, null, 90);
        $rotatedData = ob_get_clean();
        imagedestroy($image);

        return [
            'base64' => base64_encode($rotatedData),
            'mime'   => 'image/jpeg',
        ];
    }
}
