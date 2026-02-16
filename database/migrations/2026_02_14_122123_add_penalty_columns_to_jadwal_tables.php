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
        Schema::table('jadwal_pegawais', function (Blueprint $table) {
            $table->integer('penalti_tidak_tap_in')->nullable()->after('jam_pulang');
            $table->integer('penalti_tidak_tap_out')->nullable()->after('penalti_tidak_tap_in');
        });

        Schema::table('jadwal_sub_units', function (Blueprint $table) {
            $table->integer('penalti_tidak_tap_in')->nullable();
            $table->integer('penalti_tidak_tap_out')->nullable();
        });

        Schema::table('jadwal_units', function (Blueprint $table) {
            $table->integer('penalti_tidak_tap_in')->nullable();
            $table->integer('penalti_tidak_tap_out')->nullable();
        });

        Schema::table('jadwal_dinas', function (Blueprint $table) {
            $table->integer('penalti_tidak_tap_in')->nullable();
            $table->integer('penalti_tidak_tap_out')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_pegawais', function (Blueprint $table) {
            $table->dropColumn(['penalti_tidak_tap_in', 'penalti_tidak_tap_out']);
        });

        Schema::table('jadwal_sub_units', function (Blueprint $table) {
            $table->dropColumn(['penalti_tidak_tap_in', 'penalti_tidak_tap_out']);
        });

        Schema::table('jadwal_units', function (Blueprint $table) {
            $table->dropColumn(['penalti_tidak_tap_in', 'penalti_tidak_tap_out']);
        });

        Schema::table('jadwal_dinas', function (Blueprint $table) {
            $table->dropColumn(['penalti_tidak_tap_in', 'penalti_tidak_tap_out']);
        });
    }
};
