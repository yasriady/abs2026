<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Bulanan</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 0.55em;
        }

        th,
        td {
            border: 1px solid #dfdfdf;
            padding: 4px;
            text-align: center;
        }

        th {
            /* background: #eee; */
        }

        .text-left {
            text-align: left;
        }

        .small {
            font-size: 11px;
        }

        .rekap {
            border-collapse: collapse;
            font-size: 12px;
        }



        .bg-red {
            background: #f8caca;
        }

        .bg-yellow {
            background: #fff3b0;
        }

        .bg-gray {
            background: #eee;
        }

        .out {
            color: #555;
            font-size: 11px;
        }

        .bg-red {
            background: #f8caca;
        }

        .bg-yellow {
            background: #fff3b0;
        }

        .bg-white {
            background: #ffffff;
        }

        .bg-gray {
            background: #eee;
        }

        .rekap {
            border-collapse: collapse;
            font-size: 11px;
            letter-spacing: -0.15px;
        }

        .rekap {
            /* table-layout: fixed; */
        }

        .rekap th,
        .rekap td {
            border: 1px solid #ccc;
            padding: 4px;
            text-align: center;
            white-space: nowrap;
            border: 1px solid #cfcfcf;
            padding: 2px 4px;
            line-height: 1.1;
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
        }

        .rekap td {
            font-size: 0.75em;
        }

        /* kolom nama */
        .rekap td.nama {
            text-align: left;
        }

        /* baris kedua (jam pulang) lebih kecil */
        .rekap .out {
            font-size: 10px;
            color: #444;
        }

        /* warna status */
        .bg-red {
            background: #f6b0b0;
        }

        .bg-yellow {
            background: #ffe699;
        }

        .bg-white {
            background: #ffffff;
        }

        .bg-gray {
            background: #eeeeee;
        }

        .holiday {
            color: #c40000;
            font-weight: bold;
        }

        .header-wrap {
            background: #ececec;
            border: 1px solid #d9d9d9;
            padding: 8px 10px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .header-left {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .header-logo {
            width: 32px;
            height: 32px;
            object-fit: contain;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .header-title {
            font-size: 16px;
            font-weight: 600;
            line-height: 1.25;
        }

        .header-meta {
            font-size: 13px;
            line-height: 1.35;
        }

        .header-right {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 18px;
        }

        .print-btn {
            border: 0;
            background: #2f8f3a;
            color: #fff;
            border-radius: 3px;
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer;
        }

        .header-note {
            font-size: 13px;
        }

        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>

</head>

<body>

    <div class="header-wrap">
        <div class="header-left">
            <img src="{{ asset('images/logo_pekanbaru_xs.png') }}" alt="Logo" class="header-logo">
            <div>
                <div class="header-title">Pemerintah Kota Pekanbaru</div>
                <div class="header-title">Rekapitulasi Absensi Bulanan</div>
                <div class="header-meta">
                    Unit : <b>{{ $unitName }}</b> ({{ $statusLabel }})
                </div>
                <div class="header-meta">
                    Periode : {{ $periodeLabel }}
                </div>
            </div>
        </div>
        <div class="header-right">
            <button type="button" class="print-btn" onclick="window.print()">print</button>
            <div class="header-note">Note: T=Telat, X=Batal, Adm=Admin</div>
        </div>
    </div>



    <table class="rekap" style="">
        <thead>

            {{-- ROW HEADER 1 --}}
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Nama</th>

                {{-- GROUP TANGGAL --}}
                <th colspan="{{ count($dates) }}">Tanggal</th>

                <th rowspan="2">Total<br />Hari</th>
                <th rowspan="2">Hari<br>Kerja</th>
                <th rowspan="2">Menit<br>Telat+PC</th>
                <th rowspan="2">&nbsp; <br />%Pot</th>
                <th rowspan="2">Total<br />Alpa</th>
                <th rowspan="2">%Pot</th>

                {{-- GROUP TIDAK HADIR --}}
                <th colspan="9">Tidak Hadir</th>
            </tr>


            {{-- ROW HEADER 2 --}}
            <tr>

                {{-- LOOP TANGGAL --}}
                @foreach($dates as $tgl)

                @php
                $d = \Carbon\Carbon::parse($tgl);
                if($d->isWeekend()) continue;
                $isLibur = isset($libur[$tgl]);
                @endphp

                <th class="{{ $isLibur ? 'holiday' : '' }}">
                    {{ $d->format('d/m') }}
                    <br>
                    <small>{{ $d->locale('id')->translatedFormat('D') }}</small>
                </th>

                @endforeach


                {{-- KOLOM STATUS --}}
                <th>DL</th>
                <th>CT</th>
                <th>CBS</th>
                <th>CS</th>
                <th>CM</th>
                <th>CKAP</th>
                <th>CB</th>
                <th>CLTN</th>
                <th>TB</th>

            </tr>

        </thead>


        <tbody>

            @foreach($rows as $i=>$row)

            {{-- ROW MASUK --}}
            <tr class="pegawai-start">

                <td rowspan="2" style="vertical-align: top;">
                    {{ $i+1 }}
                </td>
                <td rowspan="1" style="text-align: left;">
                    {{ $row['nama'] }}
                </td>

                @foreach($dates as $tgl)

                @php
                $cell = $row['dates'][$tgl];
                $symbol = $cell['symbol'];
                $raw = $cell['raw'];

                $color = '';

                if($symbol == '-') $color='bg-gray';
                elseif($symbol == 'A') $color='bg-red';
                elseif(str_contains($symbol,'T')) $color='bg-yellow';
                @endphp

                <td class="{{ $cell['color_in'] }}">
                    {{ $raw['time_in_fmt'] ?? '-' }}
                </td>

                @endforeach

                <td rowspan="2">{{ $row['stats']['total_hari'] }}</td>
                <td rowspan="2">{{ $row['stats']['hari_kerja'] }}</td>
                <td rowspan="2">{{ $row['stats']['menit_telat'] }}</td>
                <td rowspan="2">x</td>
                <td rowspan="2">{{ $row['stats']['total_alpa'] }}</td>
                <td rowspan="2">x</td>

                <td rowspan="2">{{ $row['stats']['DL'] ?? 0 }}</td>
                <td rowspan="2">{{ $row['stats']['CT'] ?? 0  }}</td>
                <td rowspan="2">{{ $row['stats']['CBS'] ?? 0  }}</td>
                <td rowspan="2">{{ $row['stats']['CS']  ?? 0 }}</td>
                <td rowspan="2">{{ $row['stats']['CM']  ?? 0 }}</td>
                <td rowspan="2">{{ $row['stats']['CKAP'] ?? 0  }}</td>
                <td rowspan="2">{{ $row['stats']['CB']  ?? 0 }}</td>
                <td rowspan="2">{{ $row['stats']['CLTN']  ?? 0 }}</td>
                <td rowspan="2">{{ $row['stats']['TB']  ?? 0 }}</td>

            </tr>

            {{-- ROW PULANG --}}
            <tr>

                <td rowspan="1" style="text-align: left;">
                    {{ $row['nik'] }}
                </td>

                @foreach($dates as $tgl)

                @php
                $cell = $row['dates'][$tgl];
                $raw = $cell['raw'];
                @endphp

                <td class="{{ $cell['color_out'] }}">
                    {{ $raw['time_out_fmt'] ?? '-' }}
                </td>

                @endforeach

            </tr>

            @endforeach
        </tbody>
    </table>

    <br>

    <div style="font-size:11px">
        <b>Keterangan:</b><br>
        H = Hadir<br>
        T = Terlambat<br>
        PC = Pulang Cepat<br>
        A = Alpha<br>
        I = Izin<br>
        C = Cuti<br>
        S = Sakit
    </div>

</body>

</html>
