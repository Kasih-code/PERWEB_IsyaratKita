@extends('layouts.admin')

@section('judul', 'Edit Profile')
@section('menu', 'profil')

@section('isi')
    <div class="adm-judul-halaman">
        <div>
            <h2>Edit Profile Administrator</h2>
            <p>Perbarui informasi akun dan foto profil.</p>
        </div>
        <div class="adm-judul-halaman__aksi">
            <a href="{{ route('admin.profil') }}" class="adm-btn adm-btn--garis">Batal</a>
            <button type="submit" form="form-profil" class="adm-btn adm-btn--primer"><x-admin.ikon nama="simpan" :ukuran="18" /> Simpan Perubahan</button>
        </div>
    </div>

    <form id="form-profil" method="POST" action="{{ route('admin.profil.update') }}" enctype="multipart/form-data" class="adm-profil" novalidate>
        @csrf @method('PUT')

        <section class="adm-kartu adm-kartu__isi" style="align-self:start">
            <h3>Foto Profile</h3>
            <div class="adm-formulir">
                <div class="adm-foto-kotak">
                    <x-admin.avatar :pengguna="$admin" :besar="true" :solid="true" data-foto-avatar />
                    <img alt="Pratinjau foto" data-foto-pratinjau hidden style="width:100%;height:100%;object-fit:cover">
                </div>
                <label class="adm-btn adm-btn--garis adm-btn--penuh adm-tombol-berkas" style="margin-top:0">
                    <x-admin.ikon nama="unggah-tray" :ukuran="16" /> Unggah Foto Baru
                    <input type="file" name="foto" accept="image/jpeg,image/png" data-foto-berkas>
                </label>
                <p class="adm-kartu__sub">JPG atau PNG, maksimal 2 MB. Gunakan foto persegi agar hasil optimal.</p>
                @error('foto')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
            </div>
        </section>

        <div class="adm-kolom">
            <section class="adm-kartu adm-kartu__isi">
                <h3>Informasi Pribadi</h3>
                <div class="adm-formulir">
                    <div class="adm-grid-2">
                        <div class="adm-bidang">
                            <label class="adm-label" for="name">Nama lengkap</label>
                            <input type="text" id="name" name="name" class="adm-input @error('name') adm-input--galat @enderror" value="{{ old('name', $admin->name) }}" maxlength="100" required>
                            @error('name')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                        </div>
                        <div class="adm-bidang">
                            <label class="adm-label" for="whatsapp">Nomor WhatsApp</label>
                            <input type="text" id="whatsapp" name="whatsapp" class="adm-input @error('whatsapp') adm-input--galat @enderror" value="{{ old('whatsapp', $admin->whatsapp) }}" maxlength="20" inputmode="tel">
                            @error('whatsapp')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="adm-bidang">
                        <label class="adm-label" for="email">Alamat email</label>
                        <input type="email" id="email" name="email" class="adm-input @error('email') adm-input--galat @enderror" value="{{ old('email', $admin->email) }}" maxlength="255" required>
                        @error('email')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>

            <section class="adm-kartu adm-kartu__isi" id="ubah-sandi">
                <h3>Ubah Kata Sandi</h3>
                <p class="adm-kartu__sub">Biarkan kosong jika tidak ingin mengubah kata sandi.</p>
                <div class="adm-formulir">
                    <div class="adm-bidang">
                        <label class="adm-label" for="sandi_sekarang">Kata sandi saat ini</label>
                        <input type="password" id="sandi_sekarang" name="sandi_sekarang" class="adm-input @error('sandi_sekarang') adm-input--galat @enderror" autocomplete="current-password">
                        @error('sandi_sekarang')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                    </div>
                    <div class="adm-grid-2">
                        <div class="adm-bidang">
                            <label class="adm-label" for="sandi_baru">Kata sandi baru</label>
                            <input type="password" id="sandi_baru" name="sandi_baru" class="adm-input @error('sandi_baru') adm-input--galat @enderror" autocomplete="new-password" minlength="8" data-sandi-baru>
                            @error('sandi_baru')<span class="adm-galat" role="alert">{{ $message }}</span>@enderror
                        </div>
                        <div class="adm-bidang">
                            <label class="adm-label" for="sandi_baru_confirmation">Konfirmasi kata sandi</label>
                            <input type="password" id="sandi_baru_confirmation" name="sandi_baru_confirmation" class="adm-input" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="adm-kekuatan" data-kekuatan data-tingkat="0">
                        <div class="adm-kekuatan__baris"><span>Kekuatan kata sandi</span><strong data-kekuatan-label></strong></div>
                        <div class="adm-kekuatan__bar"><i></i><i></i><i></i><i></i></div>
                    </div>
                </div>
            </section>
        </div>
    </form>
@endsection
