<?php

namespace App\Models;

use App\Support\Unggahan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Gambar isyarat BISINDO untuk satu huruf atau satu kata (Kamus Visual). */
class AsetBisindo extends Model
{
    protected $table = 'aset_bisindo';

    protected $fillable = ['kata', 'slug', 'berkas', 'nama_berkas', 'status', 'diunggah_oleh'];

    protected static function booted(): void
    {
        static::deleting(fn (AsetBisindo $a) => Unggahan::hapus($a->berkas));
    }

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function getUrlAttribute(): ?string
    {
        return Unggahan::url($this->berkas);
    }

    /** Satu huruf (A-Z) atau kata/ungkapan. */
    public function getJenisAttribute(): string
    {
        return preg_match('/^[A-Za-z]$/', $this->kata) ? 'huruf' : 'kata';
    }
}
