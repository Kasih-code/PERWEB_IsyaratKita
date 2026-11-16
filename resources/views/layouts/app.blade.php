<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('deskripsi', 'IsyaratKita - website pembelajaran BISINDO (Bahasa Isyarat Indonesia) dengan modul, quiz, dan kamus interaktif.')">
    <title>@yield('judul', 'IsyaratKita') | IsyaratKita</title>

    {{-- user.css = tambahan Fase 2 (navbar User, quiz, modal profil/QRIS, notifikasi). --}}
    @foreach (['dasar', 'komponen', 'halaman', 'user'] as $berkasCss)
        <link rel="stylesheet" href="{{ asset("css/{$berkasCss}.css") }}?v={{ filemtime(public_path("css/{$berkasCss}.css")) }}">
    @endforeach
{{-- Tambahan CSS untuk tampilan baru --}}
<link rel="stylesheet" href="{{ asset('css/neww.css') }}?v={{ filemtime(public_path('css/neww.css')) }}">
</head>
<body>
    @unless ($pratinjau ?? false)
        <x-navbar />
    @endunless

    <x-notifikasi />

    <main>
        @yield('isi')
    </main>

    @unless ($pratinjau ?? false)
        <x-footer />
    @endunless

    {{-- Guest: modal login (dibuka otomatis pada halaman yang membutuhkan login). --}}
    {{-- User : popup edit profil (dibuka dari menu profil pada navbar). --}}
    @if ($statusLogin)
        <x-modal-profil />
    @else
        <x-modal-login :buka-otomatis="$tampilkanModalLogin ?? false" />
    @endif

    @stack('scripts')

    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
    <script src="{{ asset('js/user.js') }}?v={{ filemtime(public_path('js/user.js')) }}" defer></script>
{{-- Tambahan JavaScript untuk animasi --}}
<script src="{{ asset('js/new.js') }}?v={{ filemtime(public_path('js/new.js')) }}" defer></script>
</body>
</html>
