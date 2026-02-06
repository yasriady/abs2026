<?php

// database/migrations/xxxx_xx_xx_add_periode_to_jadwal_units_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jadwal_units', function (Blueprint $table) {

            // 1. tambahkan kolom periode
            $table->date('start_date')->nullable()->after('hari');
            $table->date('end_date')->nullable()->after('start_date');

            // 2. index BARU untuk resolver (TANPA drop unique lama)
            $table->index(
                ['unit_id', 'hari', 'start_date', 'end_date'],
                'jadwal_units_periode_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_units', function (Blueprint $table) {
            $table->dropIndex('jadwal_units_periode_idx');
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
