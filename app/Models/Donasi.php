<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Donasi User. Selama belum ada payment gateway, semua donasi bertanda simulasi.
 */
class Donasi extends Model
{
    protected $table = 'donasi';

    protected $fillable = [
        'kode', 'user_id', 'nama', 'email', 'whatsapp', 'nominal', 'metode',
        'status', 'simulasi', 'referensi', 'biaya_layanan', 'dibayar_pada',
    ];

    protected function casts(): array
    {
        return ['simulasi' => 'boolean', 'dibayar_pada' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeBerhasil(Builder $query): Builder
    {
        return $query->where('status', 'berhasil');
    }

    public function sudahBerhasil(): bool
    {
        return $this->status === 'berhasil';
    }

    /** Kode unik, mis. IK-DON-20261002-0184 (urutan harian 4 digit). */
    public static function buatKode(): string
    {
        $tanggal = now()->format('Ymd');
        $urutan = static::whereDate('created_at', now()->toDateString())->count() + 1;

        do {
            $kode = sprintf('IK-DON-%s-%04d', $tanggal, $urutan++);
        } while (static::where('kode', $kode)->exists());

        return $kode;
    }

    public static function rupiah(int|float $angka): string
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }

    public function getNominalRupiahAttribute(): string
    {
        return self::rupiah($this->nominal);
    }

    public function getTanggalAttribute(): \Illuminate\Support\Carbon
    {
        return $this->dibayar_pada ?? $this->created_at;
    }
}
