<?php

namespace App\Http\Controllers\Devel;

use App\Http\Controllers\Controller;
use App\Models\AbsensiRaw;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;

class DevelAbsensiRawController extends Controller
{
    public function absensiRaw_lambat()
    {
        // $pegawaiAktif = Pegawai::whereDate('begin_date', '<=', Carbon::today())
        //     ->whereDate('end_date', '>=', Carbon::today())
        //     ->get();
        // return $pegawaiAktif;

        $summary = AbsensiRaw::select(
            'nik',
            'date',

            DB::raw("
            MIN(
                CASE
                    WHEN time BETWEEN '06:00:00' AND '11:59:59'
                    THEN time
                END
            ) AS time_in
        "),

            DB::raw("
            MAX(
                CASE
                    WHEN time BETWEEN '13:00:00' AND '21:00:00'
                    THEN time
                END
            ) AS time_out
        ")
        )
            ->groupBy('nik', 'date')
            ->limit(100)
            ->get();

        return $summary;
    }

    public function absensiRaw_cepat()
    {
        $summary = AbsensiRaw::select(
            'nik',
            'date',

            DB::raw("
            MIN(
                CASE
                    WHEN time BETWEEN '06:00:00' AND '11:59:59'
                    THEN time
                END
            ) AS time_in
        "),

            DB::raw("
            MAX(
                CASE
                    WHEN time BETWEEN '13:00:00' AND '21:00:00'
                    THEN time
                END
            ) AS time_out
        ")
        )
            ->whereDate('date', '2025-12-12') // ⬅️ INI KRUSIAL
            ->groupBy('nik', 'date')
            ->orderBy('date')
            ->limit(100)
            ->get();

        return $summary;
    }
}
