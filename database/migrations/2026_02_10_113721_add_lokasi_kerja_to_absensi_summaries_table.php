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
            $table->string('lokasi_kerja', 255)
                ->nullable()
                ->after('valid_device_out')
                ->comment('Daftar lokasi kerja valid (device_id unit + lokasi_kerja history), comma separated');
            $table->string("valid_devices", 255)
                ->nullable()
                ->after('lokasi_kerja')
                ->comment('Daftar lokasi kerja valid (device_id unit + lokasi_kerja history), comma separated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {
            $table->dropColumn('lokasi_kerja');
            $table->dropColumn('valid_devices');
        });
    }
};
