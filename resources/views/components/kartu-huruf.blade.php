{{--
    Satu huruf beserta gambar gestur BISINDO-nya (halaman Detail Kata).
    Gambar dicari di public/images/gestur/{huruf}.png (lihat App\Support\AsetGestur).
--}}
@props(['huruf', 'urutan'])

<div class="kartu-huruf">
    <div class="kartu-huruf__atas">
        <span class="kartu-huruf__huruf">{{ $huruf }}</span>
        <span class="kartu-huruf__urutan">Huruf {{ $urutan }}</span>
    </div>
    <x-placeholder-gambar class="kartu-huruf__gambar" :src="\App\Support\AsetGestur::url($huruf)" :alt="'Gestur BISINDO huruf '.$huruf" />
</div>
