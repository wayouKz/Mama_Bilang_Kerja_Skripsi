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
        Schema::create('t_siswa_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('t_siswa_id')->constrained('t_siswa')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('m_siswa')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_siswa_details');
    }
};
