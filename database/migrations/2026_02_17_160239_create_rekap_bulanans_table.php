<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_rekap_list', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('date');
            $table->unsignedBigInteger('unit_id');
            $table->string('status');
            $table->text('remark')->nullable();

            $table->timestamps();

            $table->string('user');
            $table->string('ip_address')->nullable();
            $table->integer('queue')->default(0);
            $table->unsignedBigInteger('sub_unit_id')->nullable();
            $table->string('status_kepegawaian')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_rekap_list');
    }
};
