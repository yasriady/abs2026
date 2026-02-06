<?php

// database/migrations/xxxx_xx_xx_fix_unique_index_on_jadwal_dinas.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jadwal_dinas', function (Blueprint $table) {
            // drop unique index lama
            $table->dropUnique('jadwal_dinas_hari_unique');

            // index baru (non-unique)
            $table->index(['hari', 'start_date', 'end_date'], 'jadwal_dinas_periode_idx');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_dinas', function (Blueprint $table) {
            $table->dropIndex('jadwal_dinas_periode_idx');
            $table->unique('hari');
        });
    }
};
