<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JadwalPegawai;

class JadwalPegawaiSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Contoh data jadwal khusus pegawai
         * (bisa beda tiap tanggal)
         */
        $data = [
            [
                'nik'        => '1471030709820001',
                'date'       => '2026-02-20',
                'jam_masuk'  => '10:00',
                'jam_pulang' => '16:00',
            ],
            [
                'nik'        => '1471030709820001',
                'date'       => '2026-02-21',
                'jam_masuk'  => '09:00',
                'jam_pulang' => '15:00',
            ],
            [
                'nik'        => '1471030709820002',
                'date'       => '2026-02-20',
                'jam_masuk'  => '08:30',
                'jam_pulang' => '17:00',
            ],
        ];

        foreach ($data as $row) {
            JadwalPegawai::updateOrCreate(
                [
                    'nik'  => $row['nik'],
                    'date' => $row['date'],
                ],
                [
                    'jam_masuk'  => $row['jam_masuk'],
                    'jam_pulang' => $row['jam_pulang'],
                ]
            );
        }
    }
}
