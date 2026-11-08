<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Progres belajar User per modul (Fase 2).
 * Data modul dan soal quiz tetap berada di kode (DataDummy / DataQuiz),
 * sehingga tabel ini hanya menyimpan hasil belajar milik User.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progres_modul', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('modul_id');
            $table->string('status', 10)->default('berjalan');   // berjalan | selesai
            $table->unsignedTinyInteger('skor_terakhir')->nullable();        // jumlah jawaban benar
            $table->unsignedTinyInteger('total_soal_terakhir')->nullable();  // jumlah soal pada percobaan terakhir
            $table->unsignedTinyInteger('skor_terbaik')->nullable();
            $table->unsignedSmallInteger('jumlah_percobaan')->default(0);
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'modul_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progres_modul');
    }
};
