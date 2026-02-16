<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('absensi_summaries', function (Blueprint $table) {
            $table->id();

            // =====================
            // IDENTITAS
            // =====================
            $table->string('nik', 30);
            $table->date('date');

            // =====================
            // WAKTU ABSENSI
            // =====================
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();

            // =====================
            // METADATA TIME IN
            // =====================
            $table->string('filename_in')->nullable();
            $table->string('device_id_in')->nullable();
            $table->decimal('lat_in', 10, 7)->nullable();
            $table->decimal('long_in', 10, 7)->nullable();
            $table->string('machine_id_in')->nullable();

            // =====================
            // METADATA TIME OUT
            // =====================
            $table->string('filename_out')->nullable();
            $table->string('device_id_out')->nullable();
            $table->decimal('lat_out', 10, 7)->nullable();
            $table->decimal('long_out', 10, 7)->nullable();
            $table->string('machine_id_out')->nullable();

            // =====================
            // STATUS
            // =====================
            $table->boolean('is_final')->default(false);

            $table->timestamps();

            // =====================
            // ANTI DUPLIKASI
            // =====================
            $table->unique(['nik', 'date'], 'uniq_absensi_harian');
            $table->index(['date']);
            $table->index(['nik']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_summaries');
    }
};
