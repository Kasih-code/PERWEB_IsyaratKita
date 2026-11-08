@extends('layouts.app')

@section('judul', 'Kamus BISINDO')

@section('isi')
    {{-- ===== Kepala + kolom pencarian ===== --}}
    <section class="kamus-kepala wadah">
        <span class="label-kecil">Kamus Visual</span>
        <h1>Kamus IsyaratKita</h1>
        <p>Cari tahu arti gerakan isyarat kata-kata harian.</p>

        <form class="pencarian" method="GET" action="{{ route('kamus.index') }}" role="search">
            <span class="pencarian__ikon"><x-ikon nama="cari" :ukuran="18" /></span>
            <label for="kolom-cari" class="hanya-pembaca-layar">Cari kosakata</label>
            <input class="pencarian__input" type="search" id="kolom-cari" name="cari"
                   value="{{ $kataPencarian }}"
                   placeholder="Cari kosakata (contoh: 'Selamat Pagi', 'Aku', 'Sama-sama')...">
        </form>
    </section>

    {{-- ===== Pilihan huruf + hasil ===== --}}
    <section class="seksi--alt kamus-isi">
        <div class="wadah">
            <h2 class="kamus-isi__judul">Pilih Berdasarkan Huruf</h2>

            <nav class="abjad" aria-label="Pilih huruf">
                @foreach ($daftarHuruf as $huruf)
                    <a href="{{ route('kamus.index', ['huruf' => $huruf]) }}"
                       @class(['aktif' => $huruf === $hurufAktif])
                       @if ($huruf === $hurufAktif) aria-current="true" @endif>{{ $huruf }}</a>
                @endforeach
            </nav>

            <h2 class="hasil__judul">{{ $judulHasil }}</h2>

            @if (count($daftarKata) > 0)
                <div class="hasil__daftar">
                    @foreach ($daftarKata as $kata)
                        <x-kartu-kata :kata="$kata" />
                    @endforeach
                </div>
            @else
                <p class="hasil__kosong">Kata belum ditemukan. Coba huruf atau kata lain.</p>
            @endif
        </div>
    </section>
@endsection
