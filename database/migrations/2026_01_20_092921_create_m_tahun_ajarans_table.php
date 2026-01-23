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
        Schema::create('m_tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_ajaran', 50);
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->string('keterangan', 255)->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_tahun_ajaran');
    }
};
