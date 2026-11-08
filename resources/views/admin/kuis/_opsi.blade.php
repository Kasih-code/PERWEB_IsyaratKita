{{-- Satu kartu pilihan jawaban. $i indeks (atau "__I__"), $o data, $benar indeks jawaban benar, $tipe tipe jawaban. --}}
@php
    $pakaiTeks = in_array($tipe, ['teks', 'teks_gambar'], true);
    $pakaiGambar = in_array($tipe, ['gambar', 'teks_gambar'], true);
    $galatTeks = $errors->first("pilihan.$i.teks");
    $galatGambar = $errors->first("pilihan.$i.gambar");
@endphp
<div @class(['adm-opsi', 'adm-opsi--benar' => $i === $benar]) data-opsi>
    <input type="hidden" name="pilihan[{{ $i }}][id]" value="{{ $o['id'] }}">
    <div data-bagian-gambar @if (! $pakaiGambar) hidden @endif>
        <img class="adm-opsi__gambar" alt="Gambar opsi" data-opsi-gambar @if ($o['gambar_url']) src="{{ $o['gambar_url'] }}" @else hidden @endif>
        <div class="adm-opsi__kosong" data-opsi-gambar-kosong @if ($o['gambar_url']) hidden @endif><x-admin.ikon nama="gambar" :ukuran="24" /></div>
        <label class="adm-opsi__unggah" style="margin-top:8px">
            <x-admin.ikon nama="unggah-tray" :ukuran="14" /> {{ $o['gambar_url'] ? 'Ganti gambar' : 'Pilih gambar' }}
            <input type="file" name="pilihan[{{ $i }}][gambar]" accept="image/jpeg,image/png,image/webp" data-opsi-berkas>
        </label>
        @if ($galatGambar)<span class="adm-galat" role="alert">{{ $galatGambar }}</span>@endif
    </div>

    <div class="adm-opsi__baris">
        <span class="adm-opsi__huruf" data-opsi-huruf>A</span>
        <span data-bagian-teks style="flex:1;min-width:0;display:flex" @if (! $pakaiTeks) hidden @endif>
            <input type="text" name="pilihan[{{ $i }}][teks]" class="adm-opsi__teks" value="{{ $o['teks'] }}" maxlength="255" placeholder="Teks pilihan" aria-label="Teks pilihan">
        </span>
        <span data-bagian-label-gambar style="flex:1;font-size:12px;color:var(--warna-teks-halus)" @if ($pakaiTeks) hidden @endif>Pilihan gambar</span>
        <label class="adm-opsi__benar" title="Tandai sebagai jawaban benar">
            <input type="radio" name="benar" value="{{ $i }}" @checked($i === $benar) aria-label="Jawaban benar">
            <x-admin.ikon nama="centang-lingkaran" :ukuran="20" />
        </label>
        <button type="button" class="adm-opsi__hapus" data-opsi-hapus aria-label="Hapus opsi"><x-admin.ikon nama="tutup" :ukuran="14" /></button>
    </div>
    @if ($galatTeks)<span class="adm-galat" role="alert">{{ $galatTeks }}</span>@endif
</div>
