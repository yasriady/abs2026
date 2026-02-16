<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {
            $table->string('anomaly_flags', 255)
                  ->nullable()
                  ->after('notes_out')
                  ->comment('Flags anomali absensi');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {
            $table->dropColumn('anomaly_flags');
        });
    }
};

