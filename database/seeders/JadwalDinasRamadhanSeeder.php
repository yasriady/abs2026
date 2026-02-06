<?php

// database/seeders/JadwalDinasRamadhanSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JadwalDinas;

class JadwalDinasRamadhanSeeder extends Seeder
{
    public function run(): void
    {
        $periodeAwal = '2026-02-18';
        $periodeAkhir = '2026-03-17';

        $jadwalNormal = [
            1 => ['08:00', '16:00'],
            2 => ['08:00', '16:00'],
            3 => ['08:00', '16:00'],
            4 => ['08:00', '16:30'],
            5 => ['08:00', '16:30'],
        ];

        foreach ($jadwalNormal as $hari => [$masuk, $pulang]) {

            $jamMasukRamadhan  = date('H:i:s', strtotime("$masuk +1 hour"));
            $jamPulangRamadhan = date('H:i:s', strtotime("$pulang -1 hour"));

            JadwalDinas::create([
                'hari'        => $hari,
                'start_date'  => $periodeAwal,
                'end_date'    => $periodeAkhir,
                'jam_masuk'   => $jamMasukRamadhan,
                'jam_pulang'  => $jamPulangRamadhan,
            ]);
        }
    }
}
