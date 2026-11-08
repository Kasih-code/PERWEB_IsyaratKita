<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk Admin | IsyaratKita</title>
    @foreach (['dasar', 'admin'] as $berkasCss)
        <link rel="stylesheet" href="{{ asset("css/{$berkasCss}.css") }}?v={{ filemtime(public_path("css/{$berkasCss}.css")) }}">
    @endforeach
</head>
<body class="adm-badan">
<main class="adm-masuk">
    <div class="adm-kartu adm-masuk__kartu">
        <div class="adm-logo">
            <span class="adm-logo__ikon"><x-admin.ikon nama="tangan" :ukuran="22" /></span>
            <span class="adm-logo__teks"><strong>IsyaratKita</strong><span>ADMIN</span></span>
        </div>
        <h1>Masuk ke Admin CMS</h1>
        <p>Khusus pengelola konten IsyaratKita.</p>

        @if (session('sukses'))
            <div class="adm-info adm-info--hijau" style="margin-top:18px" role="status"><x-admin.ikon nama="centang-lingkaran" :ukuran="18" />{{ session('sukses') }}</div>
        @endif
        @if (session('info_admin'))
            <div class="adm-info adm-info--kuning" style="margin-top:18px" role="status"><x-admin.ikon nama="info" :ukuran="18" />{{ session('info_admin') }}</div>
        @endif
        @if (Auth::check() && ! Auth::user()->adalahAdmin())
            <div class="adm-info adm-info--kuning" style="margin-top:18px" role="alert"><x-admin.ikon nama="info" :ukuran="18" />Kamu sedang masuk sebagai User biasa. Masuk dengan akun Admin untuk melanjutkan.</div>
        @endif

        <form method="POST" action="{{ route('admin.login.proses') }}" class="adm-masuk__form" novalidate>
            @csrf
            <div class="adm-bidang">
                <label class="adm-label" for="email">Alamat email</label>
                <input type="email" id="email" name="email" class="adm-input @error('email') adm-input--galat @enderror" value="{{ old('email') }}" autocomplete="username" required autofocus>
                @error('email')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
            </div>
            <div class="adm-bidang">
                <label class="adm-label" for="kata_sandi">Kata sandi</label>
                <div class="adm-kata-sandi">
                    <input type="password" id="kata_sandi" name="kata_sandi" class="adm-input @error('kata_sandi') adm-input--galat @enderror" autocomplete="current-password" required>
                    <button type="button" data-adm-lihat-sandi="kata_sandi" aria-label="Tampilkan kata sandi"><x-admin.ikon nama="mata" :ukuran="18" /></button>
                </div>
                @error('kata_sandi')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
            </div>
            <label class="adm-ingat"><input type="checkbox" name="ingat" value="1"> Ingat saya di perangkat ini</label>
            <button type="submit" class="adm-btn adm-btn--primer adm-btn--penuh">Masuk</button>
        </form>
    </div>
</main>
<script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}" defer></script>
</body>
</html>
