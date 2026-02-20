<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {

            $table->timestamp('created_at')
                ->useCurrent()
                ->change();

            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('absensi_summaries', function (Blueprint $table) {

            $table->timestamp('created_at')
                ->nullable()
                ->change();

            $table->timestamp('updated_at')
                ->nullable()
                ->change();
        });
    }
};