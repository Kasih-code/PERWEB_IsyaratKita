{{-- Komponen Logo IsyaratKita --}}

@props(['ukuranIkon' => 22])

<a
    href="{{ route('beranda') }}"
    {{ $attributes->class('logo') }}
    aria-label="IsyaratKita - ke Beranda"
>
    <img
        src="{{ asset('images/umum/logo isyarat kita.png') }}"
        alt="Logo IsyaratKita"
        class="logo__gambar"
        width="44"
        height="44"
    >

    <span class="logo__teks">
        Isyarat<span>Kita</span>
    </span>
</a>
