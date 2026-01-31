<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mahasiswa;
use Faker\Factory as Faker;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $jurusanList = [
            'Teknik Informatika',
            'Sistem Informasi',
            'Manajemen',
            'Akuntansi',
            'Teknik Sipil',
            'Teknik Elektro',
            'Ilmu Komunikasi',
            'Hukum',
            'Kesehatan Masyarakat',
        ];

        $angkatanList = ['2020', '2021', '2022', '2023', '2024'];

        for ($i = 1; $i <= 500; $i++) {
            Mahasiswa::create([
                'nim' => 'NIM' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'nama' => $faker->name,
                'jurusan' => $faker->randomElement($jurusanList),
                'angkatan' => $faker->randomElement($angkatanList),
            ]);
        }
    }
}
