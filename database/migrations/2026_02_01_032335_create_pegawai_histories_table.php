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
        Schema::create('pegawai_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_pegawai_id')->constrained()->cascadeOnDelete();

            $table->enum('status_kepegawaian', [
                'asn',
                'pns',
                'pppk',
                'pppk-pw',
                'thl',
                'nib'
            ]);

            $table->foreignId('id_unit');
            $table->foreignId('id_sub_unit')->nullable();
            $table->foreignId('id_struktur_organisasi')->nullable();

            $table->date('begin_date');
            $table->date('end_date')->nullable();

            $table->boolean('is_active')->default(false);
            $table->string('lokasi_kerja')->nullable();
            $table->integer('order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai_histories');
    }
};
