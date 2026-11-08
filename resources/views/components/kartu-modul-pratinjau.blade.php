{{-- Kartu modul untuk pratinjau di Beranda. $modul = ['id','nomor','judul','deskripsi']
    - User : mengarah ke detail modul.
    - Guest: membuka modal login; setelah login diarahkan ke detail modul tsb. --}}
@props(['modul'])

<a href="{{ route('modul.detail', $modul['id']) }}" class="kartu kartu-modul kartu-modul--tautan"
    @unless ($statusLogin) data-buka-modal-login data-tujuan-login="{{ route('modul.detail', $modul['id'], false) }}" @endunless>
    <div class="kartu-modul__atas">
        <span class="kartu-modul__nomor">Modul {{ $modul['nomor'] }}</span>
        <span class="kartu-modul__centang"><x-ikon nama="centang" :ukuran="14" :tebal="2.5" /></span>
    </div>
    <h3 class="kartu-modul__judul">{{ $modul['judul'] }}</h3>
    <p class="kartu-modul__teks">{{ $modul['deskripsi'] }}</p>
    <x-placeholder-gambar class="kartu-modul__gambar" :src="$modul['gambar'] ?? null" />
</a>