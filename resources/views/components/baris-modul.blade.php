{{--
    Satu baris modul pada halaman Modul Pembelajaran.
    $modul = ['id','judul','deskripsi','status', 'tombol'?] ; status: terkunci | berjalan | selesai
    - Guest : semua modul terkunci, tombol membuka modal login.
    - User  : status dari database; tombol mengarah ke detail modul
              (modul terkunci mengarah ke detail yang akan memberi tahu alasannya).
--}}
@props(['modul'])

@php
    $status = $modul['status'] ?? 'terkunci';
    $teksStatus = ['terkunci' => 'Terkunci', 'berjalan' => 'Sedang Dipelajari', 'selesai' => 'Selesai'][$status] ?? 'Terkunci';
    $variasiTombol = $status === 'berjalan' ? 'primer' : 'garis';
@endphp

<article class="baris-modul baris-modul--{{ $status }}" @if ($status === 'berjalan') aria-current="step" @endif>
    <div class="baris-modul__ikon">
        @if ($status === 'terkunci')
            <x-ikon nama="kunci" :ukuran="22" />
        @elseif ($status === 'selesai')
            <x-ikon nama="centang" :ukuran="26" :tebal="3" />
        @else
            <span class="baris-modul__nomor">{{ $modul['id'] }}</span>
        @endif
    </div>
    <div class="baris-modul__isi">
        <div class="baris-modul__judul">
            <h3 class="baris-modul__nama">{{ $modul['judul'] }}</h3>
            <span class="baris-modul__status-sr">{{ $teksStatus }}</span>
        </div>
        <p class="baris-modul__teks">{{ $modul['deskripsi'] }}</p>
    </div>

    @if ($statusLogin)
        <x-tombol :variasi="$variasiTombol" ukuran="kecil"
                  :href="route('modul.detail', $modul['id'])"
                  :class="$status === 'terkunci' ? 'tombol--terkunci' : ''">
            {{ $modul['tombol'] ?? 'Buka Modul' }}
        </x-tombol>
    @else
        <x-tombol variasi="garis" ukuran="kecil" :class="$status === 'terkunci' ? 'tombol--terkunci' : ''" data-buka-modal-login>
            Buka Modul
        </x-tombol>
    @endif
</article>
