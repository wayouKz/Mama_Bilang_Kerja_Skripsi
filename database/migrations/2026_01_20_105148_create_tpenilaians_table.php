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
        Schema::create('t_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_mapel_id')->constrained('t_jadwal_mapels')->onDelete('cascade');
            $table->enum('jenis_penilaian', ['formatif', 'sumatif']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_penilaian');
    }
};
