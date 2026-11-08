@extends('layouts.admin')

@section('judul', 'Editor Soal')
@section('menu', 'modul')
@section('jejak', 'Editor Soal')

@section('isi')
    @php
        $ada = $pertanyaan->exists;
        $tipe = old('tipe_jawaban', $pertanyaan->tipe_jawaban);
        $aksi = $ada ? route('admin.kuis.soal.update', [$modul, $pertanyaan]) : route('admin.kuis.soal.store', $modul);
        $urlGambar = $pertanyaan->gambar_url;
        $dipilihAset = old('huruf_aset', $pertanyaan->huruf_aset);
    @endphp

    <div class="adm-judul-halaman">
        <div>
            <h2>{{ $ada ? 'Edit Soal Quiz' : 'Tambah Soal Quiz' }}</h2>
            <p>{{ $modul->label }} · Pertanyaan {{ $nomor }}</p>
        </div>
        <div class="adm-judul-halaman__aksi">
            <a href="{{ route('admin.kuis.index', $modul) }}" class="adm-btn adm-btn--garis">Batal</a>
            <button type="submit" form="form-soal" class="adm-btn adm-btn--primer"><x-admin.ikon nama="simpan" :ukuran="18" /> Simpan Soal</button>
        </div>
    </div>

    @if ($errors->any())
        <div class="adm-info adm-info--kuning" role="alert" style="align-items:flex-start">
            <x-admin.ikon nama="info" :ukuran="18" />
            <div><strong>Periksa kembali isian berikut:</strong>
                <ul style="margin:6px 0 0 18px;list-style:disc">@foreach (collect($errors->all())->unique()->take(8) as $pesan)<li>{{ $pesan }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    <form id="form-soal" method="POST" action="{{ $aksi }}" enctype="multipart/form-data" class="adm-editor" novalidate data-editor-soal data-maks="{{ $maksPilihan }}">
        @csrf
        @if ($ada) @method('PUT') @endif

        <div class="adm-kolom">
            <section class="adm-kartu adm-kartu__isi">
                <div class="adm-kartu__kepala"><h3>Pertanyaan</h3><span class="adm-lencana">Pilihan Ganda</span></div>
                <div class="adm-formulir">
                    <div class="adm-bidang">
                        <label class="adm-label" for="teks">Teks pertanyaan</label>
                        <textarea id="teks" name="teks" class="adm-textarea @error('teks') adm-input--galat @enderror" rows="3" maxlength="1000" required>{{ old('teks', $pertanyaan->teks) }}</textarea>
                        @error('teks')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                    </div>

                    <div class="adm-bidang">
                        <span class="adm-label">Gambar pertanyaan <small>(opsional)</small></span>
                        <div data-soal-gambar-wadah @if (! $urlGambar) hidden @endif>
                            <img class="adm-pratinjau__gambar" style="max-width:320px" src="{{ $urlGambar }}" alt="Gambar pertanyaan" data-soal-gambar>
                            @if ($ada && $pertanyaan->gambar)
                                <label class="adm-ingat" style="margin-top:8px"><input type="checkbox" name="hapus_gambar" value="1" @checked(old('hapus_gambar'))> Hapus gambar unggahan</label>
                            @endif
                        </div>
                        <label class="adm-dropzone adm-dropzone--tipis" data-dropzone>
                            <input type="file" name="gambar" accept="image/jpeg,image/png,image/webp" data-soal-berkas>
                            <x-admin.ikon nama="gambar-tambah" :ukuran="20" /> <strong>Unggah gambar pendukung</strong>
                        </label>
                        @error('gambar')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                    </div>

                    @if ($daftarAset->isNotEmpty() || $dipilihAset)
                        <div class="adm-bidang">
                            <label class="adm-label" for="huruf_aset">Atau pakai gambar dari Kelola Asset <small>(opsional)</small></label>
                            <select id="huruf_aset" name="huruf_aset" class="adm-select">
                                <option value="">Tidak dipakai</option>
                                @if ($dipilihAset && ! $daftarAset->contains($dipilihAset))<option value="{{ $dipilihAset }}" selected>{{ $dipilihAset }}</option>@endif
                                @foreach ($daftarAset as $kata)<option value="{{ $kata }}" @selected($dipilihAset === $kata)>{{ $kata }}</option>@endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </section>

            <section class="adm-kartu adm-kartu__isi">
                <div class="adm-pilihan-kepala">
                    <div>
                        <h3>Pilihan Jawaban</h3>
                        <p class="adm-kartu__sub">Pilih satu jawaban benar. Gambar dapat digunakan di setiap opsi.</p>
                    </div>
                    <button type="button" class="adm-btn adm-btn--garis" data-opsi-tambah><x-admin.ikon nama="tambah" :ukuran="16" /> Tambah Opsi</button>
                </div>
                @error('pilihan')<span class="adm-galat" role="alert" style="display:block;margin-top:8px">{{ $message }}</span>@enderror
                @error('benar')<span class="adm-galat" role="alert" style="display:block;margin-top:8px">{{ $message }}</span>@enderror

                <div class="adm-opsi-grid" style="margin-top:16px" data-opsi-daftar>
                    @foreach ($pilihan as $i => $o)
                        @include('admin.kuis._opsi', ['i' => $i, 'o' => $o, 'benar' => $benar, 'tipe' => $tipe])
                    @endforeach
                </div>
            </section>

            <section class="adm-kartu adm-kartu__isi">
                <h3>Pembahasan Jawaban</h3>
                <div class="adm-formulir">
                    <textarea name="pembahasan" class="adm-textarea" rows="3" maxlength="2000" placeholder="Penjelasan yang tampil pada hasil quiz bagi User yang menjawab salah (opsional)" aria-label="Pembahasan jawaban">{{ old('pembahasan', $pertanyaan->pembahasan) }}</textarea>
                </div>
            </section>
        </div>

        <aside class="adm-sisi-kanan">
            <section class="adm-kartu adm-kartu__isi">
                <h3>Pengaturan Soal</h3>
                <div class="adm-formulir">
                    <div class="adm-bidang">
                        <label class="adm-label" for="tipe_jawaban">Tipe jawaban</label>
                        <select id="tipe_jawaban" name="tipe_jawaban" class="adm-select" data-tipe-jawaban>
                            @foreach (\App\Models\Pertanyaan::TIPE_JAWABAN as $nilai => $label)
                                <option value="{{ $nilai }}" @selected($tipe === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="adm-bidang">
                        <label class="adm-label" for="poin">Poin</label>
                        <input type="number" id="poin" name="poin" class="adm-input @error('poin') adm-input--galat @enderror" min="1" max="100" value="{{ old('poin', $pertanyaan->poin) }}" required>
                        @error('poin')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                    </div>
                    <div class="adm-bidang">
                        <label class="adm-label" for="kesulitan">Kesulitan</label>
                        <select id="kesulitan" name="kesulitan" class="adm-select">
                            @foreach (\App\Models\Pertanyaan::KESULITAN as $nilai => $label)
                                <option value="{{ $nilai }}" @selected(old('kesulitan', $pertanyaan->kesulitan) === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="adm-saklar">
                        <span class="adm-label" id="label-acak">Acak pilihan</span>
                        <label style="display:inline-flex">
                            <input type="checkbox" name="acak_pilihan" value="1" @checked(old('_token') ? old('acak_pilihan') : $pertanyaan->acak_pilihan) aria-labelledby="label-acak">
                            <span class="adm-saklar__tanda"></span>
                        </label>
                    </div>
                </div>
            </section>
            <section class="adm-kartu adm-kartu__isi">
                <h3 style="display:flex;gap:8px;align-items:center;font-size:14px"><x-admin.ikon nama="info" :ukuran="16" /> Panduan Media</h3>
                <p class="adm-kartu__sub" style="margin-top:8px;line-height:1.6">Gunakan rasio yang konsisten, tampilkan tangan secara utuh, dan validasi semua gestur bersama pengajar Tuli/BISINDO. Format JPG, PNG, atau WebP maksimal 5 MB.</p>
            </section>
        </aside>
    </form>
@endsection

@push('modal')
    <template data-opsi-template>
        @include('admin.kuis._opsi', ['i' => '__I__', 'o' => ['id' => null, 'teks' => '', 'gambar_url' => null], 'benar' => -1, 'tipe' => $tipe])
    </template>
@endpush
