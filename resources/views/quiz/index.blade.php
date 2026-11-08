@extends('layouts.app')

@section('judul', 'Quiz '.$modul->judul)

@section('isi')
    <section class="seksi--alt quiz-seksi">
        <div class="quiz">
            <header class="quiz__kepala">
                <span class="label-kecil">Quiz {{ $modul->label }}</span>
                <h1>{{ $modul->judul }}</h1>
            </header>

            <form method="POST" action="{{ route('modul.quiz.kirim', $modul->id) }}"
                  data-quiz data-total="{{ $totalSoal }}" data-total-poin="{{ $totalPoin }}"
                  @if ($detikBatas) data-batas-detik="{{ $detikBatas }}" @endif novalidate>
                @csrf
                <input type="hidden" name="waktu_habis" value="0" data-quiz-waktu-habis>

                <div class="quiz__status">
                    <span class="quiz__nomor">Pertanyaan <span data-quiz-nomor>1</span> dari {{ $totalSoal }}</span>
                    @if ($detikBatas)
                        <span class="quiz__waktu" data-quiz-waktu aria-live="off">--:--</span>
                    @endif
                    <strong class="quiz__skor">Skor: <span data-quiz-skor>0</span>/100</strong>
                </div>
                <div class="quiz__lintasan" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $totalSoal }}" aria-valuenow="1" data-quiz-lintasan>
                    <div class="quiz__isi" data-quiz-bar style="width: {{ 100 / $totalSoal }}%"></div>
                </div>

                <div class="quiz__kartu">
                    @foreach ($soal as $indeks => $butir)
                        {{-- data-benar dipakai hanya untuk tampilan skor langsung; penilaian resmi tetap di server. --}}
                        <fieldset class="quiz-soal" data-soal data-benar="{{ $butir['benar'] }}" data-poin="{{ $butir['poin'] }}">
                            <legend class="quiz-soal__teks">{{ $butir['teks'] }}</legend>

                            @if ($butir['bergambar'])
                                <x-placeholder-gambar class="quiz-soal__gambar" :src="$butir['gambar']" alt="Gambar pertanyaan" />
                            @endif

                            <div class="quiz-soal__pilihan">
                                @foreach ($butir['pilihan'] as $pilihan)
                                    <label class="pilihan @if ($pilihan['gambar']) pilihan--gambar @endif">
                                        <input type="radio" class="hanya-pembaca-layar" name="jawaban[{{ $butir['id'] }}]" value="{{ $pilihan['id'] }}"
                                               @checked((string) old("jawaban.{$butir['id']}") === (string) $pilihan['id'])>
                                        <span class="pilihan__tanda" aria-hidden="true"></span>
                                        @if ($pilihan['gambar'])
                                            <img class="pilihan__gambar" src="{{ $pilihan['gambar'] }}" alt="" loading="lazy">
                                        @endif
                                        @if ($pilihan['teks'] !== null && $pilihan['teks'] !== '')
                                            <span class="pilihan__teks">{{ $pilihan['teks'] }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>

                <p class="quiz__galat" data-quiz-galat role="alert" hidden></p>

                <div class="quiz__aksi">
                    <x-tombol variasi="garis" ukuran="kecil" :href="route('modul.detail', $modul->id)" data-quiz-kembali>Kembali</x-tombol>
                    <x-tombol ukuran="kecil" data-quiz-lanjut hidden>Lanjut</x-tombol>
                    <x-tombol type="submit" ukuran="kecil" data-quiz-kirim>Kirim</x-tombol>
                </div>
            </form>
        </div>
    </section>
@endsection
