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
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id(); // manual ID boleh
            $table->string('nip')->nullable();
            $table->string('nik')->nullable();
            $table->string('nama');
            $table->string('status_kepegawaian');
            $table->string('id_unit');
            $table->string('id_sub_unit')->nullable();
            $table->string('id_struktur_organisasi')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->date('begin_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('x_now_x')->default(false);
            $table->string('lokasi_kerja')->nullable();
            $table->integer('order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
