<?php

namespace App\Models;

use App\Support\AsetGestur;
use App\Support\Unggahan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pertanyaan extends Model
{
    protected $table = 'pertanyaan';

    public const TIPE_JAWABAN = ['teks' => 'Teks', 'gambar' => 'Gambar', 'teks_gambar' => 'Teks + gambar'];
    public const KESULITAN = ['dasar' => 'Dasar', 'menengah' => 'Menengah', 'sulit' => 'Sulit'];

    protected $fillable = [
        'kuis_id', 'urutan', 'teks', 'gambar', 'huruf_aset', 'tipe_jawaban',
        'poin', 'kesulitan', 'acak_pilihan', 'pembahasan',
    ];

    protected function casts(): array
    {
        return ['acak_pilihan' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::deleting(fn (Pertanyaan $p) => $p->hapusBerkas());
    }

    public function kuis(): BelongsTo
    {
        return $this->belongsTo(Kuis::class);
    }

    public function pilihan(): HasMany
    {
        return $this->hasMany(PilihanJawaban::class)->orderBy('urutan')->orderBy('id');
    }

    /** URL gambar pendukung: unggahan sendiri, atau gambar dari Kelola Asset. */
    public function getGambarUrlAttribute(): ?string
    {
        if ($this->gambar) {
            return Unggahan::url($this->gambar);
        }

        return $this->huruf_aset ? AsetGestur::url($this->huruf_aset) : null;
    }

    /** Apakah soal ini memakai gambar (agar placeholder tetap tampil walau gambar belum ada). */
    public function getMemilikiGambarAttribute(): bool
    {
        return $this->gambar !== null || $this->huruf_aset !== null
            || $this->pilihan->contains(fn ($p) => $p->gambar !== null);
    }

    public function hapusBerkas(): void
    {
        Unggahan::hapus($this->gambar);
        $this->pilihan()->get()->each(fn (PilihanJawaban $p) => Unggahan::hapus($p->gambar));
    }
}
