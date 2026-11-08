{{--
    Popup Edit Profil (User). Gaya disamakan dengan Edit Profile Admin.
    Hanya Nama yang dapat diubah; Alamat Email bersifat baca-saja.
    Dibuka dari menu profil di navbar (data-buka-modal="modal-profil").
    Terbuka otomatis jika validasi nama gagal.
--}}
@php
    $galatProfil = $errors->getBag('profil');
@endphp

<div class="modal-latar" id="modal-profil" aria-hidden="true" data-bisa-tutup-latar
     @if ($galatProfil->any()) data-buka-otomatis @endif>
    <div class="modal modal--profil" role="dialog" aria-modal="true" aria-labelledby="judul-profil">
        <div class="modal-profil__ikon"><x-ikon nama="pengguna-lingkaran" :ukuran="64" :tebal="1.6" /></div>

        <div class="modal-profil__kepala">
            <h2 id="judul-profil">Edit Profil</h2>
            <p>Perbarui informasi akun.</p>
        </div>

        <form method="POST" action="{{ route('profil.update') }}" novalidate>
            @csrf
            @method('PATCH')

            <div class="modal-profil__isi">
                <h3 class="modal-profil__judul">Informasi Pribadi</h3>

                <div class="bidang">
                    <label class="bidang__label" for="profil-nama">Nama lengkap</label>
                    <input class="input @if ($galatProfil->has('nama')) input--galat @endif" type="text" id="profil-nama" name="nama"
                           value="{{ old('nama', $penggunaLogin->name) }}" maxlength="100" autocomplete="name" data-fokus-awal>
                    @if ($galatProfil->has('nama'))<p class="bidang__galat">{{ $galatProfil->first('nama') }}</p>@endif
                </div>

                <div class="bidang">
                    <label class="bidang__label" for="profil-email">Alamat email</label>
                    <input class="input input--baca-saja" type="email" id="profil-email" value="{{ $penggunaLogin->email }}" readonly tabindex="-1">
                </div>
            </div>

            <div class="modal-profil__aksi">
                <x-tombol variasi="garis" data-tutup-modal>Batal</x-tombol>
                <x-tombol type="submit">Simpan Perubahan</x-tombol>
            </div>
        </form>
    </div>
</div>