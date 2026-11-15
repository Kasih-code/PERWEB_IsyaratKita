{{--
    Modal Login / Daftar (Guest) - Fase 2: terhubung ke autentikasi sungguhan.
    Dibuka lewat elemen apa pun yang memiliki atribut data-buka-modal-login.
      data-mode-login="daftar"   -> modal dibuka dalam mode Daftar Gratis
      data-tujuan-login="/url"   -> halaman tujuan setelah login (default: halaman saat ini)
    Jika bukaOtomatis = true, modal langsung tampil saat halaman dibuka.
    Form dikirim ke AuthController (POST /masuk atau POST /daftar).
    Desain mengikuti login-modal.png: Nama Lengkap, Alamat Email, Kata Sandi.
    Mode "Masuk" hanya memakai Email + Kata Sandi (kolom nama diabaikan);
    mode "Daftar" mewajibkan ketiganya.
--}}
@props(['bukaOtomatis' => false])

@php
    $galat = $errors->getBag('login');
    $mode = old('mode_modal', session('mode_modal', 'masuk'));
    $mode = $mode === 'daftar' ? 'daftar' : 'masuk';
    $bukaSaatMuat = $bukaOtomatis || $galat->any() || session('buka_modal_login');
    $kembali = request()->routeIs('login') ? route('beranda') : request()->fullUrl();
@endphp

<div class="modal-latar" id="modal-login" aria-hidden="true"
     data-aksi-masuk="{{ route('login.proses') }}"
     data-aksi-daftar="{{ route('daftar.proses') }}"
     data-mode-awal="{{ $mode }}"
     @if ($bukaSaatMuat) data-buka-otomatis @endif>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="judul-modal-login">
        <div class="modal__kepala">
            <x-logo :ukuran-ikon="18" />
            <button type="button" class="modal__tutup" data-tutup-modal-login aria-label="Tutup">
                <x-ikon nama="tutup" :ukuran="16" />
            </button>
        </div>

        <h2 class="modal__judul" id="judul-modal-login" data-teks-judul>{{ $mode === 'daftar' ? 'Buat Akun Gratis' : 'Selamat Datang' }}</h2>
        <p class="modal__teks" data-teks-pengantar>{{ $mode === 'daftar' ? 'Daftar untuk memulai progres belajar BISINDO Anda.' : 'Masuk untuk memulai progres belajar BISINDO Anda.' }}</p>

        <form class="modal__form" method="POST" data-form-login novalidate
              action="{{ $mode === 'daftar' ? route('daftar.proses') : route('login.proses') }}">
            @csrf
            <input type="hidden" name="kembali" value="{{ $kembali }}" data-input-kembali>
            <input type="hidden" name="mode_modal" value="{{ $mode }}" data-input-mode>

            <div class="bidang" data-bidang-nama @if ($mode !== 'daftar') hidden @endif>
                <label class="bidang__label" for="login-nama">Nama Lengkap</label>
                <input class="input @if ($galat->has('nama')) input--galat @endif" type="text" id="login-nama" name="nama"
                       value="{{ old('nama') }}" placeholder="Andy Pratama" autocomplete="name"
                       maxlength="100" data-input-nama @if ($mode !== 'daftar') disabled @endif>
                @if ($galat->has('nama'))<p class="bidang__galat">{{ $galat->first('nama') }}</p>@endif
            </div>

            <div class="bidang">
                <label class="bidang__label" for="login-email">Alamat Email</label>
                <input class="input @if ($galat->has('email')) input--galat @endif" type="email" id="login-email" name="email"
                       value="{{ old('email') }}" placeholder="andi@email.com" autocomplete="email">
                @if ($galat->has('email'))<p class="bidang__galat">{{ $galat->first('email') }}</p>@endif
            </div>

            <div class="bidang">
                <div class="bidang__baris">
                    <label class="bidang__label" for="login-sandi">Kata Sandi</label>
                    <a href="#" class="bidang__tautan" data-lupa-sandi>Lupa Sandi?</a>
                </div>
                <div class="input-kata-sandi">
                    <input class="input @if ($galat->has('kata_sandi')) input--galat @endif" type="password" id="login-sandi" name="kata_sandi"
                           placeholder="••••••••" autocomplete="{{ $mode === 'daftar' ? 'new-password' : 'current-password' }}" data-input-sandi>
                    <button type="button" class="input-kata-sandi__mata" data-tombol-mata aria-label="Tampilkan kata sandi">
                        <x-ikon nama="mata" :ukuran="18" />
                    </button>
                </div>
                @if ($galat->has('kata_sandi'))<p class="bidang__galat">{{ $galat->first('kata_sandi') }}</p>@endif
            </div>

            <x-tombol type="submit" penuh class="modal__kirim" data-tombol-kirim>{{ $mode === 'daftar' ? 'Daftar Sekarang' : 'Masuk Sekarang' }}</x-tombol>

            <p class="modal__catatan" data-catatan-modal hidden>
                Pemulihan kata sandi belum tersedia pada versi ini.
            </p>
        </form>

        <p class="modal__daftar" data-teks-alih>
            <span data-teks-alih-label>{{ $mode === 'daftar' ? 'Sudah punya akun?' : 'Belum punya akun?' }}</span>
            <button type="button" data-alih-mode>{{ $mode === 'daftar' ? 'Masuk' : 'Daftar Gratis' }}</button>
        </p>
    </div>
</div>
