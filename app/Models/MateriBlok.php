<?php

namespace App\Models;

use App\Support\Unggahan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu blok materi: heading | teks | gambar | video. */
class MateriBlok extends Model
{
    protected $table = 'materi_blok';

    public const TIPE = ['heading', 'teks', 'gambar', 'video'];

    protected $fillable = ['modul_id', 'urutan', 'tipe', 'teks', 'gambar', 'alt', 'video_url', 'durasi'];

    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class);
    }

    public function getGambarUrlAttribute(): ?string
    {
        return Unggahan::url($this->gambar);
    }

    /**
     * Alamat embed untuk video YouTube (youtu.be / youtube.com/watch). Selain itu null
     * (halaman publik menampilkan tautan biasa). Hanya host YouTube yang di-embed.
     */
    public function getVideoEmbedAttribute(): ?string
    {
        $url = (string) $this->video_url;
        if (preg_match('~^https?://(?:www\.)?(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{11})(?:[?&#/]|$)~', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1];
        }

        return null;
    }
}
