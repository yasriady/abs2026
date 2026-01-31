<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubUnitCsvSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/seeders/subunit.csv');

        if (!file_exists($path)) {
            $this->command->error("CSV tidak ditemukan: " . $path);
            return;
        }

        // SQLite-safe reset
        DB::statement('PRAGMA foreign_keys = OFF;');
        DB::table('sub_units')->delete();
        DB::statement('DELETE FROM sqlite_sequence WHERE name="sub_units";');
        DB::statement('PRAGMA foreign_keys = ON;');

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, ';'); // skip header

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            DB::table('sub_units')->insert([
                'id' => (int) $row[0],
                'sub_unit' => trim($row[1]),
                'unit_id' => (int) $row[2],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        fclose($handle);

        $this->command->info("Seeder SubUnit selesai.");
    }
}
