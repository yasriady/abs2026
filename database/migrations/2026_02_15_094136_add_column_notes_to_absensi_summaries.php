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
            $table->text('notes_hari')->nullable()->after('attribute_out');
            $table->text('notes_in')->nullable()->after('notes_hari');
            $table->text('notes_out')->nullable()->after('notes_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {
            $table->dropColumn(['notes_hari', 'notes_in', 'notes_out']);
        });
    }
};
