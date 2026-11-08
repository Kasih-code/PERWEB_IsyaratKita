<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asset gambar isyarat BISINDO (menu Kelola Asset).
 * "kata" berisi satu huruf (mis. "A") atau satu kata/ungkapan (mis. "Terima Kasih").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aset_bisindo', function (Blueprint $table) {
            $table->id();
            $table->string('kata', 60)->index();
            $table->string('slug', 80)->index();
            $table->string('berkas');                                 // path pada disk "unggahan"
            $table->string('nama_berkas', 150);                       // nama berkas asli (untuk tampilan)
            $table->string('status', 10)->default('aktif');           // aktif | nonaktif
            $table->foreignId('diunggah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aset_bisindo');
    }
};
