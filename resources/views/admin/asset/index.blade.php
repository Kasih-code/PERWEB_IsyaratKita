@extends('layouts.admin')

@section('judul', 'Kelola Asset')
@section('menu', 'asset')

@section('isi')
    @php
        $bukaModal = $errors->asset->any();
        $idEdit = old('_id');
    @endphp

    <div class="adm-judul-halaman">
        <div>
            <h2>Kelola Asset BISINDO</h2>
            <p>Kelola gambar isyarat per kata dan huruf yang tampil di Kamus Visual.</p>
        </div>
        <span class="adm-lencana">{{ number_format($total, 0, ',', '.') }} asset</span>
    </div>

    <form method="GET" action="{{ route('admin.asset.index') }}" class="adm-filter" role="search">
        <div class="adm-input-ikon adm-filter__cari">
            <x-admin.ikon nama="cari" :ukuran="18" />
            <input type="search" name="cari" class="adm-input" value="{{ $cari }}" placeholder="Cari kata, huruf, atau filename..." aria-label="Cari asset">
        </div>
        <select name="status" class="adm-select" aria-label="Filter status" onchange="this.form.submit()">
            <option value="">Semua status</option>
            <option value="aktif" @selected($status === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
        </select>
        <button type="button" class="adm-btn adm-btn--primer" data-asset-tambah><x-admin.ikon nama="tambah" :ukuran="18" /> Tambah Asset</button>
    </form>

    <section class="adm-tabel-kartu">
        <div class="adm-tabel-gulir">
            <table class="adm-tabel">
                <thead>
                    <tr><th>No</th><th>Huruf</th><th>Preview</th><th>Filename</th><th>Last Update</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $i => $aset)
                        <tr>
                            <td class="adm-kolom-angka">{{ str_pad($daftar->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}</td>
                            <td><strong>{{ $aset->kata }}</strong></td>
                            <td><img class="adm-mini" src="{{ $aset->url }}" alt="Isyarat {{ $aset->kata }}" loading="lazy"></td>
                            <td class="adm-berkas">{{ $aset->nama_berkas }}</td>
                            <td>{{ $aset->updated_at->translatedFormat('d M Y') }}</td>
                            <td><span @class(['adm-lencana', 'adm-lencana--hijau' => $aset->status === 'aktif', 'adm-lencana--abu' => $aset->status !== 'aktif'])>{{ $aset->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="adm-kolom-aksi">
                                <span class="adm-aksi">
                                    <button type="button" class="adm-aksi-ikon" aria-label="Edit asset {{ $aset->kata }}"
                                            data-asset-edit data-id="{{ $aset->id }}" data-kata="{{ $aset->kata }}" data-status="{{ $aset->status }}"
                                            data-url="{{ $aset->url }}" data-berkas="{{ $aset->nama_berkas }}"
                                            data-aksi="{{ route('admin.asset.update', ['aset' => $aset, 'page' => $daftar->currentPage()]) }}">
                                        <x-admin.ikon nama="pensil" :ukuran="16" />
                                    </button>
                                    <button type="button" class="adm-aksi-ikon adm-aksi-ikon--bahaya" aria-label="Hapus asset {{ $aset->kata }}"
                                            data-adm-hapus data-aksi="{{ route('admin.asset.destroy', $aset) }}"
                                            data-judul="Hapus asset “{{ $aset->kata }}”?"
                                            data-teks="Asset akan dihapus dari Kamus BISINDO dan materi yang menggunakannya. Tindakan ini tidak dapat dibatalkan."
                                            data-tombol="Ya, Hapus Asset">
                                        <x-admin.ikon nama="sampah" :ukuran="16" />
                                    </button>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="adm-kosong"><strong>Belum ada asset</strong>
                            @if ($cari !== '' || $status) Tidak ada asset yang cocok dengan pencarian. @else Klik "Tambah Asset" untuk mengunggah gambar isyarat pertama. @endif
                        </div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.paginasi :data="$daftar" satuan="asset" />
    </section>
@endsection

@push('modal')
    {{-- Modal Tambah / Edit asset (satu form dipakai untuk keduanya) --}}
    <div class="adm-modal-latar @if ($bukaModal) terbuka @endif" id="modal-asset" role="dialog" aria-modal="true" aria-labelledby="judul-asset" aria-hidden="{{ $bukaModal ? 'false' : 'true' }}">
        <form class="adm-modal" method="POST" action="{{ old('_aksi_form', route('admin.asset.store')) }}" enctype="multipart/form-data" data-asset-form data-aksi-tambah="{{ route('admin.asset.store') }}" novalidate>
            @csrf
            <input type="hidden" name="_method" value="{{ old('_method', 'POST') }}" data-asset-metode>
            <input type="hidden" name="_aksi_form" value="{{ old('_aksi_form', route('admin.asset.store')) }}" data-asset-aksi-form>
            <div class="adm-modal__kepala">
                <div>
                    <h2 id="judul-asset">Tambah / Edit Asset BISINDO</h2>
                    <p>Format JPG, PNG, atau WebP · maksimal 5 MB</p>
                </div>
                <button type="button" class="adm-modal__tutup" data-adm-tutup aria-label="Tutup"><x-admin.ikon nama="tutup" :ukuran="20" /></button>
            </div>
            <div class="adm-modal__isi">
                <div class="adm-grid-2" style="grid-template-columns: minmax(0,1fr) 180px">
                    <div class="adm-bidang">
                        <label class="adm-label" for="asset-kata">Kata / Huruf</label>
                        <input type="text" id="asset-kata" name="kata" class="adm-input @if ($errors->asset->has('kata')) adm-input--galat @endif" value="{{ old('kata') }}" maxlength="60" required>
                        @if ($errors->asset->has('kata'))<span class="adm-galat" role="alert">{{ $errors->asset->first('kata') }}</span>@endif
                    </div>
                    <div class="adm-bidang">
                        <label class="adm-label" for="asset-status">Status</label>
                        <select id="asset-status" name="status" class="adm-select">
                            <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="adm-modal-asset">
                    <div class="adm-bidang">
                        <label class="adm-dropzone" data-dropzone>
                            <input type="file" name="berkas" accept="image/jpeg,image/png,image/webp" data-asset-berkas>
                            <span class="adm-dropzone__ikon"><x-admin.ikon nama="unggah" :ukuran="20" /></span>
                            <strong>Klik atau tarik file ke sini</strong>
                            <small>Resolusi rekomendasi 1200 × 900 px</small>
                        </label>
                        @if ($errors->asset->has('berkas'))<span class="adm-galat" role="alert">{{ $errors->asset->first('berkas') }}</span>@endif
                    </div>
                    <div class="adm-pratinjau">
                        <span class="adm-label">Preview</span>
                        <img class="adm-pratinjau__gambar" alt="Pratinjau gambar" data-asset-pratinjau hidden>
                        <div class="adm-pratinjau__kosong" data-asset-pratinjau-kosong><x-admin.ikon nama="gambar" :ukuran="32" /></div>
                        <small data-asset-nama-berkas>Belum ada gambar</small>
                    </div>
                </div>

                <p class="adm-info"><x-admin.ikon nama="info" :ukuran="18" /> Pastikan bentuk tangan, arah gerak, ekspresi, dan variasi lokal telah divalidasi bersama pengajar Tuli/BISINDO.</p>
            </div>
            <div class="adm-modal__kaki">
                <button type="button" class="adm-btn adm-btn--garis" data-adm-tutup>Batal</button>
                <button type="submit" class="adm-btn adm-btn--primer"><x-admin.ikon nama="simpan" :ukuran="18" /> Simpan Asset</button>
            </div>
        </form>
    </div>
@endpush
