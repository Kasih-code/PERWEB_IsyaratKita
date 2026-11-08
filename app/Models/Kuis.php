<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Pengaturan kuis satu modul + daftar pertanyaannya. */
class Kuis extends Model
{
    protected $table = 'kuis';

    protected $fillable = ['modul_id', 'status', 'urutan_soal', 'nilai_lulus', 'durasi_menit'];

    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class);
    }

    public function pertanyaan(): HasMany
    {
        return $this->hasMany(Pertanyaan::class)->orderBy('urutan')->orderBy('id');
    }

    public function percobaan(): HasMany
    {
        return $this->hasMany(PercobaanKuis::class);
    }

    public function aktif(): bool
    {
        return $this->status === 'aktif';
    }

    /** Kuis dapat dikerjakan User jika aktif dan memiliki minimal satu pertanyaan. */
    public function bisaDikerjakan(): bool
    {
        return $this->aktif() && $this->pertanyaan()->exists();
    }
}
