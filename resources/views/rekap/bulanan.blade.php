<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Bulanan</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        /* TABLE */
        .rekap {
            border-collapse: collapse;
            width: 100%;
            /* table-layout: auto; */
            /* penting */
            table-layout: fixed;
        }

        .rekap th,
        .rekap td {
            border: 1px solid #999;
            padding: 2px 4px;
            line-height: 1.2;
            white-space: nowrap;
            vertical-align: middle;
        }

        /* HEADER */
        .rekap th {
            font-weight: 600;
            text-align: center;
        }

        /* NAMA */
        .nama {
            text-align: left;
            font-size: 0.80em;
        }

        /* ANGKA */
        .num {
            text-align: center;
        }

        /* STATUS COLORS */
        .bg-red {
            background: #f6b0b0;
        }

        .bg-yellow {
            background: #ffe699;
        }

        .bg-gray {
            background: #eee;
        }

        .bg-white {
            background: #fff;
        }

        .holiday {
            color: #c40000;
            font-weight: bold;
        }

        /* HEADER BOX */
        .header-wrap {
            /* background: #ececec; */
            /* border: 1px solid #bdbdbd; */
            padding: 8px 10px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }

        .header-title {
            font-size: 15px;
            font-weight: 600;
        }

        .header-meta {
            font-size: 12px;
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

        @media print {
            .print-btn {
                display: none;
            }

            .rekap {
                table-layout: auto;
            }
        }

        /* kolom nama */
        .rekap .nama {
            min-width: 110px;
            width: 120px;
            white-space: normal;
        }

        /* kolom tanggal dibuat ramping */
        .rekap .tgl {
            width: 35px;
            min-width: 35px;
            max-width: 35px;
            padding: 1px 2px;
        }

        /* teks jam lebih kecil agar muat */
        .rekap .tgl {
            font-size: 0.65em;
            text-align: center;
        }

        .dropdown {
            position: relative;
            display: inline-block;
            margin-left: 6px;
        }

        .dropdown-btn {
            border: 0;
            background: #2d6cdf;
            color: #fff;
            border-radius: 3px;
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background: #fff;
            min-width: 160px;
            border: 1px solid #ccc;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
            z-index: 99;
        }

        .dropdown-content a {
            color: #333;
            padding: 7px 10px;
            text-decoration: none;
            display: block;
            font-size: 12px;
        }

        .dropdown-content a:hover {
            background: #f1f1f1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        @media print {
            .dropdown {
                display: none;
            }
        }

        .rekap thead th {
            background: #f3f3f3;
            border-bottom: 2px solid #666;
        }

        .sum {
            width: 60px;
            min-width: 60px;
            max-width: 60px;
            font-size: 0.85em;
        }

        .sum2 {
            width: 32px;
            min-width: 32px;
            max-width: 32px;
            font-size: 0.85em;
        }

        .sum3 {
            width: 40px;
            min-width: 40px;
            max-width: 40px;
            font-size: 0.85em;
        }

        .rekap th,
        .rekap td {
            box-sizing: border-box;
        }

        .rekap th[rowspan] {
            vertical-align: middle;
        }

        @page {
            size: landscape;
            margin: 0;
        }

        .nama {
            word-break: break-word;
        }

        .tidakhadir {
            font-size: 0.75em;
            width: 38px;
            min-width: 38px;
            max-width: 38px;
        }
    </style>
</head>

<body>

    <div class="header-wrap">
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

        <div style="display:flex; align-items:flex-start; gap:6px;">

            <button class="print-btn" onclick="window.print()">Print</button>

            <div class="dropdown">
                <button class="dropdown-btn">Export ▾</button>
                <div class="dropdown-content">
                    <a href="{{ route('rekap.export.pdf', request()->query()) }}">
                        Export PDF
                    </a>
                    <a href="{{ route('rekap.export.excel', request()->query()) }}">
                        Export Excel (.xlsx)
                    </a>
                </div>
            </div>

        </div>

    </div>

    <table class="rekap">

        <thead>

            {{-- HEADER BARIS 1 --}}
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2" class="nama">Nama</th>

                @foreach($dates as $tgl)
                @php
                $d=\Carbon\Carbon::parse($tgl);
                $isLibur=isset($libur[$tgl]);
                @endphp

                <th class="tgl {{ $isLibur?'holiday':'' }}">
                    {{ $d->format('d/m') }}
                </th>
                @endforeach

                <th colspan="1" class="sum2">Total</th>
                <th colspan="1" class="sum2">Hari</th>
                <th colspan="2" class="sum">Menit Telat</th>
                <th colspan="2" class="sum">Total Alpa</th>
                <th colspan="9">Tidak Hadir</th>
            </tr>


            {{-- HEADER BARIS 2 --}}
            <tr>

                @foreach($dates as $tgl)
                @php $d=\Carbon\Carbon::parse($tgl); @endphp

                <th class="tgl">
                    {{ $d->locale('id')->translatedFormat('D') }}
                </th>
                @endforeach

                <th class="sum">Hari</th>
                <th class="sum">Kerja</th>

                <th class="sum">JML</th>
                <th class="sum">%Pot</th>

                <th class="sum">Alpa</th>
                <th class="sum">%Pot</th>

                <th class="tidakhadir">DL</th>
                <th class="tidakhadir">CT</th>
                <th class="tidakhadir">CBS</th>
                <th class="tidakhadir">CS</th>
                <th class="tidakhadir">CM</th>
                <th class="tidakhadir">CKAP</th>
                <th class="tidakhadir">CB</th>
                <th class="tidakhadir">CLTN</th>
                <th class="tidakhadir">TB</th>

            </tr>

        </thead>


        <tbody>

            @foreach($rows as $i=>$row)

            <tr>

                <td class="num">{{ $i+1 }}</td>

                {{-- MERGED NAMA + NIK --}}
                <td class="nama">
                    {{ $row['nama'] }}<br>
                    {{ $row['nik'] }}
                </td>


                @foreach($dates as $tgl)
                @php
                $cell=$row['dates'][$tgl];
                @endphp

                <td class="tgl {{ $cell['color_in'] }}">
                    {{ $cell['raw']['time_in_fmt'] ?? '-' }}<br>
                    {{ $cell['raw']['time_out_fmt'] ?? '-' }}
                </td>
                @endforeach


                <td class="num sum">{{ $row['stats']['total_hari'] }}</td>
                <td class="num sum">{{ $row['stats']['hari_kerja'] }}</td>
                <td class="num sum">{{ $row['stats']['menit_telat'] }}</td>
                <td class="num sum">x</td>
                <td class="num sum">{{ $row['stats']['total_alpa'] }}</td>
                <td class="num sum">x</td>

                <td class="num tidakhadir">{{ $row['stats']['DL'] ?? 0 }}</td>
                <td class="num tidakhadir">{{ $row['stats']['CT'] ?? 0 }}</td>
                <td class="num tidakhadir">{{ $row['stats']['CBS'] ?? 0 }}</td>
                <td class="num tidakhadir">{{ $row['stats']['CS'] ?? 0 }}</td>
                <td class="num tidakhadir">{{ $row['stats']['CM'] ?? 0 }}</td>
                <td class="num tidakhadir">{{ $row['stats']['CKAP'] ?? 0 }}</td>
                <td class="num tidakhadir">{{ $row['stats']['CB'] ?? 0 }}</td>
                <td class="num tidakhadir">{{ $row['stats']['CLTN'] ?? 0 }}</td>
                <td class="num tidakhadir">{{ $row['stats']['TB'] ?? 0 }}</td>

            </tr>

            @endforeach

        </tbody>
    </table>

    <br>

    <div style="font-size:10px">
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