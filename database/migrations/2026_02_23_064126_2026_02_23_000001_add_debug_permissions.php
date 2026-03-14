<?php
// database/migrations/2026_02_23_000001_add_debug_permissions.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up()
    {
        // Buat permission debug.access
        $permission = Permission::create(['name' => 'debug.access', 'guard_name' => 'web']);

        // Assign ke role admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }
    }

    public function down()
    {
        Permission::where('name', 'debug.access')->delete();
    }
};
