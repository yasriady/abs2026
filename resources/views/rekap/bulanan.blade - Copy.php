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

        /* ===== EXPORT BUTTON ===== */
        .export-wrap {
            position: relative;
        }

        .export-btn {
            border: 0;
            background: #2d6cdf;
            color: #fff;
            border-radius: 4px;
            padding: 6px 14px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all .15s ease;
            position: relative;
            overflow: hidden;
        }

        .export-btn:hover {
            background: #1f57b8;
        }

        .export-btn:active {
            transform: scale(.97);
        }

        .export-btn[disabled] {
            opacity: .6;
            cursor: not-allowed;
        }

        /* ripple */
        .export-btn::after {
            content: '';
            position: absolute;
            width: 120%;
            height: 120%;
            background: rgba(255, 255, 255, .25);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            border-radius: 50%;
            opacity: 0;
        }

        .export-btn:active::after {
            animation: ripple .4s ease;
        }

        @keyframes ripple {
            to {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        /* ===== DROPDOWN ===== */
        .export-menu {
            position: absolute;
            right: 0;
            top: 120%;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            min-width: 180px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .15);
            opacity: 0;
            transform: translateY(-6px) scale(.97);
            pointer-events: none;
            transition: .18s ease;
        }

        .export-menu.show {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .export-menu a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            text-decoration: none;
            color: #333;
            font-size: 12px;
            transition: .12s;
        }

        .export-menu a:hover {
            background: #f4f7ff;
        }

        .export-menu a:active {
            transform: scale(.97);
        }

        /* divider */
        .export-divider {
            height: 1px;
            background: #eee;
            margin: 4px 0;
        }

        /* spinner */
        .loader {
            width: 14px;
            height: 14px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* PRINT MODE */
        @media print {
            .export-wrap {
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

            <div style="display:flex; gap:6px">

                <!-- PRINT -->
                <button class="print-btn" onclick="window.print()">
                    Print
                </button>

                <!-- EXPORT -->
                <div class="export-wrap">

                    <button class="export-btn"
                        id="exportBtn"
                        aria-haspopup="true"
                        aria-expanded="false"
                        onclick="toggleExport()">

                        <span id="exportLabel">Export</span>
                        <span id="exportLoader" style="display:none" class="loader"></span>
                        ▾
                    </button>

                    <div class="export-menu" id="exportMenu" role="menu">

                        <a href="{{ route('rekap.export.pdf', request()->only([
                                'unit_id',
                                'sub_unit_id',
                                'bulan',
                                'status_pegawai'
                            ])) }}"
                            onclick="startExport(event)">
                            📄 Export PDF
                        </a>

                        <a href="{{ route('rekap.export.excel', request()->query()) }}"
                            onclick="startExport(event)">
                            📊 Export Excel (.xlsx)
                        </a>

                    </div>
                </div>

            </div>

            <div class="header-note">
                Note: T=Telat, X=Batal, Adm=Admin
            </div>

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

    <script>
        const btn = document.getElementById('exportBtn');
        const menu = document.getElementById('exportMenu');
        const label = document.getElementById('exportLabel');
        const loader = document.getElementById('exportLoader');

        function toggleExport() {
            const open = menu.classList.toggle('show');
            btn.setAttribute('aria-expanded', open);
        }

        function closeExport() {
            menu.classList.remove('show');
            btn.setAttribute('aria-expanded', false);
        }

        document.addEventListener('click', e => {
            if (!e.target.closest('.export-wrap')) closeExport();
        });

        document.addEventListener('keydown', e => {
            if (e.key === "Escape") closeExport();
        });

        function startExport(e) {
            label.textContent = "Processing...";
            loader.style.display = "inline-block";
            btn.disabled = true;
        }
    </script>

</body>

</html>