@extends('layouts.admin')

@section('judul', 'Dashboard')
@section('menu', 'dashboard')

@section('isi')
    <div class="adm-sambutan">
        <h2>Selamat datang, {{ \Illuminate\Support\Str::before($penggunaLogin->name, ' ') }} 👋</h2>
        <p>Pantau perkembangan platform IsyaratKita hari ini, {{ now()->translatedFormat('j F Y') }}.</p>
    </div>

    <section class="adm-statistik" aria-label="Ringkasan">
        <article class="adm-kartu adm-stat">
            <div class="adm-stat__kepala">Total Pengguna <span class="adm-stat__ikon"><x-admin.ikon nama="pengguna-banyak" :ukuran="18" /></span></div>
            <div class="adm-stat__nilai">{{ number_format($totalPengguna, 0, ',', '.') }}</div>
        </article>
        <article class="adm-kartu adm-stat">
            <div class="adm-stat__kepala">Total Modul <span class="adm-stat__ikon"><x-admin.ikon nama="buku" :ukuran="18" /></span></div>
            <div class="adm-stat__nilai">{{ number_format($totalModul, 0, ',', '.') }}</div>
            <div class="adm-stat__catatan adm-stat__catatan--redup">{{ $modulTerbit }} modul terbit</div>
        </article>
        <article class="adm-kartu adm-stat">
            <div class="adm-stat__kepala">Total Donasi <span class="adm-stat__ikon"><x-admin.ikon nama="donasi" :ukuran="18" /></span></div>
            <div class="adm-stat__nilai">{{ $totalDonasiTeks }}</div>
            @if ($adaSimulasi)<div class="adm-stat__catatan adm-stat__catatan--redup">Termasuk donasi simulasi</div>@endif
        </article>
    </section>

    <section class="adm-kartu adm-kartu__isi" aria-labelledby="judul-donasi-terbaru">
        <div class="adm-kartu__kepala">
            <h3 id="judul-donasi-terbaru">Donasi Terbaru</h3>
            <a href="{{ route('admin.donasi.index') }}" class="adm-tautan">Lihat semua</a>
        </div>
        <div class="adm-tabel-gulir" style="margin-top:16px;border-radius:10px;overflow:hidden">
            <table class="adm-tabel">
                <thead><tr><th>Donatur</th><th>Nominal</th><th>Metode</th><th>Tanggal</th></tr></thead>
                <tbody>
                    @forelse ($donasiTerbaru as $d)
                        <tr>
                            <td>{{ $d->nama }}</td>
                            <td>{{ $d->nominal_rupiah }}</td>
                            <td>{{ $d->metode }}@if ($d->simulasi) <span class="adm-lencana adm-lencana--kuning" style="margin-left:6px">Simulasi</span>@endif</td>
                            <td>{{ $d->tanggal->translatedFormat('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="adm-kosong"><strong>Belum ada donasi masuk</strong>Donasi yang berhasil akan tampil di sini.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
