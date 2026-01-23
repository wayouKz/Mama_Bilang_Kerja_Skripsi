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
        Schema::create('t_guru_mapel_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_mapel_id')->constrained('t_guru_mapel')->onDelete('cascade');
            $table->foreignId('mapel_id')->constrained('m_mapel')->onDelete('cascade');
            $table->integer('beban_jam');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_guru_mapel_detail');
    }
};
