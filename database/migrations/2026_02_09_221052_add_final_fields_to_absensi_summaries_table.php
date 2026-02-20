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
            /**
             * =====================================================
             * JAM FINAL (pengganti accessor time_in_final / time_out_final)
             * =====================================================
             */
            $table->time('time_in_final')
                ->nullable()
                ->after('time_in');

            $table->time('time_out_final')
                ->nullable()
                ->after('time_out');

            /**
             * =====================================================
             * STATUS FINAL (pengganti seluruh accessor status_*)
             * =====================================================
             */
            $table->string('status_masuk_final', 64)
                ->nullable()
                ->after('time_in_final');

            $table->string('status_pulang_final', 64)
                ->nullable()
                ->after('time_out_final');

            $table->string('status_hari_final', 64)
                ->nullable()
                ->after('status_pulang_final');

            /**
             * =====================================================
             * SUMBER JAM (MESIN / ADMIN / AUTO)
             * =====================================================
             */
            $table->enum('time_in_source', ['MESIN', 'ADMIN', 'AUTO'])
                ->default('MESIN')
                ->after('status_hari_final');

            $table->enum('time_out_source', ['MESIN', 'ADMIN', 'AUTO'])
                ->default('MESIN')
                ->after('time_in_source');

            /**
             * =====================================================
             * DESKRIPSI DEVICE (mengganti relasi + accessor device)
             * =====================================================
             */
            $table->string('device_desc_in', 100)
                ->nullable()
                ->after('device_id_in');

            $table->string('device_desc_out', 100)
                ->nullable()
                ->after('device_id_out');

            /**
             * =====================================================
             * VALIDASI DEVICE (opsional tapi kuat)
             * =====================================================
             */
            $table->boolean('valid_device_in')
                ->default(true)
                ->after('device_desc_in');

            $table->boolean('valid_device_out')
                ->default(true)
                ->after('device_desc_out');

            /**
             * =====================================================
             * SNAPSHOT JADWAL (opsional, anti-resolver)
             * =====================================================
             */
            $table->time('jadwal_masuk')
                ->nullable()
                ->after('valid_device_out');

            $table->time('jadwal_pulang')
                ->nullable()
                ->after('jadwal_masuk');

            /**
             * =====================================================
             * CATATAN FINAL (audit & debug)
             * =====================================================
             */
            $table->string('final_note', 255)
                ->nullable()
                ->after('jadwal_pulang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'time_in_final',
                'time_out_final',
                'status_masuk_final',
                'status_pulang_final',
                'status_hari_final',
                'time_in_source',
                'time_out_source',
                'device_desc_in',
                'device_desc_out',
                'valid_device_in',
                'valid_device_out',
                'jadwal_masuk',
                'jadwal_pulang',
                'final_note',
            ]);
        });
    }
};
