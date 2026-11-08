@extends('layouts.app')

@section('judul', $modul->judul)

@section('isi')
    {{-- ===== Kepala materi ===== --}}
    <section class="materi-kepala">
        <div class="wadah">
            <div class="materi-kepala__atas">
                <a href="{{ $pratinjau ? route('admin.modul.edit', $modul) : route('modul.index') }}" class="kembali kembali--tanpa-jarak">
                    <x-ikon nama="panah-kiri" :ukuran="16" /> {{ $pratinjau ? 'Kembali ke Editor' : 'Kembali ke Modul' }}
                </a>
                <span class="materi-kepala__urutan">Materi {{ $halaman }} dari {{ $totalMateri }}</span>
            </div>
            <span class="label-kecil">{{ $modul->label }}</span>
            <h1>{{ $modul->judul }}</h1>
            @if ($modul->deskripsi)
                <p>{{ $modul->deskripsi }}</p>
            @endif
        </div>
    </section>

    {{-- ===== Isi materi (blok dari database, diatur Admin) ===== --}}
    <section class="seksi--alt materi-isi">
        <div class="wadah">
            @if ($pratinjau)
                <p class="materi-pratinjau">
                    @if ($modul->dapatDilihatPublik())
                        Pratinjau Admin: tampilan ini hanya untuk pengecekan, progres tidak dicatat.
                    @else
                        Pratinjau Admin: modul ini masih draft atau disembunyikan, sehingga belum tampil untuk Guest dan User.
                    @endif
                </p>
            @endif

            @forelse ($blokMateri as $blok)
                @switch($blok->tipe)
                    @case('heading')
                        <h2 class="materi-blok__heading">{{ $blok->teks }}</h2>
                        @break
                    @case('teks')
                        @php
                            $teksAman = e($blok->teks);
                            $teksAman = preg_replace('~(https?://[^\s<]+[^\s<.,;:!?)])~', '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', $teksAman);
                        @endphp
                        <p class="materi-isi__teks materi-blok">{!! nl2br($teksAman) !!}</p>
                        @break
                    @case('gambar')
                        <figure class="materi-blok materi-blok__gambar">
                            <x-placeholder-gambar class="materi-isi__gambar" :src="$blok->gambar_url" :alt="$blok->alt ?? ''" />
                            @if ($blok->alt)<figcaption>{{ $blok->alt }}</figcaption>@endif
                        </figure>
                        @break
                    @case('video')
                        <div class="materi-blok materi-blok__video">
                            @if ($blok->video_embed)
                                <iframe src="{{ $blok->video_embed }}" title="Video pembelajaran" loading="lazy" allowfullscreen
                                        referrerpolicy="strict-origin-when-cross-origin"></iframe>
                            @else
                                <a href="{{ $blok->video_url }}" target="_blank" rel="noopener noreferrer" class="tombol tombol--garis tombol--kecil">Tonton video{{ $blok->durasi ? ' ('.$blok->durasi.')' : '' }}</a>
                            @endif
                        </div>
                        @break
                @endswitch
            @empty
                <p class="materi-isi__teks">Materi untuk modul ini belum tersedia.</p>
            @endforelse

            @php $qs = $pratinjau ? ['pratinjau' => 1] : []; @endphp

            <div class="materi-isi__aksi materi-isi__aksi--ganda">
                @if ($halaman > 1)
                    <x-tombol variasi="garis" :href="route('modul.detail', ['id' => $modul->id, 'materi' => $halaman - 1] + $qs)">Materi Sebelumnya</x-tombol>
                @else
                    <span></span>
                @endif

                @if ($halaman < $totalMateri)
                    <x-tombol :href="route('modul.detail', ['id' => $modul->id, 'materi' => $halaman + 1] + $qs)">Materi Berikutnya</x-tombol>
                @elseif ($pratinjau)
                    <x-tombol variasi="garis" :href="route('admin.modul.edit', $modul)">Kembali ke Editor</x-tombol>
                @elseif ($adaQuiz)
                    @if ($statusLogin)
                        {{-- User: lanjut ke quiz modul ini. --}}
                        <x-tombol :href="route('modul.quiz.tampil', $modul->id)">Lanjut ke Quiz</x-tombol>
                    @else
                        {{-- Quiz membutuhkan login: Guest diminta masuk lebih dulu, lalu diarahkan ke quiz. --}}
                        <x-tombol data-buka-modal-login data-tujuan-login="{{ route('modul.quiz.tampil', $modul->id, false) }}">Lanjut ke Quiz</x-tombol>
                    @endif
                @else
                    {{-- Modul tanpa quiz aktif: selesai setelah materi dibaca. --}}
                    @if ($statusLogin)
                        <form method="POST" action="{{ route('modul.selesai', $modul->id) }}">
                            @csrf
                            <x-tombol type="submit">Selesaikan Modul</x-tombol>
                        </form>
                    @else
                        <x-tombol data-buka-modal-login data-tujuan-login="{{ route('modul.detail', $modul->id, false) }}">Selesaikan Modul</x-tombol>
                    @endif
                @endif
            </div>
        </div>
    </section>
@endsection
