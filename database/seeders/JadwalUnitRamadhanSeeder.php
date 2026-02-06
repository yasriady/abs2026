<?php

// database/seeders/JadwalUnitRamadhanSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JadwalUnit;

class JadwalUnitRamadhanSeeder extends Seeder
{
    public function run(): void
    {
        // 🔧 KONFIGURASI
        $unitId = 1; // 👉 ganti sesuai unit yang ingin diberi jadwal Ramadhan

        $startDate = '2026-02-18';
        $endDate   = '2026-03-17';

        // jadwal NORMAL unit (harus sama dengan seeder default)
        $jadwalNormal = [
            1 => ['08:00', '16:00'], // Senin
            2 => ['08:00', '16:00'], // Selasa
            3 => ['08:00', '16:00'], // Rabu
            4 => ['08:00', '16:30'], // Kamis
            5 => ['08:00', '16:30'], // Jumat
        ];

        foreach ($jadwalNormal as $hari => [$jamMasuk, $jamPulang]) {

            $jamMasukRamadhan  = date('H:i:s', strtotime("$jamMasuk +1 hour"));
            $jamPulangRamadhan = date('H:i:s', strtotime("$jamPulang -1 hour"));

            JadwalUnit::updateOrCreate(
                [
                    'unit_id'    => $unitId,
                    'hari'       => $hari,
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                ],
                [
                    'jam_masuk'  => $jamMasukRamadhan,
                    'jam_pulang' => $jamPulangRamadhan,
                ]
            );
        }
    }
}
