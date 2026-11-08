<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul pembelajaran (dikelola Admin, dibaca Guest/User).
 * "nomor" adalah urutan tampil (1, 2, 3, ...). Modul hanya tampil di publik
 * jika status = terbit DAN tampil_publik = true.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modul', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('nomor')->index();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('status', 10)->default('draft');          // draft | terbit
            $table->boolean('tampil_publik')->default(true);
            $table->string('cover')->nullable();                      // path pada disk "unggahan"
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modul');
    }
};
