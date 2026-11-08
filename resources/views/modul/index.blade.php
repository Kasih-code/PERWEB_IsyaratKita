@extends('layouts.app')

@section('judul', 'Modul Pembelajaran')

@section('isi')
    {{-- ===== Hero ===== --}}
    <x-hero ukuran="sedang" :gambar="asset('images/umum/hero-modul.jpg')">
        <x-slot:judul>Modul Pembelajaran BISINDO</x-slot:judul>
        Pelajari tata cara bahasa isyarat melalui modul yang interaktif, visual, dan dilengkapi kuis penguji pemahaman untuk memastikan progres belajar yang optimal.
    </x-hero>

    {{-- ===== Progres + daftar modul ===== --}}
    <section class="seksi--alt modul-seksi">
        <div class="wadah">
            <div class="kartu progres">
                <div class="progres__info">
                    <span class="label-kecil">Progress Belajarmu</span>
                    <h2>Teruskan Usahamu!</h2>
                    @if ($statusLogin)
                        @if (($progresBelajar['selesai'] ?? 0) > 0)
                            <p>Kamu telah menyelesaikan {{ $progresBelajar['selesai'] }} dari {{ $progresBelajar['total'] }} modul dasar.</p>
                        @else
                            <p>Kamu belum menyelesaikan modul. Mulai dari Modul 1 ya!</p>
                        @endif
                    @else
                        <p>Login untuk mengakses modul, dan memulai progress anda!</p>
                    @endif
                </div>
                <div class="progres__bar">
                    <div class="progres__baris">
                        <span>Modul Selesai</span>
                        <strong>{{ $progresBelajar['persen'] }}%</strong>
                    </div>
                    <div class="progres__lintasan" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progresBelajar['persen'] }}">
                        <div class="progres__isi" style="width: {{ $progresBelajar['persen'] }}%"></div>
                    </div>
                </div>
            </div>

            <div class="modul-daftar">
                <h2 class="judul-seksi">Daftar Modul Belajar</h2>
                @foreach ($daftarModul as $modul)
                    <x-baris-modul :modul="$modul" />
                @endforeach
            </div>
        </div>
    </section>
@endsection
