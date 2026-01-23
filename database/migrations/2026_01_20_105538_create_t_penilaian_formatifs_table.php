<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_penilaian_formatif', function (Blueprint $table) {
            $table->id();
            $table->foreignId('t_penilaian_id')->constrained('t_penilaian')->onDelete('cascade');
            $table->foreignId('t_siswa_id')->constrained('t_siswa')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_penilaian_formatif');
    }
};
