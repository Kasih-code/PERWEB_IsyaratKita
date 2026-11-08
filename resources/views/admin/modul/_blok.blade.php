{{--
    Satu blok materi pada Editor Modul.
    $i : indeks blok (atau "__I__" untuk <template>), $b : array data blok.
    Nama input di-reindex oleh JS (admin.js) agar urutan = urutan di layar.
--}}
@php
    $tipe = $b['tipe'];
    $judulTipe = ['heading' => 'Heading', 'teks' => 'Teks', 'gambar' => 'Gambar', 'video' => 'Video'][$tipe];
    $ikonTipe = ['heading' => 'heading', 'teks' => 'teks', 'gambar' => 'gambar', 'video' => 'video'][$tipe];
    $galat = fn ($kolom) => $errors->first("blok.$i.$kolom");
@endphp
<article class="adm-blok" data-blok data-tipe="{{ $tipe }}" draggable="false">
    <input type="hidden" name="blok[{{ $i }}][id]" value="{{ $b['id'] }}" data-blok-id>
    <input type="hidden" name="blok[{{ $i }}][tipe]" value="{{ $tipe }}">
    <input type="hidden" name="blok[{{ $i }}][salin_dari]" value="" data-blok-salin>

    <div class="adm-blok__kepala">
        <button type="button" class="adm-blok__geser" data-blok-geser aria-label="Tarik untuk mengubah urutan"><x-admin.ikon nama="geser" :ukuran="16" /></button>
        <span class="adm-blok__tipe"><span class="adm-blok__tipe-ikon"><x-admin.ikon :nama="$ikonTipe" :ukuran="15" /></span>{{ $judulTipe }}</span>
        <span class="adm-blok__alat">
            <button type="button" data-blok-aksi="naik" aria-label="Pindah ke atas"><x-admin.ikon nama="chevron-atas" :ukuran="16" /></button>
            <button type="button" data-blok-aksi="turun" aria-label="Pindah ke bawah"><x-admin.ikon nama="chevron-bawah" :ukuran="16" /></button>
            <button type="button" data-blok-aksi="salin" aria-label="Duplikat blok"><x-admin.ikon nama="salin" :ukuran="16" /></button>
            <button type="button" class="adm-hapus" data-blok-aksi="hapus" aria-label="Hapus blok"><x-admin.ikon nama="sampah" :ukuran="16" /></button>
        </span>
    </div>

    <div class="adm-blok__isi">
        @if ($tipe === 'heading')
            <input type="text" name="blok[{{ $i }}][teks]" class="adm-input @if ($galat('teks')) adm-input--galat @endif" value="{{ $b['teks'] }}" maxlength="200" placeholder="Judul bagian materi" aria-label="Isi heading">
            @if ($galat('teks'))<span class="adm-galat" role="alert">{{ $galat('teks') }}</span>@endif
        @elseif ($tipe === 'teks')
            <textarea name="blok[{{ $i }}][teks]" class="adm-textarea @if ($galat('teks')) adm-input--galat @endif" rows="4" maxlength="10000" placeholder="Tulis isi materi di sini" aria-label="Isi teks">{{ $b['teks'] }}</textarea>
            @if ($galat('teks'))<span class="adm-galat" role="alert">{{ $galat('teks') }}</span>@endif
        @elseif ($tipe === 'gambar')
            <div class="adm-blok__media">
                <div>
                    <img class="adm-blok__gambar" alt="Gambar materi" data-blok-gambar @if ($b['gambar_url']) src="{{ $b['gambar_url'] }}" @else hidden @endif>
                    <div class="adm-blok__gambar-kosong" data-blok-gambar-kosong @if ($b['gambar_url']) hidden @endif><x-admin.ikon nama="gambar" :ukuran="28" /></div>
                </div>
                <div>
                    <div class="adm-bidang">
                        <label class="adm-label">Alt text</label>
                        <input type="text" name="blok[{{ $i }}][alt]" class="adm-input" value="{{ $b['alt'] }}" maxlength="200" placeholder="Deskripsi singkat gambar">
                    </div>
                    <label class="adm-btn adm-btn--garis adm-tombol-berkas">
                        <x-admin.ikon nama="unggah-tray" :ukuran="16" /> <span data-blok-label-gambar>{{ $b['gambar_url'] ? 'Ganti Gambar' : 'Pilih Gambar' }}</span>
                        <input type="file" name="blok[{{ $i }}][gambar]" accept="image/jpeg,image/png,image/webp" data-blok-berkas>
                    </label>
                    @if ($galat('gambar'))<span class="adm-galat" role="alert" style="display:block;margin-top:6px">{{ $galat('gambar') }}</span>@endif
                </div>
            </div>
        @elseif ($tipe === 'video')
            <div class="adm-blok__media">
                <div class="adm-blok__video-kotak"><span><x-admin.ikon nama="putar" :ukuran="16" /></span></div>
                <div>
                    <div class="adm-bidang">
                        <label class="adm-label">URL video</label>
                        <input type="url" name="blok[{{ $i }}][video_url]" class="adm-input @if ($galat('video_url')) adm-input--galat @endif" value="{{ $b['video_url'] }}" maxlength="255" placeholder="https://www.youtube.com/watch?v=...">
                        @if ($galat('video_url'))<span class="adm-galat" role="alert">{{ $galat('video_url') }}</span>@endif
                    </div>
                    <div class="adm-bidang">
                        <label class="adm-label">Durasi <small>(mm:ss)</small></label>
                        <input type="text" name="blok[{{ $i }}][durasi]" class="adm-input @if ($galat('durasi')) adm-input--galat @endif" value="{{ $b['durasi'] }}" maxlength="10" placeholder="03:24">
                        @if ($galat('durasi'))<span class="adm-galat" role="alert">{{ $galat('durasi') }}</span>@endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</article>
