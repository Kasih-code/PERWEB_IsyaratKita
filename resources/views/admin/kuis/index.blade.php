@extends('layouts.admin')

@section('judul', 'Quiz Modul')
@section('menu', 'modul')
@section('jejak', 'Quiz Modul')

@section('isi')
    <div class="adm-judul-halaman">
        <div>
            <h2>Quiz · {{ $modul->label }}</h2>
            <p>{{ $modul->judul }} · {{ $soal->count() }} soal · nilai lulus {{ $kuis->nilai_lulus }}%</p>
        </div>
        <div class="adm-judul-halaman__aksi">
            <a href="{{ route('admin.modul.edit', $modul) }}" class="adm-btn adm-btn--garis"><x-admin.ikon nama="panah-kiri" :ukuran="16" /> Editor Modul</a>
            <a href="{{ route('admin.kuis.soal.create', $modul) }}" class="adm-btn adm-btn--primer"><x-admin.ikon nama="tambah" :ukuran="18" /> Tambah Soal</a>
            <button type="submit" form="form-kuis" class="adm-btn adm-btn--primer"><x-admin.ikon nama="simpan" :ukuran="18" /> Simpan Soal</button>
        </div>
    </div>

    @if ($errors->any())
        <div class="adm-info adm-info--kuning" role="alert"><x-admin.ikon nama="info" :ukuran="18" /> {{ $errors->first() }}</div>
    @endif
    @if ($kuis->aktif() && $soal->isEmpty())
        <div class="adm-info adm-info--kuning"><x-admin.ikon nama="info" :ukuran="18" /> Quiz aktif tetapi belum punya soal, sehingga belum bisa dikerjakan User. Tambahkan minimal satu soal.</div>
    @endif

    <form id="form-kuis" method="POST" action="{{ route('admin.kuis.update', $modul) }}" class="adm-kuis-pengaturan" novalidate>
        @csrf @method('PUT')
        <div class="adm-bidang">
            <label class="adm-label" for="status">Status quiz</label>
            <select id="status" name="status" class="adm-select">
                <option value="aktif" @selected(old('status', $kuis->status) === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(old('status', $kuis->status) === 'nonaktif')>Nonaktif</option>
            </select>
        </div>
        <div class="adm-bidang">
            <label class="adm-label" for="urutan_soal">Urutan soal</label>
            <select id="urutan_soal" name="urutan_soal" class="adm-select">
                <option value="berurutan" @selected(old('urutan_soal', $kuis->urutan_soal) === 'berurutan')>Berurutan</option>
                <option value="acak" @selected(old('urutan_soal', $kuis->urutan_soal) === 'acak')>Acak</option>
            </select>
        </div>
        <div class="adm-bidang">
            <label class="adm-label" for="nilai_lulus">Nilai lulus</label>
            <div class="adm-input-akhiran">
                <input type="number" id="nilai_lulus" name="nilai_lulus" class="adm-input @error('nilai_lulus') adm-input--galat @enderror" min="1" max="100" value="{{ old('nilai_lulus', $kuis->nilai_lulus) }}" required>
                <span>%</span>
            </div>
            @error('nilai_lulus')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
        </div>
        <div class="adm-bidang">
            <label class="adm-label" for="durasi_menit">Durasi</label>
            <div class="adm-input-akhiran">
                <input type="number" id="durasi_menit" name="durasi_menit" class="adm-input @error('durasi_menit') adm-input--galat @enderror" min="1" max="600" value="{{ old('durasi_menit', $kuis->durasi_menit) }}" placeholder="Tanpa batas">
                <span>menit</span>
            </div>
            @error('durasi_menit')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
        </div>
    </form>

    <section class="adm-kolom" aria-label="Daftar soal">
        @forelse ($soal as $i => $butir)
            @php $benar = $butir->pilihan->firstWhere('benar', true); @endphp
            <article class="adm-kartu adm-soal">
                <span class="adm-soal__nomor">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <div>
                    <h3 class="adm-soal__teks">{{ $butir->teks }}</h3>
                    <div class="adm-soal__pilihan">
                        @foreach ($butir->pilihan as $pilihan)<span>{{ $pilihan->huruf }}. {{ $pilihan->ringkas }}</span>@endforeach
                    </div>
                    @if ($benar)
                        <span class="adm-soal__benar"><x-admin.ikon nama="centang-lingkaran" :ukuran="16" /> Jawaban benar: {{ $benar->huruf }}. {{ $benar->ringkas }}</span>
                    @endif
                </div>
                <div class="adm-soal__aksi">
                    @if ($butir->memiliki_gambar)<span class="adm-lencana">Dengan gambar</span>@endif
                    <span class="adm-urut">
                        <form method="POST" action="{{ route('admin.kuis.soal.pindah', [$modul, $butir]) }}">@csrf<input type="hidden" name="arah" value="naik"><button type="submit" aria-label="Naikkan soal" @disabled($loop->first)><x-admin.ikon nama="chevron-atas" :ukuran="14" /></button></form>
                        <form method="POST" action="{{ route('admin.kuis.soal.pindah', [$modul, $butir]) }}">@csrf<input type="hidden" name="arah" value="turun"><button type="submit" aria-label="Turunkan soal" @disabled($loop->last)><x-admin.ikon nama="chevron-bawah" :ukuran="14" /></button></form>
                    </span>
                    <a href="{{ route('admin.kuis.soal.edit', [$modul, $butir]) }}" class="adm-btn adm-btn--garis adm-btn--kecil"><x-admin.ikon nama="pensil" :ukuran="16" /> Edit</a>
                    <button type="button" class="adm-aksi-ikon adm-aksi-ikon--bahaya" aria-label="Hapus soal {{ $i + 1 }}"
                            data-adm-hapus data-aksi="{{ route('admin.kuis.soal.destroy', [$modul, $butir]) }}"
                            data-judul="Hapus soal {{ $i + 1 }}?"
                            data-teks="Soal beserta pilihan jawabannya akan dihapus. Tindakan ini tidak dapat dibatalkan."
                            data-tombol="Ya, Hapus Soal"><x-admin.ikon nama="sampah" :ukuran="16" /></button>
                </div>
            </article>
        @empty
            <div class="adm-kartu"><div class="adm-kosong"><strong>Belum ada soal</strong>Klik "Tambah Soal" untuk membuat soal pertama.</div></div>
        @endforelse
    </section>
@endsection
