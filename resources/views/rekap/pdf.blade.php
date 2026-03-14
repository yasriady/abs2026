<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            word-wrap: break-word;
        }

        th {
            background: #eee;
        }

        .left {
            text-align: left;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">Pemerintah Kota Pekanbaru</div>
        <div class="subtitle">Rekapitulasi Absensi Bulanan</div>
        <div class="info">Unit : <b>{{ $unitName }}</b> ({{ $statusLabel }})</div>
        <div class="info">Periode : {{ $periodeLabel }}</div>
    </div>

    <div style="transform: scale(0.75); transform-origin: top left;">
        @include('rekap._table')
    </div>

</body>

</html>