<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian PJLP</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 2cm 2cm 3cm 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            text-align: center;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .info {
            text-align: left;
            margin-bottom: 10px;
        }
        .info table {
            width: auto;
            border-collapse: collapse;
            font-size: 14px;
        }
        .info td {
            padding: 5px 0;
            vertical-align: top;
            border: none;
        }
        .info td:first-child {
            font-weight: bold;
            text-align: left;
            width: 130px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        /* 🚀 Ensure each report starts on a new page */
        .page-break {
            page-break-before: always;
        }
    </style>
    
</head>
<body>
    @foreach ($attendances as $index => $attendance)
        <div class="container @if($index > 0) page-break @endif">
            <h2>LAPORAN HARIAN PJLP {{ strtoupper($attendance->position->position_name) }}</h2>

            <div class="info">
                <table>
                    <tr>
                        <td><strong>Bulan</strong></td>
                        <td><strong>:</strong></td>
                        <td>{{ \Carbon\Carbon::create()->month($monthlyreport->month)->translatedFormat('F') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nama PJLP</strong></td>
                        <td><strong>:</strong></td>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Satuan Kerja</strong></td>
                        <td><strong>:</strong></td>
                        <td>LPSPL Sorong</td>
                    </tr>
                    <tr>
                        <td><strong>Jabatan PJLP</strong></td>
                        <td><strong>:</strong></td>
                        <td>{{ $attendance->position->position_name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal</strong></td>
                        <td><strong>:</strong></td>
                        <td>{{ \Carbon\Carbon::parse($attendance->created_at)->format('d-m-Y') }}</td>
                    </tr>
                </table>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Jam Kerja</th>
                        <th>Uraian Tugas</th>
                        <th>Output</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendance->dailyreports as $dailyIndex => $dailyreport)
                        <tr>
                            <td>{{ $dailyIndex + 1 }}</td>
                            <td>{{ $attendance->start_time }} - {{ $attendance->end_time }}</td>
                            <td>{!! $dailyreport->description !!}</td>
                            <td>{{ $dailyreport->output }}</td>
                            <td>{{ $dailyreport->note }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center;">Tidak ada data tugas harian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
