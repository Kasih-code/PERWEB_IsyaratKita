<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pada Fase 2 modul_id hanya 1-5 (tinyint). Mulai Fase 3 modul berasal dari tabel
 * "modul" dan Admin dapat menambah/menghapus modul, sehingga id dapat melebihi 255.
 * Tidak memakai foreign key agar data progres Fase 2 tidak ditolak sebelum seeding;
 * penghapusan progres saat modul dihapus ditangani oleh model Modul.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progres_modul', function (Blueprint $table) {
            $table->unsignedBigInteger('modul_id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('progres_modul', function (Blueprint $table) {
            $table->unsignedTinyInteger('modul_id')->change();
        });
    }
};
