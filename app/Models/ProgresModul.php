<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresModul extends Model
{
    protected $table = 'progres_modul';

    protected $fillable = [
        'user_id',
        'modul_id',
        'status',
        'skor_terakhir',
        'total_soal_terakhir',
        'skor_terbaik',
        'jumlah_percobaan',
        'selesai_pada',
    ];

    protected function casts(): array
    {
        return [
            'selesai_pada' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class, 'modul_id');
    }

    public function selesai(): bool
    {
        return $this->status === 'selesai';
    }
}
