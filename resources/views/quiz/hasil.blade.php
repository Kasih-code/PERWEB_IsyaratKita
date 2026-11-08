@extends('layouts.app')

@section('judul', 'Hasil Quiz '.$modul->judul)

@section('isi')
    <section class="seksi--alt hasil-seksi">
        <div class="wadah">
            <article @class(['hasil-kartu', 'hasil-kartu--gagal' => ! $lulus])>
                <div class="hasil-kartu__lencana">
                    <x-ikon :nama="$lulus ? 'lencana' : 'info'" :ukuran="40" :tebal="1.6" />
                </div>

                @if ($lulus)
                    <h1>Selamat!</h1>
                    <p class="hasil-kartu__teks">Kamu berhasil menyelesaikan {{ $modul->judul_lengkap }}</p>
                @else
                    <h1>Belum Berhasil</h1>
                    <p class="hasil-kartu__teks">Nilaimu ({{ $nilai }}%) belum mencapai batas lulus ({{ $batasLulus }}%). Pelajari materinya lagi, lalu coba kuis sekali lagi.</p>
                @endif

                <div class="hasil-skor">
                    <span class="hasil-skor__label">Skor kuis kamu</span>
                    <strong class="hasil-skor__angka">{{ $benar }} / {{ $total }}</strong>
                    <span @class(['hasil-skor__nilai', 'hasil-skor__nilai--rendah' => ! $lulus])>{{ $labelNilai }}</span>
                </div>

                <div class="hasil-progres">
                    <div class="hasil-progres__baris">
                        <strong>Progres Belajar Terupdate</strong>
                        <strong class="hasil-progres__persen">{{ $persenProgres }}% Selesai</strong>
                    </div>
                    <div class="progres__lintasan" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $persenProgres }}">
                        <div class="progres__isi" style="width: {{ $persenProgres }}%"></div>
                    </div>
                </div>

                <div class="hasil-kartu__aksi">
                    @if ($lulus)
                        @if ($berikutnya)
                            <x-tombol :href="route('modul.detail', $berikutnya->id)">Lanjut ke Modul Berikutnya</x-tombol>
                        @else
                            <x-tombol :href="route('modul.index')">Kembali ke Daftar Modul</x-tombol>
                        @endif
                        <x-tombol variasi="garis" :href="route('modul.quiz.tampil', $modul->id)">Ulangi Kuis</x-tombol>
                    @else
                        <x-tombol :href="route('modul.quiz.tampil', $modul->id)">Ulangi Kuis</x-tombol>
                        <x-tombol variasi="garis" :href="route('modul.detail', $modul->id)">Pelajari Materi</x-tombol>
                    @endif
                </div>

                @if (count($pembahasan) > 0)
                    <details class="hasil-pembahasan">
                        <summary>Pembahasan soal yang belum tepat ({{ count($pembahasan) }})</summary>
                        <ol>
                            @foreach ($pembahasan as $butir)
                                <li value="{{ $butir['nomor'] }}">
                                    <strong>{{ $butir['teks'] }}</strong>
                                    <span>Jawabanmu: {{ $butir['jawabanmu'] }}</span>
                                    <span>Jawaban benar: {{ $butir['benar'] }}</span>
                                    @if ($butir['penjelasan'])<em>{{ $butir['penjelasan'] }}</em>@endif
                                </li>
                            @endforeach
                        </ol>
                    </details>
                @endif
            </article>
        </div>
    </section>
@endsection
