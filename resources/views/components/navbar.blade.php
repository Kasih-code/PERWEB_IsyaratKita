{{--
    Navbar utama.
    - Guest : tombol Masuk + Daftar Gratis (membuka modal login).
    - User  : ikon + nama User dengan menu (Edit Profil, Keluar), sesuai desain User.
--}}
@php
    $menuNavbar = \App\Data\DataDummy::menuNavbar();
@endphp

<header class="navbar">
    <div class="wadah">
        <div class="navbar__isi">
            <div class="navbar__logo"><x-logo /></div>

            <nav class="navbar__menu" aria-label="Menu utama">
                @foreach ($menuNavbar as $menu)
                    <a href="{{ route($menu['rute']) }}"
                       @class(['navbar__tautan', 'navbar__tautan--aktif' => request()->routeIs($menu['pola'])])
                       @if (request()->routeIs($menu['pola'])) aria-current="page" @endif>
                        {{ $menu['nama'] }}
                    </a>
                @endforeach
            </nav>

            @if ($statusLogin)
                <div class="navbar__aksi">
                    <div class="profil-menu" data-profil-menu>
                        <button type="button" class="profil-menu__tombol" data-profil-tombol
                                aria-expanded="false" aria-haspopup="true" aria-controls="profil-menu-isi">
                            <x-ikon nama="pengguna-lingkaran" :ukuran="28" :tebal="1.7" />
                            <span class="profil-menu__nama">{{ $penggunaLogin->name }}</span>
                        </button>

                        <div class="profil-menu__isi" id="profil-menu-isi" data-profil-isi hidden>
                            <div class="profil-menu__kepala">
                                <x-ikon nama="pengguna-lingkaran" :ukuran="26" :tebal="1.7" />
                                <div class="profil-menu__identitas">
                                    <strong>{{ $penggunaLogin->name }}</strong>
                                    <span>{{ $penggunaLogin->email }}</span>
                                </div>
                            </div>
                            @if ($penggunaLogin->adalahAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="profil-menu__butir">
                                    <x-ikon nama="pengguna" :ukuran="14" /> Admin CMS
                                </a>
                            @endif
                            <button type="button" class="profil-menu__butir" data-buka-modal="modal-profil">
                                <x-ikon nama="pengguna" :ukuran="14" /> Edit Profil
                            </button>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="profil-menu__butir profil-menu__butir--keluar">
                                    <x-ikon nama="keluar" :ukuran="14" /> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <div class="navbar__aksi">
                    <button type="button" class="navbar__masuk" data-buka-modal-login>Masuk</button>
                    <x-tombol data-buka-modal-login data-mode-login="daftar">Daftar Gratis</x-tombol>
                </div>
            @endif

            <button type="button" class="navbar__tombol-menu" data-tombol-menu
                    aria-expanded="false" aria-controls="panel-navbar" aria-label="Buka menu">
                <x-ikon nama="menu" :ukuran="22" />
            </button>
        </div>
    </div>

    {{-- Panel menu untuk layar kecil --}}
    <div class="navbar__panel" id="panel-navbar">
        @foreach ($menuNavbar as $menu)
            <a href="{{ route($menu['rute']) }}"
               @class(['navbar__tautan-mobile', 'aktif' => request()->routeIs($menu['pola'])])>
                {{ $menu['nama'] }}
            </a>
        @endforeach

        @if ($statusLogin)
            <div class="navbar__panel-pengguna">
                <div class="profil-menu__kepala">
                    <x-ikon nama="pengguna-lingkaran" :ukuran="26" :tebal="1.7" />
                    <div class="profil-menu__identitas">
                        <strong>{{ $penggunaLogin->name }}</strong>
                        <span>{{ $penggunaLogin->email }}</span>
                    </div>
                </div>
                <div class="navbar__panel-aksi">
                    @if ($penggunaLogin->adalahAdmin())
                        <x-tombol variasi="garis" :href="route('admin.dashboard')">Admin CMS</x-tombol>
                    @endif
                    <x-tombol variasi="garis" data-buka-modal="modal-profil">Edit Profil</x-tombol>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-tombol type="submit" variasi="garis" class="tombol--bahaya" style="width:100%">Keluar</x-tombol>
                    </form>
                </div>
            </div>
        @else
            <div class="navbar__panel-aksi">
                <x-tombol variasi="garis" data-buka-modal-login>Masuk</x-tombol>
                <x-tombol data-buka-modal-login data-mode-login="daftar">Daftar Gratis</x-tombol>
            </div>
        @endif
    </div>
</header>
