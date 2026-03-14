<table>
    <tr>
        <td colspan="{{ 3 + count($dates) + 4 }}" style="font-weight:bold; font-size:14px;">
            Pemerintah Kota Pekanbaru
        </td>
    </tr>

    <tr>
        <td colspan="{{ 3 + count($dates) + 4 }}" style="font-weight:bold; font-size:13px;">
            Rekapitulasi Absensi Bulanan
        </td>
    </tr>

    <tr>
        <td colspan="{{ 3 + count($dates) + 4 }}">
            Unit : <b>{{ $unitName }}</b> ({{ $statusLabel }})
        </td>
    </tr>

    <tr>
        <td colspan="{{ 3 + count($dates) + 4 }}">
            Periode : {{ $periodeLabel }}
        </td>
    </tr>

    <tr>
        <td colspan="{{ 3 + count($dates) + 4 }}"></td>
    </tr>
</table>

@include('rekap._table')