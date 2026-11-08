@extends('layouts.admin')

@section('judul', 'Admin Profile')
@section('menu', 'profil')

@section('isi')
    <div class="adm-judul-halaman">
        <div>
            <h2>Profil Administrator</h2>
            <p>Kelola informasi pribadi dan keamanan akun admin.</p>
        </div>
    </div>

    <div class="adm-profil">
        <section class="adm-kartu adm-profil__ringkas">
            <x-admin.avatar :pengguna="$admin" :besar="true" />
            <h3>{{ $admin->name }}</h3>
            <span class="adm-profil__email">{{ $admin->email }}</span>
            <span class="adm-lencana" style="margin-top:6px">{{ $admin->labelPeran() }}</span>
            <dl class="adm-daftar-info">
                <div><dt>Bergabung</dt><dd>{{ $admin->created_at->translatedFormat('j F Y') }}</dd></div>
                <div><dt>Login terakhir</dt><dd>{{ $admin->terakhir_login_pada ? $admin->terakhir_login_pada->translatedFormat('d M Y, H:i') : '-' }}</dd></div>
                <div><dt>Status akun</dt><dd>Aktif</dd></div>
            </dl>
            <a href="{{ route('admin.profil.edit') }}" class="adm-btn adm-btn--primer adm-btn--penuh" style="margin-top:18px"><x-admin.ikon nama="pensil" :ukuran="16" /> Edit Profile</a>
        </section>

        <div class="adm-kolom">
            <section class="adm-kartu adm-kartu__isi">
                <h3>Informasi Pribadi</h3>
                <dl class="adm-data">
                    <div><dt>Nama lengkap</dt><dd>{{ $admin->name }}</dd></div>
                    <div><dt>Alamat email</dt><dd>{{ $admin->email }}</dd></div>
                    <div><dt>Nomor WhatsApp</dt><dd>{{ $admin->whatsapp ?: '-' }}</dd></div>
                </dl>
            </section>

            <section class="adm-kartu adm-kartu__isi">
                <h3>Keamanan Akun</h3>
                <div class="adm-formulir" style="gap:0">
                    <div class="adm-baris-fitur">
                        <span class="adm-baris-fitur__ikon"><x-admin.ikon nama="kunci" :ukuran="20" /></span>
                        <span class="adm-baris-fitur__teks">
                            <strong>Kata sandi</strong>
                            <span>{{ $admin->sandi_diubah_pada ? 'Terakhir diubah '.$admin->sandi_diubah_pada->diffForHumans() : 'Belum pernah diubah lewat CMS' }}</span>
                        </span>
                        <a href="{{ route('admin.profil.edit') }}#ubah-sandi" class="adm-btn adm-btn--garis">Ubah Kata Sandi</a>
                    </div>
                    <div class="adm-baris-fitur">
                        <span class="adm-baris-fitur__ikon adm-baris-fitur__ikon--abu"><x-admin.ikon nama="perisai" :ukuran="20" /></span>
                        <span class="adm-baris-fitur__teks"><strong>Verifikasi dua langkah</strong><span>Belum tersedia pada versi ini</span></span>
                        <span class="adm-lencana adm-lencana--abu">Belum aktif</span>
                    </div>
                </div>
            </section>

            <section class="adm-kartu adm-kartu__isi">
                <h3>Sesi Terbaru</h3>
                <div class="adm-formulir" style="gap:0">
                    <div class="adm-baris-fitur">
                        <span class="adm-baris-fitur__ikon adm-baris-fitur__ikon--abu"><x-admin.ikon nama="monitor" :ukuran="20" /></span>
                        <span class="adm-baris-fitur__teks"><strong>{{ $sesi['perangkat'] }}</strong><span>IP {{ $sesi['ip'] }} · Sekarang</span></span>
                        <span class="adm-lencana">Sesi ini</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
