<?php

namespace App\Models;

use App\Support\Unggahan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * Satu modul pembelajaran. Relasi: Admin (User) -> Modul -> MateriBlok & Kuis -> Pertanyaan -> PilihanJawaban.
 */
class Modul extends Model
{
    protected $table = 'modul';

    protected $fillable = ['nomor', 'judul', 'deskripsi', 'status', 'tampil_publik', 'cover', 'dibuat_oleh'];

    protected function casts(): array
    {
        return ['tampil_publik' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Setiap modul baru langsung punya pengaturan kuis bawaan.
        static::created(function (Modul $modul) {
            $modul->kuis()->create([]);
        });

        // Bersihkan data & berkas milik modul yang dihapus.
        static::deleting(function (Modul $modul) {
            ProgresModul::where('modul_id', $modul->id)->delete();
            Unggahan::hapus($modul->cover);
            $modul->blok()->get()->each(fn (MateriBlok $b) => Unggahan::hapus($b->gambar));
            $modul->kuis?->pertanyaan()->get()->each->hapusBerkas();
        });
    }

    /* ---------- Relasi ---------- */

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function blok(): HasMany
    {
        return $this->hasMany(MateriBlok::class)->orderBy('urutan')->orderBy('id');
    }

    public function kuis(): HasOne
    {
        return $this->hasOne(Kuis::class);
    }

    public function progres(): HasMany
    {
        return $this->hasMany(ProgresModul::class, 'modul_id');
    }

    /* ---------- Scope & bantuan ---------- */

    /** Modul yang boleh dilihat Guest/User. */
    public function scopePublik(Builder $query): Builder
    {
        return $query->where('status', 'terbit')->where('tampil_publik', true);
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('nomor')->orderBy('id');
    }

    public function dapatDilihatPublik(): bool
    {
        return $this->status === 'terbit' && $this->tampil_publik;
    }

    public function getLabelAttribute(): string
    {
        return 'Modul '.$this->nomor;
    }

    /** Judul gaya daftar modul: "Modul 1: Pengenalan Abjad A-Z". */
    public function getJudulLengkapAttribute(): string
    {
        return 'Modul '.$this->nomor.': '.$this->judul;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return Unggahan::url($this->cover);
    }

    /**
     * Susun ulang nomor menjadi 1..N tanpa celah. Jika $modulId dan $posisi diberikan,
     * modul tersebut dipindahkan ke posisi itu (modul lain bergeser).
     */
    public static function susunUlangNomor(?int $modulId = null, ?int $posisi = null): void
    {
        DB::transaction(function () use ($modulId, $posisi) {
            $semua = static::query()->urut()->get();

            if ($modulId !== null && $posisi !== null) {
                $dipindah = $semua->firstWhere('id', $modulId);
                if ($dipindah) {
                    $sisa = $semua->reject(fn ($m) => $m->id === $modulId)->values();
                    $posisi = max(1, min($posisi, $sisa->count() + 1));
                    $sisa->splice($posisi - 1, 0, [$dipindah]);
                    $semua = $sisa;
                }
            }

            foreach ($semua->values() as $indeks => $modul) {
                if ((int) $modul->nomor !== $indeks + 1) {
                    $modul->forceFill(['nomor' => $indeks + 1])->saveQuietly();
                }
            }
        });
    }
}
