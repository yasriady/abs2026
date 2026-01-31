<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UnitCsvSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/seeders/unit.csv');

        if (!file_exists($path)) {
            $this->command->error("File CSV tidak ditemukan: " . $path);
            return;
        }

        DB::table('units')->truncate(); // kosongkan dulu

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, ';'); // skip header

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            DB::table('units')->insert([
                'id' => (int) $row[0],
                'unit' => $row[1],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        fclose($handle);

        $this->command->info("Seeder Unit dari CSV selesai.");
    }
}
