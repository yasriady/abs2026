<?php

namespace Database\Seeders;

use App\Models\JadwalDinas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JadwalDinasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jadwal = [
            1 => ['08:00', '16:00'],
            2 => ['08:00', '16:00'],
            3 => ['08:00', '16:00'],
            4 => ['08:00', '16:30'],
            5 => ['08:00', '16:30'],
        ];

        foreach ($jadwal as $hari => [$masuk, $pulang]) {
            JadwalDinas::updateOrCreate(
                ['hari' => $hari],
                [
                    'jam_masuk'  => $masuk,
                    'jam_pulang' => $pulang,
                ]
            );
        }
    }
}
