<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {
            $table->unsignedInteger('late_minutes')
                ->default(0)
                ->after('status_hari_final');

            $table->unsignedInteger('early_minutes')
                ->default(0)
                ->after('late_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'late_minutes',
                'early_minutes'
            ]);
        });
    }
};
