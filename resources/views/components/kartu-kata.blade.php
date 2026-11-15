{{-- Kartu kata pada Kamus. Dapat dibuka langsung di halaman untuk melihat ejaan isyarat per huruf.
     $kata = ['kata','slug','huruf'] --}}
@props(['kata', 'terbuka' => false])

@php
    $teksKata = $kata['kata'];
    $hurufSaja = strtoupper(preg_replace('/[^A-Za-z]/', '', \Illuminate\Support\Str::ascii($teksKata)));
    $hurufKata = $hurufSaja === '' ? [] : str_split($hurufSaja);
    $gambarKata = \App\Support\AsetGestur::url($teksKata);
@endphp

<details class="kartu-kata" @if ($terbuka) open @endif>
    <summary class="kartu-kata__ringkas">
        <span class="kartu-kata__kata">{{ $teksKata }}</span>
        <span class="kartu-kata__aksi">Lihat Isyarat</span>
    </summary>

    @if ($gambarKata)
        <figure class="kata-kartu__gambar-kata">
            <img src="{{ $gambarKata }}" alt="Isyarat BISINDO untuk kata {{ $teksKata }}" loading="lazy">
        </figure>
    @endif

    <div class="kata-kartu__huruf">
        @foreach ($hurufKata as $indeks => $huruf)
            <x-kartu-huruf :huruf="$huruf" :urutan="$indeks + 1" />
        @endforeach
    </div>
</details>
