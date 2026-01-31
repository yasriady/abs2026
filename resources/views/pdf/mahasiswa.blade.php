k<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Mahasiswa</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
        }
        .header small {
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>DATA MAHASISWA</h2>
    <small>Universitas Contoh Pekanbaru</small>
</div>

<table>
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="20%">NIM</th>
            <th width="35%">Nama</th>
            <th width="25%">Jurusan</th>
            <th width="15%">Angkatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $i => $m)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $m->nim }}</td>
            <td>{{ $m->nama }}</td>
            <td>{{ $m->jurusan }}</td>
            <td>{{ $m->angkatan }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    Dicetak pada: {{ now()->format('d-m-Y H:i') }}
</div>

</body>
</html>

