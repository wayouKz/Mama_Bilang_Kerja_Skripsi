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
        Schema::create('absen_siswa_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absen_siswa_id')->constrained('absen_siswas')->onDelete('cascade');
            $table->foreignId('t_siswa_id')->constrained('t_siswa')->onDelete('cascade');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absen_siswa_details');
    }
};
