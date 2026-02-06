<?php

namespace Database\Seeders;

use App\Models\JadwalUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JadwalUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // contoh: unit_id = 1
        $unitId = 1;

        $jadwal = [
            1 => ['08:00', '16:00'],
            2 => ['08:00', '16:00'],
            3 => ['08:00', '16:00'],
            4 => ['08:00', '16:30'],
            5 => ['08:00', '16:30'],
        ];

        foreach ($jadwal as $hari => [$masuk, $pulang]) {
            JadwalUnit::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'hari'    => $hari,
                ],
                [
                    'jam_masuk'  => $masuk,
                    'jam_pulang' => $pulang,
                ]
            );
        }
    }
}
