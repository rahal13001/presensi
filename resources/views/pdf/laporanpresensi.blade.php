<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Presensi PJLP</title>
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
            text-align: left;
        }
        .info td:first-child {
            font-weight: bold;
            text-align: left;
            width: 130px;
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
            border:none;
            text-align: center;
            margin-top: 40px;
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
    <h4>Laporan Presensi / Kehadiran PJLP</h4>

    <div class="info">
        <table>

            <tr>
                <td><strong>Nama PJLP</strong></td>
                <td><strong>:</strong></td>
                <td>{{ $monthlyreport->user->name }}</td>
            </tr>
            <tr>
                <td><strong>Jabatan PJLP</strong></td>
                <td><strong>:</strong></td>
                <td>{{$monthlyreport->position->position_name }}</td>
            </tr>
            <tr>
                <td><strong>Bulan</strong></td>
                <td><strong>:</strong></td>
                <td>{{ \Carbon\Carbon::create()->month($monthlyreport->month)->translatedFormat('F') }} {{$monthlyreport->year}}</td>
            </tr>
            <tr>
                <td><strong>Tanggal</strong></td>
                <td><strong>:</strong></td>
                <td>
                  1 s.d  {{\Carbon\Carbon::create($monthlyreport->year, $monthlyreport->moneth)->endOfMonth()->day}}
                </td>
            </tr>
        </table>
    </div>

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

    <div class="note">
        <h4>Keterangan Pengenaan Sanksi</h4>
        <ol>
            <li>Toleransi keterlambatan maksimal 30 menit.</li>
            <li>Keterlambatan setelah 30 menit wajib diganti dengan sejumlah menit keterlambatan yang sama (terhitung menit ke-31 dan seterusnya).</li>
            <li>Tidak hadir tanpa keterangan dikenakan sanksi pemotongan gaji sebesar Rp300.000,00/hari.</li>
            <li>Pulang Sebelum Waktunya (PSW), dikenakan sanksi pemotongan sebesar Rp150.000,00/hari.</li>
            <li>Ijin Sakit wajib disertai Bukti Pendukung, apabila tidak melampirkan maka akan dianggap Tidak Hadir Tanpa Keterangan + sanksi sesuai ketentuan berlaku.</li>
            <li>Akumulasi toleransi maksimal keterlambatan dalam kurun waktu 1 (satu) bulan berjalan, sebanyak 120 (seratus dua puluh) menit dan dikenakan sanksi administratif berupa Surat Peringatan Tertulis</li>
            <li>Diberikan hak cuti selama 12 (dua belas) hari dari masa berlaku Kontrak PJLP selama 1 (satu) tahun</li>
            <li>Ijin Sakit atau Cuti Bersama memotong jumlah cuti dalam 1 (satu) tahun</li>
            <!-- Continue with all notes -->
        </ol>
    </div>

    <table class="signature-table">
        <tr>
            <td>
                <p>Pengawas Pekerjaan</p>
                <p><strong>Ketua Tim Dukungan Manajerial</strong></p>
                @if ($monthlyreport->team_sign)
                    <img src="{{ $monthlyreport->dukman_sign }}" alt="Team Sign">
                @else
                    <div class="placeholder"></div>
                @endif
                <p>{{ $monthlyreport->dukman_leader }}</p>
                <p>NIP. {{ $monthlyreport->dukman_idnumber }}</p>
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
