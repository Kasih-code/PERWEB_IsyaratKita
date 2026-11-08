@extends('layouts.app')

@section('judul', $kataBesar.' - Kamus BISINDO')

@section('isi')
    <section class="seksi--alt kata-isi">
        <div class="wadah">
            {{-- Jejak halaman + tombol kembali --}}
            <nav class="jejak" aria-label="Jejak halaman">
                <a href="{{ route('kamus.index') }}">Kamus</a>
                <span aria-hidden="true">&gt;</span>
                <a href="{{ route('kamus.index', ['huruf' => $kata['huruf']]) }}">Huruf {{ $kata['huruf'] }}</a>
                <span aria-hidden="true">&gt;</span>
                <span class="jejak__aktif">{{ $kataBesar }}</span>
            </nav>

            <a href="{{ route('kamus.index', ['huruf' => $kata['huruf']]) }}" class="kembali">
                <x-ikon nama="chevron-kiri" :ukuran="16" /> Kembali ke Kamus
            </a>

            {{-- Kartu utama: satu huruf = satu gambar gestur --}}
            <article class="kata-kartu">
                <div class="kata-kartu__kepala">
                    <div>
                        <h1 class="kata-kartu__judul">{{ $kataBesar }}</h1>
                        <p class="kata-kartu__teks">Ejaan isyarat satu tangan per huruf untuk kosa kata "{{ $kataBesar }}".</p>
                    </div>
                </div>

                @if ($gambarKata)
                    <figure class="kata-kartu__gambar-kata">
                        <img src="{{ $gambarKata }}" alt="Isyarat BISINDO untuk kata {{ $kata['kata'] }}" loading="lazy">
                    </figure>
                @endif

                <div class="kata-kartu__huruf">
                    @foreach ($daftarHurufKata as $indeks => $huruf)
                        <x-kartu-huruf :huruf="$huruf" :urutan="$indeks + 1" />
                    @endforeach
                </div>
            </article>

            <aside class="tips">
                <span class="tips__ikon"><x-ikon nama="info" :ukuran="18" /></span>
                <div>
                    <h2>Tips Mengeja Kata</h2>
                    <p>Eja setiap huruf dengan tempo yang konsisten dan pastikan telapak tangan menghadap ke depan lawan bicara Anda agar mudah dibaca.</p>
                </div>
            </aside>
        </div>
    </section>
@endsection
