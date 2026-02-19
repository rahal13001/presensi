<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan - Page 1</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.5cm;
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
            color: #1a237e;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
            text-align: center;
        }
        .header-subtitle {
            color: #1a237e;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            text-align: center;
        }
        /* Employee info */
        .info-table {
            border: none;
            margin-bottom: 8px;
        }
        .info-table td {
            border: none;
            padding: 2px 5px;
            font-size: 10px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        /* Section titles */
        .section-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            font-size: 11px;
            margin: 10px 0 8px 0;
        }
        /* Checklist table */
        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        .checklist-table th,
        .checklist-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
        }
        .checklist-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .col-no {
            width: 30px;
        }
        .checklist-table .col-scope {
            width: 250px;
            text-align: left;
            padding-left: 5px;
        }
        .col-label {
            width: 50px;
        }
        .col-day {
            width: auto;
            min-width: 16px;
        }
        /* Lain-lain */
        .lainlain {
            font-size: 10px;
            margin-top: 10px;
        }
        .lainlain-title {
            font-weight: bold;
        }
        /* Documentation */
        .doc-section {
            margin-top: 12px;
            page-break-inside: avoid;
        }
        .doc-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 5px;
        }
        .photo-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .photo-table td {
            border: none;
            padding: 5px;
            text-align: center;
            vertical-align: bottom;
            height: 220px;
            overflow: hidden;
        }
        .photo-wrapper {
            height: 190px;
            display: block;
            margin-bottom: 5px;
        }
        .photo-table img {
            max-width: 98%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
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
            <div class="header-title" style="color: #000;">LAPORAN BULANAN {{ strtoupper($positionName) }}</div>
            <div class="header-subtitle" style="color: #000;">DIREKTORAT JENDERAL PENATAAN RUANG LAUT</div>
        </td>
    </tr>
</table>

{{-- Employee Info --}}
<table class="info-table">
    <tr>
        <td class="info-label">LAPORAN BULAN</td>
        <td>: {{ $monthName }} {{ $year }}</td>
    </tr>
    <tr>
        <td class="info-label">NAMA</td>
        <td>: {{ $report->user->name }}</td>
    </tr>
</table>

{{-- Sub-title --}}
<div class="section-title" style="text-decoration: none;">LAPORAN {{ strtoupper($positionName) }}</div>

{{-- Checklist Table --}}
<table class="checklist-table">
    {{-- Row 1: Week headers --}}
    <tr>
        <th class="col-no" rowspan="2">No.</th>
        <th class="col-scope" rowspan="2">Pekerjaan Ruang Lingkup</th>
        <th class="col-label" rowspan="2">Minggu<br>Tanggal</th>
        @foreach($weeks as $week)
            <th colspan="{{ count($week['days']) }}">{{ $week['label'] }}</th>
        @endforeach
    </tr>
    {{-- Row 2: Day numbers --}}
    <tr>
        @foreach($weeks as $week)
            @foreach($week['days'] as $day)
                <th class="col-day" style="background-color: {{ $dayColors[$day] ?? '#FFFFFF' }};">{{ $day }}</th>
            @endforeach
        @endforeach
    </tr>
    {{-- Scope rows --}}
    @foreach($scopes as $index => $scope)
        <tr>
            <td class="col-no">{{ $index + 1 }}.</td>
            <td class="col-scope">{{ $scope->name }}</td>
            <td class="col-label"></td>
            @foreach($weeks as $week)
                @foreach($week['days'] as $day)
                    <td class="col-day" style="background-color: {{ $dayColors[$day] ?? '#FFFFFF' }};">
                        @if(isset($matrix[$scope->id][$day]) && $matrix[$scope->id][$day])
                            ✓
                        @endif
                    </td>
                @endforeach
            @endforeach
        </tr>
    @endforeach
</table>

{{-- Lain-lain Section --}}
@if(count($otherWorkNames) > 0)
    <div class="lainlain">
        <span class="lainlain-title">Lain-lain.</span>
        <ol style="margin: 3px 0 0 20px; padding: 0;">
            @foreach($otherWorkNames as $name)
                <li>{{ $name }}</li>
            @endforeach
        </ol>
    </div>
@endif

{{-- Dokumentasi Section --}}
@if(count($photoData) > 0)
    <div class="doc-section">
        <div class="doc-title">Dokumentasi.</div>
        <table class="photo-table">
            @for($row = 0; $row < ceil(count($photoData) / 3); $row++)
                <tr>
                    @for($col = 0; $col < 3; $col++)
                        @php $idx = $row * 3 + $col; @endphp
                        <td>
                        @if(isset($photoData[$idx]))
                            <div class="photo-wrapper">
                                <img src="data:{{ $photoData[$idx]['mime'] }};base64,{{ $photoData[$idx]['base64'] }}">
                            </div>
                            @if($photoData[$idx]['caption'])
                                <div style="line-height: 1.1;"><small>{{ $photoData[$idx]['caption'] }}</small></div>
                            @endif
                        @endif
                    </td>
                    @endfor
                </tr>
            @endfor
        </table>
    </div>
@endif

</body>
</html>
