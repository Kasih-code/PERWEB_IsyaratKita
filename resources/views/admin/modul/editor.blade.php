@extends('layouts.admin')

@section('judul', 'Editor Modul')
@section('menu', 'modul')
@section('jejak', 'Editor Modul')

@section('isi')
    @php
        $ada = $modul->exists;
        $judulHalaman = $ada ? 'Modul '.$modul->nomor.' · '.$modul->judul : 'Tambah Modul Baru';
        $kosongBlok = ['id' => null, 'tipe' => 'teks', 'teks' => '', 'alt' => '', 'video_url' => '', 'durasi' => '', 'gambar_url' => null];
        $aksiForm = $ada ? route('admin.modul.update', $modul) : route('admin.modul.store');
    @endphp

    <div class="adm-judul-halaman">
        <div>
            <h2>{{ $judulHalaman }}</h2>
            <p>Simpan draf atau terbitkan setelah seluruh materi dan kuis selesai ditinjau.</p>
        </div>
        <div class="adm-judul-halaman__aksi">
            <a href="{{ route('admin.modul.index') }}" class="adm-btn adm-btn--garis">Batal</a>
            @if ($ada)
                <a href="{{ route('modul.detail', ['id' => $modul->id, 'pratinjau' => 1]) }}" target="_blank" rel="noopener" class="adm-btn adm-btn--garis"><x-admin.ikon nama="mata" :ukuran="18" /> Pratinjau</a>
            @endif
            <button type="submit" form="form-modul" class="adm-btn adm-btn--primer"><x-admin.ikon nama="simpan" :ukuran="18" /> Simpan Perubahan</button>
        </div>
    </div>

    @if ($errors->any())
        <div class="adm-info adm-info--kuning" role="alert" style="align-items:flex-start">
            <x-admin.ikon nama="info" :ukuran="18" />
            <div><strong>Periksa kembali isian berikut:</strong>
                <ul style="margin:6px 0 0 18px;list-style:disc">
                    @foreach (collect($errors->all())->unique()->take(8) as $pesan)<li>{{ $pesan }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form id="form-modul" method="POST" action="{{ $aksiForm }}" enctype="multipart/form-data" class="adm-editor" novalidate data-editor-modul>
        @csrf
        @if ($ada) @method('PUT') @endif

        {{-- ===== Kolom kiri ===== --}}
        <div class="adm-kolom">
            <section class="adm-kartu adm-kartu__isi">
                <h3>Informasi Modul</h3>
                <div class="adm-formulir">
                    <div class="adm-bidang">
                        <label class="adm-label" for="judul">Judul modul</label>
                        <input type="text" id="judul" name="judul" class="adm-input @error('judul') adm-input--galat @enderror" value="{{ old('judul', $modul->judul) }}" maxlength="150" required>
                        @error('judul')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                    </div>
                    <div class="adm-bidang">
                        <label class="adm-label" for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" class="adm-textarea @error('deskripsi') adm-input--galat @enderror" rows="4" maxlength="1000">{{ old('deskripsi', $modul->deskripsi) }}</textarea>
                        @error('deskripsi')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>

            <section aria-labelledby="judul-materi">
                <div class="adm-materi-kepala">
                    <div>
                        <h3 id="judul-materi">Materi Pembelajaran</h3>
                        <p class="adm-kartu__sub"><span data-blok-jumlah>{{ count($blokAwal) }}</span> blok · tarik untuk mengubah urutan</p>
                    </div>
                    <div class="adm-tambah-blok">
                        <button type="button" class="adm-btn adm-btn--garis adm-btn--kecil" data-blok-tambah="heading"><x-admin.ikon nama="heading" :ukuran="16" /> Heading</button>
                        <button type="button" class="adm-btn adm-btn--garis adm-btn--kecil" data-blok-tambah="teks"><x-admin.ikon nama="teks" :ukuran="16" /> Teks</button>
                        <div class="adm-menu-wadah adm-menu-media">
                            <button type="button" class="adm-btn adm-btn--garis adm-btn--kecil" data-adm-dropdown="dd-media" aria-expanded="false"><x-admin.ikon nama="tambah" :ukuran="16" /> Media</button>
                            <div class="adm-dropdown" id="dd-media" hidden>
                                <button type="button" data-blok-tambah="gambar"><x-admin.ikon nama="gambar" :ukuran="16" /> Gambar</button>
                                <button type="button" data-blok-tambah="video"><x-admin.ikon nama="video" :ukuran="16" /> Video</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="adm-kolom" style="margin-top:16px" data-blok-daftar>
                    @foreach ($blokAwal as $i => $b)
                        @include('admin.modul._blok', ['i' => $i, 'b' => $b])
                    @endforeach
                </div>
                <div class="adm-materi-kosong" data-blok-kosong @if (count($blokAwal) > 0) hidden @endif>Belum ada blok materi. Tambahkan Heading, Teks, atau Media di atas.</div>
            </section>
        </div>

        {{-- ===== Kolom kanan ===== --}}
        <aside class="adm-sisi-kanan">
            <section class="adm-kartu adm-kartu__isi">
                <h3>Pengaturan</h3>
                <div class="adm-formulir">
                    <div class="adm-bidang">
                        <label class="adm-label" for="status">Status</label>
                        <select id="status" name="status" class="adm-select">
                            <option value="terbit" @selected(old('status', $modul->status) === 'terbit')>Terbit</option>
                            <option value="draft" @selected(old('status', $modul->status) === 'draft')>Draft</option>
                        </select>
                        @error('status')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                    </div>
                    <div class="adm-bidang">
                        <label class="adm-label" for="nomor">Urutan modul</label>
                        <select id="nomor" name="nomor" class="adm-select">
                            @for ($n = 1; $n <= $jumlahModul; $n++)
                                <option value="{{ $n }}" @selected((int) old('nomor', $modul->nomor) === $n)>Modul {{ $n }}</option>
                            @endfor
                        </select>
                        @error('nomor')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                    </div>
                    <div class="adm-saklar">
                        <span class="adm-label" id="label-publik">Tampil di publik</span>
                        <input type="hidden" name="tampil_publik" value="0">
                        <label style="display:inline-flex">
                            <input type="checkbox" name="tampil_publik" value="1" @checked((bool) old('tampil_publik', $modul->tampil_publik)) aria-labelledby="label-publik">
                            <span class="adm-saklar__tanda"></span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="adm-kartu adm-kartu__isi">
                <h3>Cover Modul</h3>
                <div class="adm-formulir">
                    @if ($modul->cover_url)
                        <img class="adm-cover" src="{{ $modul->cover_url }}" alt="Cover {{ $modul->judul }}" data-cover-pratinjau>
                    @else
                        <img class="adm-cover" alt="Pratinjau cover" data-cover-pratinjau hidden>
                        <div class="adm-cover adm-cover--kosong" data-cover-kosong><x-admin.ikon nama="gambar" :ukuran="32" /></div>
                    @endif
                    <label class="adm-btn adm-btn--garis adm-btn--penuh adm-tombol-berkas" style="margin-top:0">
                        <x-admin.ikon nama="unggah-tray" :ukuran="16" /> {{ $modul->cover ? 'Ganti Cover' : 'Pilih Cover' }}
                        <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" data-cover-berkas>
                    </label>
                    @error('cover')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                </div>
            </section>

            <section class="adm-kartu adm-kartu__isi">
                <div class="adm-baris-lencana">
                    <h3>Quiz Modul</h3>
                    <span class="adm-lencana">{{ $jumlahSoal }} soal</span>
                </div>
                @if ($ada)
                    <p class="adm-kartu__sub" style="margin-top:8px">Nilai lulus {{ $modul->kuis?->nilai_lulus ?? 70 }}% · urutan soal {{ ($modul->kuis?->urutan_soal ?? 'berurutan') === 'acak' ? 'diacak' : 'berurutan' }}.</p>
                    <a href="{{ route('admin.kuis.index', $modul) }}" class="adm-btn adm-btn--garis adm-btn--penuh" style="margin-top:16px"><x-admin.ikon nama="kuis" :ukuran="16" /> Kelola Quiz</a>
                @else
                    <p class="adm-kartu__sub" style="margin-top:8px">Simpan modul terlebih dahulu untuk mengelola quiz-nya.</p>
                @endif
            </section>
        </aside>
    </form>
@endsection

@push('modal')
    {{-- Cetakan blok untuk tombol Heading/Teks/Gambar/Video (indeks diganti oleh JS). --}}
    @foreach (['heading', 'teks', 'gambar', 'video'] as $tipeBlok)
        <template data-blok-template="{{ $tipeBlok }}">
            @include('admin.modul._blok', ['i' => '__I__', 'b' => array_merge($kosongBlok, ['tipe' => $tipeBlok])])
        </template>
    @endforeach
@endpush
