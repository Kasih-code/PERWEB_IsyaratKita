@extends('layouts.app')

@section('judul', 'Beranda')

@section('isi')
    {{-- ===== Hero ===== --}}
    <x-hero ukuran="besar" label="Belajar Bahasa Isyarat Indonesia Online" :gambar="asset('images/umum/hero1.png')">
        <x-slot:judul>
            Hubungkan Jarak, <span class="teks-aksen">Suarakan Hati</span> Lewat Bahasa Isyarat
        </x-slot:judul>

        IsyaratKita adalah platform belajar BISINDO interaktif yang dirancang khusus untuk mempermudah siapa saja berkomunikasi secara inklusif dan setara dengan Teman Tuli.

        <x-slot:aksi>
            <x-tombol :href="route('modul.index')">Mulai Belajar Sekarang</x-tombol>
        </x-slot:aksi>
    </x-hero>

    {{-- ===== Tentang IsyaratKita ===== --}}
    <section class="seksi seksi--alt" id="tentang">
        <div class="wadah tentang__isi">
            <x-placeholder-gambar class="tentang__gambar" :src="asset('images/umum/rangkul.png')" alt="Ilustrasi kebersamaan dan komunikasi inklusif" />

            <div>
                <span class="label-kecil">Tentang Kami</span>
                <h2 class="judul-seksi tentang__judul">Mengapa IsyaratKita Hadir?</h2>
                <p class="tentang__teks">
                    Kami hadir sebagai jawaban atas tantangan komunikasi sehari-hari antara Teman Dengar dan Teman Tuli. Dengan kurikulum terstruktur dan menyenangkan, kami percaya inklusivitas sejati dimulai dari langkah kecil untuk saling mengerti.
                </p>

                <div class="tentang__fitur">
                    <div>
                        <h3>Belajar Interaktif</h3>
                        <p>Materi dikemas dengan visualisasi video, gambar real-time, dan latihan quiz seru.</p>
                    </div>
                    <div>
                        <h3>Dukungan Komunitas</h3>
                        <p>100% donasi dan materi dikurasi bersama pakar Tuli dan organisasi inklusi.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Apa itu BISINDO ===== --}}
    <section class="seksi" id="bisindo">
        <div class="wadah bisindo__isi">
            <div>
                <span class="label-kecil">Mengenal Isyarat</span>
                <h2 class="judul-seksi bisindo__judul">Apa Itu BISINDO?</h2>
                <p class="bisindo__teks">
                    BISINDO (Bahasa Isyarat Indonesia) adalah bahasa isyarat alami yang tumbuh dari budaya Teman Tuli Indonesia. Berbeda dengan SIBI yang berpola tata bahasa formal, BISINDO lebih komunikatif, ekspresif, dan menggunakan dua tangan secara aktif serta ekspresi wajah yang natural.
                </p>
                <div class="bisindo__aksi">
                    {{-- Mengarah ke materi pengantar BISINDO (Modul 1). Guest akan diminta login. --}}
                    <x-tombol variasi="garis" :href="route('modul.detail', 1)">Pelajari Lebih Lanjut</x-tombol>
                </div>
            </div>
            <x-placeholder-gambar class="bisindo__gambar" :src="asset('images/umum/confused final.png')" alt="BISINDO" />
        </div>
    </section>

    {{-- ===== Preview 3 modul ===== --}}
    <section class="seksi seksi--alt" id="kurikulum">
        <div class="wadah">
            <x-judul-seksi class="kurikulum__kepala" label="Kurikulum Kami"
                           judul="Mulai Belajar Bersama IsyaratKita"
                           deskripsi="Alur belajar terstruktur dari dasar hingga mahir yang mudah diikuti." />

            <div class="kurikulum__daftar">
                @foreach ($daftarModulPratinjau as $modul)
                    <x-kartu-modul-pratinjau :modul="$modul" />
                @endforeach
            </div>

            <div class="kurikulum__aksi">
                <x-tombol :href="route('modul.index')">Lihat Selengkapnya</x-tombol>
            </div>
        </div>
    </section>

    {{-- ===== Ajakan bergabung ===== --}}
    <section class="seksi ajakan">
        <div class="wadah">
            <h2 class="judul-seksi">Siap Belajar BISINDO Bersama IsyaratKita?</h2>
            <p>Bergabunglah dengan ribuan siswa lainnya yang sudah memulai langkah mereka menuju kesetaraan di Indonesia.</p>
            <x-tombol :href="route('modul.index')">Mulai Belajar Sekarang</x-tombol>
        </div>
    </section>
@endsection
