<?php

namespace Database\Seeders;

use App\Models\JadwalPegawai;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JadwalPegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JadwalPegawai::updateOrCreate(
            [
                'nik'  => '1234567890',
                'date' => '2026-02-10',
            ],
            [
                'jam_masuk'  => '09:00',
                'jam_pulang' => '17:00',
            ]
        );
    }
}
