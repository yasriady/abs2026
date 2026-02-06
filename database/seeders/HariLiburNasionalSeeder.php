<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class HariLiburNasionalSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/hari-libur-nasional.csv');

        if (!File::exists($path)) {
            $this->command->error("File tidak ditemukan: storage/app/$path");
            return;
        }

        $content = File::get($path);
        $lines = array_filter(explode("\n", $content));

        // Hapus header
        array_shift($lines);

        DB::table('hari_libur_nasionals')->delete();

        foreach ($lines as $line) {
            $row = str_getcsv(trim($line), ';');

            DB::table('hari_libur_nasionals')->insert([
                'id' => $row[0] ?: null,
                'date' => $row[1],
                'year' => $row[2],
                'category' => $row[3],
                'description' => $row[4],
                'created_at' => $row[5] ?: null,
                'updated_at' => $row[6] ?: null,
            ]);
        }

        $this->command->info('Seeder Hari Libur Nasional berhasil diimport dari CSV.');
    }
}
