<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan role sudah tersedia
        $adminRole = Role::where('name', 'admin')->first();
        $adminUnitRole = Role::where('name', 'admin_unit')->first();
        $adminSubUnitRole = Role::where('name', 'admin_subunit')->first();
        $userRole = Role::where('name', 'user')->first();

        // 1. Super Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@system.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'unit_id' => null,
                'sub_unit_id' => null,
            ]
        );
        $admin->syncRoles([$adminRole]);

        // 2. Admin Unit
        $adminUnit = User::updateOrCreate(
            ['email' => 'admin.unit@system.id'],
            [
                'name' => 'Admin Unit',
                'password' => Hash::make('admin123'),
                'unit_id' => 1, // GANTI sesuai ID unit di tabel units
                'sub_unit_id' => null,
            ]
        );
        $adminUnit->syncRoles([$adminUnitRole]);

        // 3. Admin SubUnit
        $adminSubUnit = User::updateOrCreate(
            ['email' => 'admin.subunit@system.id'],
            [
                'name' => 'Admin SubUnit',
                'password' => Hash::make('admin123'),
                'unit_id' => 1, // GANTI sesuai ID unit
                'sub_unit_id' => 1, // GANTI sesuai ID sub_unit
            ]
        );
        $adminSubUnit->syncRoles([$adminSubUnitRole]);

        // 4. User Biasa
        $user = User::updateOrCreate(
            ['email' => 'user@system.id'],
            [
                'name' => 'User Biasa',
                'password' => Hash::make('user123'),
                'unit_id' => 1,
                'sub_unit_id' => 1,
            ]
        );
        $user->syncRoles([$userRole]);

        $this->command->info('UserSeeder berhasil dijalankan!');
    }
}
