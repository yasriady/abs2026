<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {
            $table->string('sumber_jadwal', 20)
                ->nullable()
                ->after('jadwal_pulang')
                ->comment('Sumber jadwal: pegawai | sub_unit | unit | dinas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {
            $table->dropColumn('sumber_jadwal');
        });
    }
};
