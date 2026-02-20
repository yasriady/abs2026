<!DOCTYPE html>
<html>
<head>
    <title>Print Rekap</title>

    <style>
        body { font-family: Arial; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border:1px solid #000; padding:6px; }
        th { background:#eee; }
        @media print {
            button { display:none }
        }
    </style>
</head>
<body>

<h3 style="text-align:center">
    Rekapitulasi Absensi
</h3>

<p>
Tanggal: {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('F Y') }}
</p>

<table>
<thead>
<tr>
    <th>No</th>
    <th>Unit</th>
    <th>SubUnit</th>
    <th>Status</th>
</tr>
</thead>

<tbody>
@foreach($rows as $i => $row)
<tr>
    <td>{{ $i+1 }}</td>
    <td>{{ $row->unit->unit }}</td>
    <td>{{ $row->subUnit->name ?? '-' }}</td>
    <td>{{ $row->status }}</td>
</tr>
@endforeach
</tbody>
</table>

<br>

<button onclick="window.print()">Print</button>

</body>
</html>
