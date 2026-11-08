<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 (Admin): peran akun + data profil tambahan.
 *  - role              : "user" (default) atau "admin"
 *  - whatsapp, foto    : tampil pada profil Admin
 *  - terakhir_login_pada, sandi_diubah_pada : informasi pada halaman Admin Profile
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 10)->default('user')->after('password')->index();
            $table->string('whatsapp', 20)->nullable()->after('email');
            $table->string('foto')->nullable()->after('whatsapp');
            $table->timestamp('terakhir_login_pada')->nullable();
            $table->timestamp('sandi_diubah_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'whatsapp', 'foto', 'terakhir_login_pada', 'sandi_diubah_pada']);
        });
    }
};
