<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // jadwal_pegawais
        Schema::table('jadwal_pegawais', function (Blueprint $table) {
            $table->time('batas_in')->nullable()->after('jam_pulang');
            $table->time('batas_out')->nullable()->after('batas_in');
            $table->integer('toleransi_telat_menit')->nullable();
            $table->integer('toleransi_pulang_cepat_menit')->nullable();
        });

        // jadwal_sub_units
        Schema::table('jadwal_sub_units', function (Blueprint $table) {
            $table->time('batas_in')->nullable()->after('jam_pulang');
            $table->time('batas_out')->nullable()->after('batas_in');
            $table->integer('toleransi_telat_menit')->nullable();
            $table->integer('toleransi_pulang_cepat_menit')->nullable();
        });

        // jadwal_units
        Schema::table('jadwal_units', function (Blueprint $table) {
            $table->time('batas_in')->nullable()->after('jam_pulang');
            $table->time('batas_out')->nullable()->after('batas_in');
            $table->integer('toleransi_telat_menit')->nullable();
            $table->integer('toleransi_pulang_cepat_menit')->nullable();
        });

        // jadwal_dinas
        Schema::table('jadwal_dinas', function (Blueprint $table) {
            $table->time('batas_in')->nullable()->after('jam_pulang');
            $table->time('batas_out')->nullable()->after('batas_in');
            $table->integer('toleransi_telat_menit')->nullable();
            $table->integer('toleransi_pulang_cepat_menit')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_pegawais', function (Blueprint $table) {
            // $table->dropColumn(['batas_in', 'batas_out']);
            $table->dropColumn(['batas_in', 'batas_out', 'toleransi_telat_menit', 'toleransi_pulang_cepat_menit']);
        });

        Schema::table('jadwal_sub_units', function (Blueprint $table) {
            // $table->dropColumn(['batas_in', 'batas_out']);
            $table->dropColumn(['batas_in', 'batas_out', 'toleransi_telat_menit', 'toleransi_pulang_cepat_menit']);
        });

        Schema::table('jadwal_units', function (Blueprint $table) {
            // $table->dropColumn(['batas_in', 'batas_out']);
            $table->dropColumn(['batas_in', 'batas_out', 'toleransi_telat_menit', 'toleransi_pulang_cepat_menit']);
        });

        Schema::table('jadwal_dinas', function (Blueprint $table) {
            // $table->dropColumn(['batas_in', 'batas_out']);
            $table->dropColumn(['batas_in', 'batas_out', 'toleransi_telat_menit', 'toleransi_pulang_cepat_menit']);
        });
    }
};
