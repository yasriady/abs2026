<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Device;

class DeviceCsvSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/device.csv');

        if (!File::exists($path)) {
            $this->command->error("File tidak ditemukan: {$path}");
            return;
        }

        // Aman untuk SQLite
        DB::statement('PRAGMA foreign_keys = OFF;');
        Device::query()->delete();
        DB::statement('PRAGMA foreign_keys = ON;');

        $handle = fopen($path, 'r');

        if (!$handle) {
            $this->command->error("Gagal membuka file CSV");
            return;
        }

        $delimiter = ';';
        $header = fgetcsv($handle, 0, $delimiter); // buang header

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

            // Pastikan minimal 8 kolom
            if (count($row) < 8) {
                continue;
            }

            Device::create([
                // ⚠️ JANGAN SET ID
                'device_id'   => $this->clean($row[1]),
                'unit_id'     => $this->clean($row[2]),
                'desc'        => $this->clean($row[3]),
                'enabled'     => (int) $this->clean($row[4]),
                'public_key' => $this->clean($row[5]),
                'private_key'=> $this->clean($row[6]),
                'created_at' => $this->fixDate($row[7]),
                'updated_at' => $this->fixDate($row[8]),
            ]);
        }

        fclose($handle);

        $this->command->info('Seeder Device CSV berhasil dijalankan!');
    }

    private function clean($value)
    {
        $value = trim($value);

        if ($value === '\N' || $value === '') {
            return null;
        }

        return $value;
    }

    private function fixDate($value)
    {
        $value = $this->clean($value);

        if ($value === '0000-00-00 00:00:00') {
            return null;
        }

        return $value;
    }
}
