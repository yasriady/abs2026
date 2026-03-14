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

            <th class="sum3">DL</th>
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

            <td class="num sum3">{{ $row['stats']['DL'] ?? 0 }}</td>
            <td class="num">{{ $row['stats']['CT'] ?? 0 }}</td>
            <td class="num">{{ $row['stats']['CBS'] ?? 0 }}</td>
            <td class="num">{{ $row['stats']['CS'] ?? 0 }}</td>
            <td class="num">{{ $row['stats']['CM'] ?? 0 }}</td>
            <td class="num">{{ $row['stats']['CKAP'] ?? 0 }}</td>
            <td class="num">{{ $row['stats']['CB'] ?? 0 }}</td>
            <td class="num">{{ $row['stats']['CLTN'] ?? 0 }}</td>
            <td class="num">{{ $row['stats']['TB'] ?? 0 }}</td>

        </tr>

        @endforeach

    </tbody>
</table>