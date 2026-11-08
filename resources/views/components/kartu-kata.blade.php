{{-- Kartu kata pada Kamus. $kata = ['kata','slug','huruf'] --}}
@props(['kata'])

<article class="kartu-kata">
    <span class="kartu-kata__kata">{{ $kata['kata'] }}</span>
    <x-tombol variasi="garis" ukuran="kecil" :href="route('kamus.detail', $kata['slug'])" :aria-label="'Lihat isyarat kata '.$kata['kata']">
        Lihat Isyarat
    </x-tombol>
</article>
