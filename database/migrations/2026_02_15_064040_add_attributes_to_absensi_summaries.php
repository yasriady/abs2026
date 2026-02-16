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
            $table->string('attribute_in', 50)
                ->nullable()
                ->after('final_note');

            $table->string('attribute_out', 50)
                ->nullable()
                ->after('attribute_in');

            $table->index('attribute_in');
            $table->index('attribute_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {
            $table->dropIndex(['attribute_in']);
            $table->dropIndex(['attribute_out']);

            $table->dropColumn([
                'attribute_in',
                'attribute_out'
            ]);
        });
    }
};
