<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Materi pembelajaran = daftar blok berurutan milik satu modul.
 * tipe: heading | teks | gambar | video
 *   heading/teks : isi pada kolom "teks"
 *   gambar       : kolom "gambar" (path) + "alt"
 *   video        : kolom "video_url" + "durasi"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materi_blok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_id')->constrained('modul')->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->string('tipe', 10);
            $table->text('teks')->nullable();
            $table->string('gambar')->nullable();
            $table->string('alt', 200)->nullable();
            $table->string('video_url', 255)->nullable();
            $table->string('durasi', 10)->nullable();
            $table->timestamps();

            $table->index(['modul_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materi_blok');
    }
};
