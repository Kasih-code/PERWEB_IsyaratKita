<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const PERAN_USER = 'user';
    public const PERAN_ADMIN = 'admin';

    /** Akun baru selalu berperan "user"; Admin dibuat lewat seeder/artisan, bukan form publik. */
    protected $attributes = ['role' => self::PERAN_USER];

    /**
     * Kolom yang boleh diisi massal. "role" SENGAJA tidak ada di sini agar
     * tidak bisa diubah lewat input form (mencegah user biasa menjadi admin).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'whatsapp',
        'foto',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'terakhir_login_pada' => 'datetime',
            'sandi_diubah_pada'   => 'datetime',
        ];
    }

    public function progresModul(): HasMany
    {
        return $this->hasMany(ProgresModul::class);
    }

    public function percobaanKuis(): HasMany
    {
        return $this->hasMany(PercobaanKuis::class);
    }

    public function donasi(): HasMany
    {
        return $this->hasMany(Donasi::class);
    }

    /** Modul yang dibuat oleh akun ini (khusus Admin). */
    public function modulDibuat(): HasMany
    {
        return $this->hasMany(Modul::class, 'dibuat_oleh');
    }

    public function adalahAdmin(): bool
    {
        return $this->role === self::PERAN_ADMIN;
    }

    /** Dua huruf awal untuk avatar, mis. "Andi Pratama" -> "AP". */
    public function inisial(): string
    {
        $kata = preg_split('/\s+/', trim($this->name)) ?: [];
        $huruf = Str::upper(Str::substr($kata[0] ?? '', 0, 1).Str::substr($kata[1] ?? '', 0, 1));

        return $huruf !== '' ? $huruf : 'AD';
    }

    public function labelPeran(): string
    {
        return $this->adalahAdmin() ? 'Super Admin' : 'Pengguna';
    }
}
