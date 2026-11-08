{{-- Logo IsyaratKita. Pemakaian: <x-logo /> --}}
@props(['ukuranIkon' => 22])

<a href="{{ route('beranda') }}" {{ $attributes->class('logo') }} aria-label="IsyaratKita - ke Beranda">
    <span class="logo__ikon"><x-ikon nama="tangan" :ukuran="$ukuranIkon" /></span>
    <span class="logo__teks">Isyarat<span>Kita</span></span>
</a>
