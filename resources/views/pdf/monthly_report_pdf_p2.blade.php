<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan - Page 2</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 2cm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
        }
        /* Header */
        .header-table {
            width: 100%;
            border: none;
            margin-bottom: 10px;
        }
        .header-table td {
            border: none;
            vertical-align: middle;
        }
        .header-title {
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
            text-align: center;
        }
        .header-subtitle {
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            text-align: center;
        }
        /* Section titles */
        .section-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            font-size: 11px;
            margin: 10px 0 8px 0;
        }
        /* Page 2 styles */
        .condition-section {
            font-size: 11px;
            margin: 15px 0;
            line-height: 2;
        }
        .notes-box {
            border: 1px solid #000;
            min-height: 200px;
            padding: 10px;
            font-size: 11px;
        }
        .signature-table {
            width: 100%;
            border: none;
            margin-top: 20px;
        }
        .signature-table td {
            border: none;
            text-align: center;
            vertical-align: top;
            font-size: 11px;
            width: 50%;
            padding: 5px;
        }
        .sig-line {
            border-top: 1px dashed #000;
            width: 200px;
            margin: 0 auto;
            margin-top: 5px;
        }
    </style>
</head>
<body>

{{-- Header --}}
<table class="header-table" style="border-bottom: 3px solid #000; width: 100%;">
    <tr>
        <td style="width: 70px; padding-bottom: 5px;">
            @if($logoBase64)
                <img src="data:image/jpeg;base64,{{ $logoBase64 }}" style="height: 60px;">
            @endif
        </td>
        <td style="padding-bottom: 5px;">
            <div class="header-title">LAPORAN BULANAN {{ strtoupper($positionName) }}</div>
            <div class="header-subtitle">DIREKTORAT JENDERAL PENATAAN RUANG LAUT</div>
        </td>
    </tr>
</table>

{{-- Title --}}
<div class="section-title">SITUASI UMUM</div>

{{-- Condition Paragraph --}}
<div class="condition-section">
    <p>Layanan {{ $positionName }} selama bulan {{ $monthName }} pada tahun {{ $year }} dalam keadaan:</p>
    <p style="margin-left: 30px;">
        @if($report->condition_status === 'baik')
            [✓] Baik
        @else
            [&nbsp;&nbsp;] Baik
        @endif
    </p>
    <p style="margin-left: 30px;">
        @if($report->condition_status === 'rusak')
            [✓] Rusak
        @else
            [&nbsp;&nbsp;] Rusak
        @endif
    </p>
    <p style="margin-left: 30px;">
        @if($report->condition_status === 'permasalahan')
            [✓] Ditemukan permasalahan
        @else
            [&nbsp;&nbsp;] Ditemukan permasalahan
        @endif
    </p>
</div>

{{-- Catatan Pimpinan --}}
<div style="margin-top: 15px;">
    <p style="font-weight: bold; font-size: 11px;">Catatan Pimpinan:</p>
    <div class="notes-box">
        {{ $report->leader_notes ?? '' }}
    </div>
</div>

{{-- Signature Block --}}
<div style="page-break-inside: avoid;">
    <div style="margin-top: 25px;">
        <p style="text-align: right; font-size: 11px;">{{ $city }}, {{ $signDay }} {{ $signMonthName }} {{ $signYear }}</p>
    </div>

<table class="signature-table">
    <tr>
        <td>&nbsp;</td>
        <td style="text-align: center;">
            <p>Mengetahui,</p>
            <p style="font-weight: bold;">Ketua Tim Kerja {{ $teamName }}</p>
        </td>
    </tr>
    <tr>
        <td style="height: 90px; text-align: center; vertical-align: bottom;">
            @if($employeeSign)
                <img src="{{ $employeeSign }}" style="height: 80px;">
            @endif
        </td>
        <td style="height: 90px; text-align: center; vertical-align: bottom;">
            @if($leaderSign)
                <img src="{{ $leaderSign }}" style="height: 80px;">
            @else
                <div style="height: 80px;">&nbsp;</div>
            @endif
        </td>
    </tr>
    <tr>
        <td>
            <p>{{ $report->user->name }}</p>
            <div class="sig-line"></div>
        </td>
        <td>
            <p>{{ $teamLeaderName }}</p>
            <div class="sig-line"></div>
        </td>
    </tr>
</table>

</div>

</body>
</html>
