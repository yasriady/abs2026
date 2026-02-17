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
        Schema::create('etl_job_niks', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->date('date');
            $table->enum('status', ['queued', 'running', 'done', 'failed'])->default('queued');
            $table->text('log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etl_job_niks');
    }
};
