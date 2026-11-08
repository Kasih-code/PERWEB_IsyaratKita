{{--
    Kotak gambar. Jika $src kosong tampil sebagai placeholder biru muda
    (sesuai desain). Isi $src dengan URL gambar untuk menggantinya.
    Atur ukuran lewat class CSS yang ditambahkan pada komponen ini.
--}}
@props(['src' => null, 'alt' => ''])

<div {{ $attributes->class('gambar-placeholder') }} @unless ($src) aria-hidden="true" @endunless>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy">
    @endif
</div>
