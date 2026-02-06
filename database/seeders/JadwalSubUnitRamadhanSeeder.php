<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JadwalSubUnit;

class JadwalSubUnitRamadhanSeeder extends Seeder
{
    public function run(): void
    {
        // 🔧 KONFIGURASI
        $subUnitId = 1; // 👉 ganti sesuai sub_unit_id yang ingin diberi jadwal Ramadhan

        $startDate = '2026-02-18';
        $endDate   = '2026-03-17';

        // Jadwal NORMAL sub unit
        $jadwalNormal = [
            1 => ['08:00', '16:00'], // Senin
            2 => ['08:00', '16:00'], // Selasa
            3 => ['08:00', '16:00'], // Rabu
            4 => ['08:00', '16:30'], // Kamis
            5 => ['08:00', '16:30'], // Jumat
        ];

        foreach ($jadwalNormal as $hari => [$jamMasuk, $jamPulang]) {

            // ⏰ Penyesuaian Ramadhan
            $jamMasukRamadhan  = date('H:i:s', strtotime("$jamMasuk +1 hour"));
            $jamPulangRamadhan = date('H:i:s', strtotime("$jamPulang -1 hour"));

            JadwalSubUnit::updateOrCreate(
                [
                    'sub_unit_id' => $subUnitId,
                    'hari'        => $hari,
                    'start_date'  => $startDate,
                    'end_date'    => $endDate,
                ],
                [
                    'jam_masuk'   => $jamMasukRamadhan,
                    'jam_pulang'  => $jamPulangRamadhan,
                ]
            );
        }
    }
}
