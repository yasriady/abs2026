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
        Schema::create('jadwal_dinas', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('hari'); // 1=Senin ... 5=Jumat
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->timestamps();

            $table->unique('hari');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_dinas');
    }
};
