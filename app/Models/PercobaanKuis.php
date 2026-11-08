<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu kali User mengerjakan kuis beserta hasilnya (User -> PercobaanKuis -> Kuis). */
class PercobaanKuis extends Model
{
    protected $table = 'percobaan_kuis';

    protected $fillable = ['user_id', 'kuis_id', 'jumlah_benar', 'total_soal', 'nilai', 'lulus', 'jawaban'];

    protected function casts(): array
    {
        return ['lulus' => 'boolean', 'jawaban' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kuis(): BelongsTo
    {
        return $this->belongsTo(Kuis::class);
    }
}
