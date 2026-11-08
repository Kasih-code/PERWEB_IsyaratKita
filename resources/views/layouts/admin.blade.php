<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('judul', 'Admin') | IsyaratKita Admin</title>
    @foreach (['dasar', 'admin'] as $berkasCss)
        <link rel="stylesheet" href="{{ asset("css/{$berkasCss}.css") }}?v={{ filemtime(public_path("css/{$berkasCss}.css")) }}">
    @endforeach
</head>
<body class="adm-badan">
@php
    $menuAktif = trim($__env->yieldContent('menu'));
    $itemMenu = [
        'dashboard' => ['Dashboard', 'dashboard', route('admin.dashboard')],
        'asset'     => ['Kelola Asset', 'gambar', route('admin.asset.index')],
        'modul'     => ['Kelola Modul', 'buku', route('admin.modul.index')],
        'donasi'    => ['Laporan Donasi Masuk', 'donasi', route('admin.donasi.index')],
    ];
@endphp
<div class="adm">
    {{-- ===== Sidebar ===== --}}
    <aside class="adm-sisi" id="adm-sisi" aria-label="Menu Admin">
        <a href="{{ route('admin.dashboard') }}" class="adm-logo" aria-label="IsyaratKita Admin - Dashboard">
            <span class="adm-logo__ikon"><x-admin.ikon nama="tangan" :ukuran="22" /></span>
            <span class="adm-logo__teks"><strong>IsyaratKita</strong><span>ADMIN</span></span>
        </a>

        <nav class="adm-nav">
            @foreach ($itemMenu as $kunci => [$nama, $ikon, $url])
                <a href="{{ $url }}" @class(['adm-nav__butir', 'adm-nav__butir--aktif' => $menuAktif === $kunci]) @if ($menuAktif === $kunci) aria-current="page" @endif>
                    <x-admin.ikon :nama="$ikon" :ukuran="20" /> {{ $nama }}
                </a>
            @endforeach
        </nav>

        <div class="adm-sisi__bawah">
            <a href="{{ route('admin.profil') }}" @class(['adm-nav__butir', 'adm-nav__butir--aktif' => $menuAktif === 'profil']) @if ($menuAktif === 'profil') aria-current="page" @endif>
                <x-admin.ikon nama="pengguna" :ukuran="20" /> Admin Profile
            </a>
            <button type="button" class="adm-nav__butir adm-nav__butir--keluar" data-adm-buka="modal-logout">
                <x-admin.ikon nama="keluar" :ukuran="20" /> Logout
            </button>
        </div>
    </aside>

    <div class="adm-utama">
        {{-- ===== Header ===== --}}
        <header class="adm-header">
            <div class="adm-header__kiri">
                <button type="button" class="adm-tombol-menu" data-adm-sisi aria-controls="adm-sisi" aria-label="Buka menu">
                    <x-admin.ikon nama="menu" :ukuran="20" />
                </button>
                <div>
                    <div class="adm-jejak">Admin / @yield('jejak', trim($__env->yieldContent('judul')))</div>
                    <h1>@yield('judul', 'Admin')</h1>
                </div>
            </div>

            <div class="adm-header__kanan">
                <div class="adm-menu-wadah">
                    <button type="button" class="adm-ikon-tombol" data-adm-dropdown="dd-notif" aria-label="Notifikasi donasi" aria-expanded="false">
                        <x-admin.ikon nama="lonceng" :ukuran="20" />
                        @if ($notifDonasi->isNotEmpty())<span class="adm-titik"></span>@endif
                    </button>
                    <div class="adm-dropdown adm-dropdown--lebar" id="dd-notif" hidden>
                        <div class="adm-dropdown__kepala">Donasi terbaru</div>
                        @forelse ($notifDonasi as $d)
                            <a class="adm-notif" href="{{ route('admin.donasi.index', ['cari' => $d->kode]) }}">
                                {{ $d->nama }} berdonasi {{ $d->nominal_rupiah }}
                                <span>{{ $d->tanggal->diffForHumans() }}{{ $d->simulasi ? ' · simulasi' : '' }}</span>
                            </a>
                        @empty
                            <span class="adm-notif adm-notif--kosong">Belum ada notifikasi.</span>
                        @endforelse
                    </div>
                </div>

                <span class="adm-header__pemisah" aria-hidden="true"></span>

                <div class="adm-menu-wadah">
                    <button type="button" class="adm-akun" data-adm-dropdown="dd-akun" aria-expanded="false">
                        <x-admin.avatar :pengguna="$penggunaLogin" />
                        <span class="adm-akun__info">
                            <strong>{{ $penggunaLogin->name }}</strong>
                            <span>{{ $penggunaLogin->labelPeran() }}</span>
                        </span>
                        <x-admin.ikon nama="chevron-bawah" :ukuran="16" />
                    </button>
                    <div class="adm-dropdown" id="dd-akun" hidden>
                        <a href="{{ route('admin.profil') }}"><x-admin.ikon nama="pengguna" :ukuran="16" /> Admin Profile</a>
                        <button type="button" class="adm-dropdown__bahaya" data-adm-buka="modal-logout"><x-admin.ikon nama="keluar" :ukuran="16" /> Logout</button>
                    </div>
                </div>
            </div>
        </header>

        <main class="adm-isi @yield('kelas-isi')">
            @yield('isi')
        </main>
    </div>
