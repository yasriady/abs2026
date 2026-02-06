<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUnitElgusfitriSeeder extends Seeder
{
    public function run(): void
    {
        // Cari unit berdasarkan nama
        $unit = Unit::where('unit', 'Dinas Komunikasi, Informatika, Statistik Dan Persandian')->first();

        if (!$unit) {
            $this->command->error('Unit tidak ditemukan: Dinas Komunikasi, Informatika, Statistik Dan Persandian');
            return;
        }

        // Buat atau update user
        $user = User::updateOrCreate(
            ['email' => 'elgusfitri@kampus.ac.id'],
            [
                'name' => 'elgusfitri',
                'password' => Hash::make('admin123'),
                'unit_id' => $unit->id,
                'sub_unit_id' => null,
            ]
        );

        // Assign role admin_unit
        $user->syncRoles(['admin_unit']);

        $this->command->info('User elgusfitri (admin_unit) berhasil dibuat untuk unit: ' . $unit->unit);
    }
}
