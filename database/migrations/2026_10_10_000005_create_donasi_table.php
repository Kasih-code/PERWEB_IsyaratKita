<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Donasi User. Belum ada payment gateway, sehingga setiap donasi yang dibuat dari
 * alur QRIS saat ini ditandai simulasi = true (tidak ada uang yang dipindahkan).
 * Jika kelak terhubung ke gateway, kolom ini bernilai false untuk transaksi nyata.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();                     // contoh: IK-DON-20261002-0184
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama', 100);
            $table->string('email', 255);
            $table->string('whatsapp', 20);
            $table->unsignedInteger('nominal');                       // rupiah
            $table->string('metode', 20)->default('qris');
            $table->string('status', 10)->default('menunggu');        // menunggu | berhasil
            $table->boolean('simulasi')->default(true);
            $table->string('referensi', 60)->nullable();
            $table->unsignedInteger('biaya_layanan')->default(0);
            $table->timestamp('dibayar_pada')->nullable();
            $table->timestamps();

            $table->index(['status', 'dibayar_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donasi');
    }
};