</div>

{{-- ===== Modal konfirmasi logout ===== --}}
<div class="adm-modal-latar" id="modal-logout" role="dialog" aria-modal="true" aria-labelledby="judul-logout" aria-hidden="true">
    <div class="adm-modal adm-modal--kecil">
        <div class="adm-konfirmasi__ikon"><x-admin.ikon nama="keluar" :ukuran="24" /></div>
        <h2 id="judul-logout">Keluar dari Admin CMS?</h2>
        <p>Sesi Anda akan berakhir. Pastikan semua perubahan sudah tersimpan sebelum keluar.</p>
        <form method="POST" action="{{ route('admin.logout') }}" class="adm-modal__aksi">
            @csrf
            <button type="button" class="adm-btn adm-btn--garis" data-adm-tutup>Batal</button>
            <button type="submit" class="adm-btn adm-btn--bahaya">Ya, Logout</button>
        </form>
    </div>
</div>

{{-- ===== Modal konfirmasi hapus (dipakai bersama; diisi lewat atribut data-adm-hapus-*) ===== --}}
<div class="adm-modal-latar" id="modal-hapus" role="dialog" aria-modal="true" aria-labelledby="judul-hapus" aria-hidden="true">
    <div class="adm-modal adm-modal--kecil">
        <div class="adm-konfirmasi__ikon"><x-admin.ikon nama="sampah" :ukuran="24" /></div>
        <h2 id="judul-hapus" data-hapus-judul>Hapus data?</h2>
        <p data-hapus-teks>Tindakan ini tidak dapat dibatalkan.</p>
        <form method="POST" action="#" class="adm-modal__aksi" data-hapus-form>
            @csrf
            @method('DELETE')
            <button type="button" class="adm-btn adm-btn--garis" data-adm-tutup>Batal</button>
            <button type="submit" class="adm-btn adm-btn--bahaya" data-hapus-tombol>Ya, Hapus</button>
        </form>
    </div>
</div>

{{-- ===== Toast (pesan sukses / galat dari server) ===== --}}
<div class="adm-toast-wadah" aria-live="polite">
    @if (session('sukses'))
        <div class="adm-toast" data-adm-toast role="status">
            <span class="adm-toast__ikon"><x-admin.ikon nama="centang" :ukuran="18" :tebal="2.5" /></span>
            <span class="adm-toast__teks"><strong>Berhasil</strong><span>{{ session('sukses') }}</span></span>
            <button type="button" class="adm-toast__tutup" data-adm-toast-tutup aria-label="Tutup"><x-admin.ikon nama="tutup" :ukuran="16" /></button>
        </div>
    @endif
    @if (session('galat'))
        <div class="adm-toast adm-toast--galat" data-adm-toast role="alert">
            <span class="adm-toast__ikon"><x-admin.ikon nama="tutup" :ukuran="18" :tebal="2.5" /></span>
            <span class="adm-toast__teks"><strong>Gagal</strong><span>{{ session('galat') }}</span></span>
            <button type="button" class="adm-toast__tutup" data-adm-toast-tutup aria-label="Tutup"><x-admin.ikon nama="tutup" :ukuran="16" /></button>
        </div>
    @endif
</div>

@stack('modal')

<script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}" defer></script>
@stack('skrip')
</body>
</html>
