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
        Schema::create('jadwal_sub_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_unit_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('hari'); // 1=Senin ... 5=Jumat
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->timestamps();

            $table->unique(['sub_unit_id', 'hari']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_sub_units');
    }
};
