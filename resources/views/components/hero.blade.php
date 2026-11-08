{{--
    Hero Section (dipakai Beranda, Modul Pembelajaran, dan Donasi).
    ukuran : besar | sedang | donasi
    Slot   : judul (wajib), slot utama = paragraf, aksi (opsional)
    Contoh :
        <x-hero ukuran="besar" label="Teks kecil">
            <x-slot:judul>Judul besar</x-slot:judul>
            Paragraf pembuka.
            <x-slot:aksi><x-tombol>Mulai</x-tombol></x-slot:aksi>
        </x-hero>
--}}
@props(['ukuran' => 'besar', 'label' => null, 'gambar' => null, 'judul', 'aksi' => null])

<section {{ $attributes->class(['hero', "hero--{$ukuran}"]) }}>
    <div class="wadah hero__isi">
        <div class="hero__konten">
            @if ($label)
                <span class="label-kecil hero__label">{{ $label }}</span>
            @endif
            <h1 class="hero__judul">{{ $judul }}</h1>
            <p class="hero__teks">{{ $slot }}</p>
            @if ($aksi && ! $aksi->isEmpty())
                <div class="hero__aksi">{{ $aksi }}</div>
            @endif
        </div>
        <x-placeholder-gambar class="hero__gambar" :src="$gambar" />
    </div>
</section>
