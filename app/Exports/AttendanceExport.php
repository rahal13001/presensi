<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Attendance;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    protected $selectedIds;

    public function __construct(array $selectedIds)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        return Attendance::whereIn('id', $this->selectedIds)->with('user', 'position', 'dailyreports')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama',
            'NIK / NIP',
            'Jabatan',
            'Jadwal Masuk',
            'Jam Masuk',
            'Jadwal Pulang',
            'Jam Pulang',
            'Latitude Masuk',
            'Longitude Masuk',
            'Latitude Pulang',
            'Longitude Pulang',
            'Akurasi Masuk',
            'Akurasi Pulang',
            'Durasi Kerja',
            'Durasi Terlambat',
            'Cuti',
            'Kehadiran',
            'Laporan Harian',
        ];
    }

    public function map($record): array
    {
        return [
            $record->start_date ? Carbon::parse($record->start_date)->format('Y-m-d') : '',
            $record->user->name ?? '',
            // isset($record->user->idnumber) ? "'".$record->user->idnumber : '',
            (string) ($record->user->idnumber ?? ''),
            $record->position->position_name ?? '',
            $record->schedule_start_time ?? '',
            $record->start_time ?? '',
            $record->schedule_end_time ?? '',
            $record->end_time ?? '',
            $record->start_latitude ?? '',
            $record->start_longitude ?? '',
            $record->end_latitude ?? '',
            $record->end_longitude ?? '',
            $record->start_accuracy ?? '',
            $record->end_accuracy ?? '',
            $this->calculateWorkDuration($record),
            $this->calculateLateDuration($record),
            $record->is_leave ? 'Ya' : 'Tidak',
            $this->isPresent($record),
            $this->dailyReport($record),
        ];
    }

    protected function dailyReport($record)
    {
        if ($record->dailyreports->isNotEmpty()) {
            return $record->dailyreports
                ->take(10) // ✅ Limit maximum links to 10
                ->map(fn($dailyreport) => "https://presensi.timurbersinar.com/laporanharian/" . $dailyreport->id . "/" . Str::slug($dailyreport->title))
                ->implode(', '); // ✅ Separate by commas
        }
        return ''; // ✅ Return empty string if no reports
    }

    protected function isPresent($record)
    {
        if ($record->is_leave) {
            return "Cuti";
        }
        elseif ($record->not_present) {
            return "Tidak Hadir";
        } else{
            return "Hadir";
        }
    }

    protected function calculateWorkDuration($record)
    {
        if (is_null($record->start_time) || is_null($record->end_time)) {
            return "0 jam 0 menit";
        }

        $startTime = Carbon::parse($record->start_time);
        $endTime = Carbon::parse($record->end_time);

        if ($endTime->lt($startTime)) {
            $endTime->addDay();  // Handle overnight shifts
        }

        $duration = $startTime->diff($endTime);
        $hours = $duration->h + ($duration->d * 24);
        $minutes = $duration->i;

        return "{$hours} jam {$minutes} menit";
    }

    protected function calculateLateDuration($record)
    {
        if (is_null($record->schedule_start_time) || is_null($record->start_time)) {
            return "0 jam 0 menit";
        }

        $scheduleStartTime = Carbon::parse($record->schedule_start_time);
        $startTime = Carbon::parse($record->start_time);

        if ($startTime->lte($scheduleStartTime)) {
            return "0 jam 0 menit";
        }

        $duration = $scheduleStartTime->diff($startTime);
        $hours = $duration->h + ($duration->d * 24);
        $minutes = $duration->i;

        return "{$hours} jam {$minutes} menit";
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                //group data by user
                $data = $this->collection()->groupBy('user_id')
                ->map(function ($records) {
                    // Sort each user's records by start_date in ascending order
                    return $records->sortBy('start_date');
                });


                $row = 2; // Start below the headings
                foreach ($data as $userId => $records) {
                    $userName = $records->first()->user->name ?? 'Unknown';
                    
                    // Insert group header
                    $sheet->setCellValue("A{$row}", "Pegawai: {$userName}");
                    $sheet->mergeCells("A{$row}:S{$row}");
                    $sheet->getStyle("A{$row}:S{$row}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'D9E1F2'],
                        ],
                    ]);
                    $row++;

                    // Insert each record for the user
                    foreach ($records as $record) {
                        $mappedRecord = $this->map($record);
                        $col = 'A';
                        foreach ($mappedRecord as $value) {
                            $sheet->setCellValue("{$col}{$row}", $value);
                            $col++;
                        }
                        $row++;
                    }

                    $row++; // Add an empty row between groups
                }
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT, // ✅ Force column B (idnumber) to be text
        ];
    }
}
