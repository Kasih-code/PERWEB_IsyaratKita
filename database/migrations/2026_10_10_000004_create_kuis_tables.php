<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quiz: Modul (1) -> Kuis (1) -> Pertanyaan (banyak) -> Pilihan Jawaban (banyak).
 * Hasil User disimpan pada percobaan_kuis (satu baris = satu kali mengerjakan + hasilnya).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_id')->unique()->constrained('modul')->cascadeOnDelete();
            $table->string('status', 10)->default('aktif');           // aktif | nonaktif
            $table->string('urutan_soal', 10)->default('berurutan');  // berurutan | acak
            $table->unsignedTinyInteger('nilai_lulus')->default(70);  // persen
            $table->unsignedSmallInteger('durasi_menit')->nullable(); // null = tanpa batas waktu
            $table->timestamps();
        });

        Schema::create('pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->text('teks');
            $table->string('gambar')->nullable();                     // gambar pendukung (unggahan)
            $table->string('huruf_aset', 60)->nullable();             // atau gambar dari Kelola Asset (kata/huruf)
            $table->string('tipe_jawaban', 12)->default('teks');      // teks | gambar | teks_gambar
            $table->unsignedSmallInteger('poin')->default(10);
            $table->string('kesulitan', 10)->default('dasar');        // dasar | menengah | sulit
            $table->boolean('acak_pilihan')->default(false);
            $table->text('pembahasan')->nullable();
            $table->timestamps();

            $table->index(['kuis_id', 'urutan']);
        });

        Schema::create('pilihan_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertanyaan_id')->constrained('pertanyaan')->cascadeOnDelete();
            $table->unsignedTinyInteger('urutan')->default(1);
            $table->string('teks', 255)->nullable();
            $table->string('gambar')->nullable();
            $table->boolean('benar')->default(false);
            $table->timestamps();

            $table->index(['pertanyaan_id', 'urutan']);
        });

        Schema::create('percobaan_kuis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $table->unsignedSmallInteger('jumlah_benar');
            $table->unsignedSmallInteger('total_soal');
            $table->unsignedTinyInteger('nilai');                     // persen (berdasarkan poin)
            $table->boolean('lulus');
            $table->json('jawaban')->nullable();                      // {pertanyaan_id: pilihan_id}
            $table->timestamps();

            $table->index(['user_id', 'kuis_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('percobaan_kuis');
        Schema::dropIfExists('pilihan_jawaban');
        Schema::dropIfExists('pertanyaan');
        Schema::dropIfExists('kuis');
    }
};
