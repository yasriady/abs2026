<?php

namespace App\Http\Controllers\Devel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DevelJadwalController extends Controller
{
   private function cariJadwal($nik, $date)
    {
        $resolver = new JadwalResolverService();
        $jadwal = $resolver->resolve($nik, $date);
        if ($jadwal) {
            // contoh hasil:
            // [
            //   'sumber' => 'sub_unit',
            //   'jam_masuk' => '09:00:00',
            //   'jam_pulang' => '15:30:00'
            // ]
        }


        return $jadwal;
    }
}
