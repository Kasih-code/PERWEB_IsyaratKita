<?php

namespace App\Models;

use App\Support\Unggahan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PilihanJawaban extends Model
{
    protected $table = 'pilihan_jawaban';

    protected $fillable = ['pertanyaan_id', 'urutan', 'teks', 'gambar', 'benar'];

    protected function casts(): array
    {
        return ['benar' => 'boolean'];
    }

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(Pertanyaan::class);
    }

    public function getGambarUrlAttribute(): ?string
    {
        return Unggahan::url($this->gambar);
    }

    /** Huruf pilihan: 1 -> A, 2 -> B, ... */
    public function getHurufAttribute(): string
    {
        return chr(64 + max(1, min((int) $this->urutan, 26)));
    }

    /** Teks ringkas untuk daftar soal Admin. */
    public function getRingkasAttribute(): string
    {
        return $this->teks !== null && $this->teks !== '' ? $this->teks : 'Gambar '.$this->huruf;
    }
}
