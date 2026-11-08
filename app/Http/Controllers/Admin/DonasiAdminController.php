<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Laporan Donasi Masuk: ringkasan, filter, tabel, detail (modal), ekspor CSV, dan bukti.
 * Data berasal dari tabel donasi (diisi alur donasi User). Selama belum ada payment
 * gateway, semua donasi bertanda "simulasi" dan ditampilkan apa adanya.
 */
class DonasiAdminController extends Controller
{
    private const RENTANG_NOMINAL = [
        'kecil'  => ['Di bawah Rp 100.000', null, 99999],
        'sedang' => ['Rp 100.000 - Rp 499.999', 100000, 499999],
        'besar'  => ['Rp 500.000 ke atas', 500000, null],
    ];

    public function index(Request $request)
    {
        $filter = $this->filter($request);
        $daftar = $this->query($filter)->latest('dibayar_pada')->latest('id')->paginate(10)->withQueryString();

        $awalBulanIni = now()->startOfMonth();
        $awalBulanLalu = now()->subMonthNoOverflow()->startOfMonth();
        $bulanIni = (int) Donasi::berhasil()->where('dibayar_pada', '>=', $awalBulanIni)->sum('nominal');
        $bulanLalu = (int) Donasi::berhasil()->whereBetween('dibayar_pada', [$awalBulanLalu, $awalBulanIni->copy()->subSecond()])->sum('nominal');

        return view('admin.donasi.index', [
            'daftar'        => $daftar,
            'filter'        => $filter,
            'rentangNominal' => self::RENTANG_NOMINAL,
            'totalDonasi'   => (int) Donasi::berhasil()->sum('nominal'),
            'jumlahBerhasil' => Donasi::berhasil()->count(),
            'bulanIni'      => $bulanIni,
            'perubahanBulan' => $bulanLalu > 0 ? round(($bulanIni - $bulanLalu) / $bulanLalu * 100, 1) : null,
            'adaSimulasi'   => Donasi::where('simulasi', true)->exists(),
        ]);
    }

    /** Ekspor CSV mengikuti filter yang sedang dipakai. */
    public function export(Request $request): StreamedResponse
    {
        $filter = $this->filter($request);
        $nama = 'laporan-donasi-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($filter) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['Kode', 'Donatur', 'Email', 'WhatsApp', 'Nominal (Rp)', 'Metode', 'Status', 'Simulasi', 'Tanggal', 'Referensi']);

            $this->query($filter)->latest('dibayar_pada')->latest('id')->chunk(200, function ($baris) use ($out) {
                foreach ($baris as $d) {
                    fputcsv($out, array_map([self::class, 'amankanCsv'], [
                        $d->kode, $d->nama, $d->email, $d->whatsapp, $d->nominal, $d->metode,
                        $d->status, $d->simulasi ? 'ya' : 'tidak', $d->tanggal->format('Y-m-d H:i'), $d->referensi ?? '',
                    ]));
                }
            });

            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Bukti donasi (berkas HTML mandiri yang dapat dicetak). */
    public function bukti(Donasi $donasi)
    {
        $html = view('admin.donasi.bukti', ['donasi' => $donasi])->render();

        return response($html, 200, [
            'Content-Type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bukti-'.$donasi->kode.'.html"',
        ]);
    }

    /* ------------------------------------------------------------------ */

    private function filter(Request $request): array
    {
        $ambilTanggal = function (?string $nilai): ?string {
            if (! $nilai || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai)) {
                return null;
            }
            try {
                return Carbon::createFromFormat('Y-m-d', $nilai)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        };

        $status = $request->query('status');
        $nominal = $request->query('nominal');

        return [
            'cari'    => trim((string) $request->query('cari', '')),
            'dari'    => $ambilTanggal($request->query('dari')),
            'sampai'  => $ambilTanggal($request->query('sampai')),
            'status'  => in_array($status, ['berhasil', 'menunggu'], true) ? $status : null,
            'nominal' => array_key_exists((string) $nominal, self::RENTANG_NOMINAL) ? $nominal : null,
        ];
    }

    private function query(array $f): Builder
    {
        return Donasi::query()
            ->when($f['cari'] !== '', function ($q) use ($f) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $f['cari']).'%';
                $q->where(fn ($w) => $w->where('nama', 'like', $like)->orWhere('email', 'like', $like)
                    ->orWhere('whatsapp', 'like', $like)->orWhere('kode', 'like', $like));
            })
            ->when($f['dari'], fn ($q, $v) => $q->whereRaw('date(coalesce(dibayar_pada, created_at)) >= ?', [$v]))
            ->when($f['sampai'], fn ($q, $v) => $q->whereRaw('date(coalesce(dibayar_pada, created_at)) <= ?', [$v]))
            ->when($f['status'], fn ($q, $v) => $q->where('status', $v))
            ->when($f['nominal'], function ($q, $kunci) {
                [, $min, $maks] = self::RENTANG_NOMINAL[$kunci];
                $min !== null && $q->where('nominal', '>=', $min);
                $maks !== null && $q->where('nominal', '<=', $maks);
            });
    }

    /** Mencegah CSV injection: sel yang diawali = + - @ diberi tanda kutip. */
    public static function amankanCsv(mixed $nilai): mixed
    {
        if (is_string($nilai) && $nilai !== '' && in_array($nilai[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$nilai;
        }

        return $nilai;
    }
}
