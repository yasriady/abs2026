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
        Schema::create('jadwal_pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 30);
            $table->date('date');
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->timestamps();

            $table->unique(['nik', 'date']);
            $table->index('nik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pegawais');
    }
};
