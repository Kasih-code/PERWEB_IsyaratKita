@extends('layouts.admin')

@section('judul', 'Kelola Modul')
@section('menu', 'modul')

@section('isi')
    <div class="adm-judul-halaman">
        <div>
            <h2>Daftar Modul Pembelajaran</h2>
            <p>Susun alur belajar BISINDO, materi, dan kuis secara terstruktur.</p>
        </div>
        <div class="adm-judul-halaman__aksi">
            <a href="{{ route('admin.modul.create') }}" class="adm-btn adm-btn--primer"><x-admin.ikon nama="tambah" :ukuran="18" /> Tambah Modul</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.modul.index') }}" class="adm-filter" role="search">
        <div class="adm-input-ikon adm-filter__cari">
            <x-admin.ikon nama="cari" :ukuran="18" />
            <input type="search" name="cari" class="adm-input" value="{{ $cari }}" placeholder="Cari modul atau judul..." aria-label="Cari modul">
        </div>
        <select name="status" class="adm-select" aria-label="Filter status" onchange="this.form.submit()">
            <option value="">Semua status</option>
            <option value="terbit" @selected($status === 'terbit')>Terbit</option>
            <option value="draft" @selected($status === 'draft')>Draft</option>
        </select>
        <select name="urut" class="adm-select" aria-label="Urutkan" onchange="this.form.submit()">
            <option value="diperbarui" @selected($urut === 'diperbarui')>Terakhir diperbarui</option>
            <option value="nomor" @selected($urut === 'nomor')>Nomor modul</option>
            <option value="judul" @selected($urut === 'judul')>Judul A-Z</option>
        </select>
    </form>

    <section class="adm-tabel-kartu">
        <div class="adm-tabel-gulir">
            <table class="adm-tabel">
                <thead><tr><th>No</th><th>Modul</th><th>Judul</th><th>Materi</th><th>Soal</th><th>Status</th><th>Last Update</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($daftar as $i => $modul)
                        <tr>
                            <td class="adm-kolom-angka">{{ str_pad($daftar->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}</td>
                            <td><a href="{{ route('admin.modul.edit', $modul) }}" class="adm-tautan-modul">Modul {{ $modul->nomor }}</a></td>
                            <td><strong>{{ $modul->judul }}</strong></td>
                            <td>{{ $modul->blok_count }}</td>
                            <td>{{ $jumlahSoal[$modul->id] ?? 0 }}</td>
                            <td>
                                <span @class(['adm-lencana', 'adm-lencana--hijau' => $modul->status === 'terbit', 'adm-lencana--abu' => $modul->status !== 'terbit'])>{{ $modul->status === 'terbit' ? 'Terbit' : 'Draft' }}</span>
                                @if ($modul->status === 'terbit' && ! $modul->tampil_publik)<span class="adm-lencana adm-lencana--kuning" title="Tidak tampil di publik">Tersembunyi</span>@endif
                            </td>
                            <td>{{ $modul->updated_at->translatedFormat('d M Y') }}</td>
                            <td class="adm-kolom-aksi">
                                <span class="adm-aksi">
                                    <a href="{{ route('admin.modul.edit', $modul) }}" class="adm-aksi-ikon" aria-label="Edit {{ $modul->label }}"><x-admin.ikon nama="pensil" :ukuran="16" /></a>
                                    <a href="{{ route('admin.kuis.index', $modul) }}" class="adm-aksi-ikon adm-aksi-ikon--netral" aria-label="Kelola quiz {{ $modul->label }}"><x-admin.ikon nama="kuis" :ukuran="16" /></a>
                                    <button type="button" class="adm-aksi-ikon adm-aksi-ikon--bahaya" aria-label="Hapus {{ $modul->label }}"
                                            data-adm-hapus data-aksi="{{ route('admin.modul.destroy', $modul) }}"
                                            data-judul="Hapus {{ $modul->label }}?"
                                            data-teks="Materi, quiz, dan progres belajar User pada modul “{{ $modul->judul }}” akan ikut terhapus. Tindakan ini tidak dapat dibatalkan."
                                            data-tombol="Ya, Hapus Modul">
                                        <x-admin.ikon nama="sampah" :ukuran="16" />
                                    </button>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="adm-kosong"><strong>Belum ada modul</strong>
                            @if ($cari !== '' || $status) Tidak ada modul yang cocok dengan pencarian. @else Klik "Tambah Modul" untuk membuat modul pertama. @endif
                        </div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.paginasi :data="$daftar" satuan="Modul" />
    </section>
@endsection
