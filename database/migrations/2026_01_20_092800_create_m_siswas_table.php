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
        Schema::create('m_siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nama_siswa', 100);
            $table->string('nisn', 50)->unique();
            $table->string('alamat', 255)->nullable();
            $table->string('no_telp', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_siswa');
    }
};
