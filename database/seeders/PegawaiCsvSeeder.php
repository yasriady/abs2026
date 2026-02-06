<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Pegawai;

class PegawaiCsvSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/pegawai.csv');

        if (!File::exists($path)) {
            $this->command->error("File tidak ditemukan: {$path}");
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF;');
        Pegawai::query()->delete();
        DB::statement('PRAGMA foreign_keys = ON;');

        $handle = fopen($path, 'r');
        $delimiter = ';';

        // Skip header
        fgetcsv($handle, 0, $delimiter);

        $maxId = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) < 16) {
                continue;
            }

            $id = (int) trim($row[0]);
            $maxId = max($maxId, $id);

            DB::table('pegawais')->insert([
                'id' => $id,
                'nip' => $this->clean($row[1]),
                'nik' => $this->clean($row[2]),
                'nama' => $this->clean($row[3]),
                'status_kepegawaian' => $this->clean($row[4]),
                'id_unit' => $this->clean($row[5]),
                'id_sub_unit' => $this->clean($row[6]),
                'id_struktur_organisasi' => $this->clean($row[7]),
                'created_at' => $this->fixDateTime($row[8]),
                'updated_at' => $this->fixDateTime($row[9]),
                'deleted_at' => $this->fixDateTime($row[10]),
                'begin_date' => $this->fixDate($row[11]),
                'end_date' => $this->fixDate($row[12]),
                'x_now_x' => (int) $this->clean($row[13]),
                'lokasi_kerja' => $this->clean($row[14]),
                'order' => (int) $this->clean($row[15]),
            ]);
        }

        fclose($handle);

        DB::statement("DELETE FROM sqlite_sequence WHERE name='pegawais'");
        DB::statement("INSERT INTO sqlite_sequence (name, seq) VALUES ('pegawais', {$maxId})");

        $this->command->info('Seeder Pegawai CSV berhasil!');
    }

    private function clean($value)
    {
        $value = trim($value);

        if ($value === '\N' || $value === '' || $value === 'NULL') {
            return null;
        }

        return $value;
    }

    private function fixDateTime($value)
    {
        $value = $this->clean($value);

        if ($value === '0000-00-00 00:00:00') {
            return null;
        }

        return $value;
    }

    private function fixDate($value)
    {
        $value = $this->clean($value);

        if ($value === '0000-00-00') {
            return null;
        }

        return $value;
    }
}
