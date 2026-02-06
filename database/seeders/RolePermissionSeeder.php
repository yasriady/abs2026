<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Unit
            'unit.view',
            'unit.create',
            'unit.update',
            'unit.delete',

            // SubUnit
            'subunit.view',
            'subunit.create',
            'subunit.update',
            'subunit.delete',

            // Mahasiswa
            'mahasiswa.view',
            'mahasiswa.create',
            'mahasiswa.update',
            'mahasiswa.delete',
            'mahasiswa.export',

            // Pegawai
            'pegawai.view',
            'pegawai.create',
            'pegawai.update',
            'pegawai.delete',
            'pegawai.export',

            // Absensi
            'absensi.view',
            'rekap.view',
            'jamkerja.view',
            'jamkerja.update',

            // Sistem
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'mesin.view',
            'libur.view',
            'libur.create',
            'libur.update',
            'libur.delete',
            'perangkat.view',
            'perangkat.create',
            'perangkat.update',
            'perangkat.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ROLES
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $adminUnit = Role::firstOrCreate(['name' => 'admin_unit']);
        $adminSubUnit = Role::firstOrCreate(['name' => 'admin_subunit']);
        $user = Role::firstOrCreate(['name' => 'user']);

        // ADMIN = ALL
        $admin->givePermissionTo(Permission::all());

        // ADMIN UNIT
        $adminUnit->givePermissionTo([
            'unit.view',
            'subunit.view',
            'subunit.create',
            'subunit.update',
            'mahasiswa.view',
            'mahasiswa.create',
            'mahasiswa.update',
            'mahasiswa.delete',
            'mahasiswa.export',
            'pegawai.view',
            'pegawai.create',
            'pegawai.update',
            'pegawai.delete',
            'pegawai.export',
            'rekap.view',
            'absensi.view',
            'jamkerja.view',
            'jamkerja.update',
            'user.view',
            'user.create',
            'user.update',
            'perangkat.view',
            'perangkat.create',
            'perangkat.update',
        ]);

        // ADMIN SUBUNIT
        $adminSubUnit->givePermissionTo([
            'subunit.view',
            'mahasiswa.view',
            'mahasiswa.create',
            'mahasiswa.update',
            'mahasiswa.delete',
            'mahasiswa.export',
            'pegawai.view',
            'pegawai.create',
            'pegawai.update',
            'pegawai.delete',
            'pegawai.export',
            'rekap.view',
            'absensi.view',
        ]);

        // USER
        $user->givePermissionTo([
            'mahasiswa.view',
            'pegawai.view',
            'absensi.view',
            'rekap.view',
        ]);
    }
}
