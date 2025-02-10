<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Presensi</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        h4 {
            text-align: left;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 6px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .signature-table {
            width: 100%;
            table-layout: fixed;
            text-align: center;
            margin-top: 40px;
            border: none;
        }

        .signature-table td {
            vertical-align: top;
            width: 50%;
            border: none;
        }

        .signature-table img, .signature-table .placeholder {
            width: 60%;
            height: 80px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            border: none;
        }
    </style>
</head>
<body>
    <h4>2. Data Presensi Pegawai</h4>

    <table>
        <thead>
            <!-- Table header -->
            <tr>
                <th rowspan="2">Tanggal</th>
                <th rowspan="2">Hari</th>
                <th colspan="2">Jam Presensi</th>
                <th rowspan="2">Jam Kerja</th>
                <th rowspan="2">Keterlambatan</th>
                <th colspan="3">Kedisiplinan</th>
                <th colspan="2">Cuti / Dinas</th>
            </tr>
            <tr>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Kehadiran</th>
                <th>Terlambat</th>
                <th>PSW</th>
                <th>Jenis</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <!-- Example row -->
            @foreach ($attendances as $index => $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->created_at)->format('d-m-Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->created_at)->translatedFormat('l') }}</td>
                    <td>{{ $attendance->start_time }}</td>
                    <td>{{ $attendance->end_time }}</td>
                    <td>{{ $attendance->work_duration}}</td>
                    <td>{{ $attendance->late_duration}}</td>
                    <td>{{ $attendance->attendance}}</td>
                    <td>{{$attendance->is_late}}</td>
                    <td>{{$attendance->psw_status}}</td>
                    <td>{{$attendance->typeofleave}}</td>
                    <td>{{$attendance->leave_reason}}</td>
                </tr>
            @endforeach

            <!-- Add more rows as needed -->
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <p>Mengetahui,</p>
                <p><strong>Ketua Tim Kerja</strong></p>
                @if ($monthlyreport->team_sign)
                    <img src="{{ $monthlyreport->team_sign }}" alt="Team Sign">
                @else
                    <div class="placeholder"></div>
                @endif
                <p>{{ $attendance->user->name }}</p>
                <p>NIP. {{ $attendance->user->idnumber }}</p>
            </td>
            <td>
                <p>PJLP</p>
                <p><strong>{{ $attendance->position->position_name }}</strong></p>
                @if ($monthlyreport->user_sign)
                    <img src="{{ $monthlyreport->user_sign }}" alt="User Sign">
                @else
                    <div class="placeholder"></div>
                @endif
                <p>{{ $monthlyreport->user->name }}</p>
                <p>NIK. {{ $monthlyreport->user->idnumber }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
