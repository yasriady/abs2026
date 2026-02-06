<?php

namespace Database\Seeders;

use App\Models\JadwalSubUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JadwalSubUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // contoh: sub_unit_id = 1
        $subUnitId = 1;

        $jadwal = [
            1 => ['08:00', '16:00'],
            2 => ['08:00', '16:00'],
            3 => ['08:00', '16:00'],
            4 => ['08:00', '16:30'],
            5 => ['08:00', '16:30'],
        ];

        foreach ($jadwal as $hari => [$masuk, $pulang]) {
            JadwalSubUnit::updateOrCreate(
                [
                    'sub_unit_id' => $subUnitId,
                    'hari'        => $hari,
                ],
                [
                    'jam_masuk'  => $masuk,
                    'jam_pulang' => $pulang,
                ]
            );
        }
    }
}
