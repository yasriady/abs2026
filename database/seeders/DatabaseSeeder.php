<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // $this->call([
        //     MahasiswaSeeder::class,
        // ]);

        $this->call([
            UnitCsvSeeder::class,
        ]);

        $this->call([
            SubUnitCsvSeeder::class,
        ]);

        $this->call([
            AdminUserSeeder::class,
        ]);

        $this->call([
            AdminUnitElgusfitriSeeder::class,
        ]);

        $this->call([
            DeviceCsvSeeder::class,
        ]);

        $this->call([
            HariLiburNasionalSeeder::class,
        ]);

        $this->call([
            PegawaiCsvSeeder::class,
        ]);

        $this->call([
            RolePermissionSeeder::class,
        ]);

        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
        ]);

        $this->call([
            JadwalDinasSeeder::class,
            JadwalUnitSeeder::class,
            JadwalSubUnitSeeder::class,
            JadwalPegawaiSeeder::class,
            // 
            JadwalDinasRamadhanSeeder::class,
            JadwalUnitRamadhanSeeder::class,

        ]);
    }
}
