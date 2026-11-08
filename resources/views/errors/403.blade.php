@extends('layouts.app')

@section('judul', 'Akses Ditolak')

@section('isi')
    <section class="seksi--alt">
        <div class="wadah" style="text-align:center;max-width:560px;margin-inline:auto">
            <span class="label-kecil">Kesalahan 403</span>
            <h1 class="judul-seksi" style="margin-top:8px">Akses Ditolak</h1>
            <p style="margin-top:12px;color:var(--warna-teks-redup)">{{ $exception->getMessage() ?: 'Kamu tidak memiliki izin untuk membuka halaman ini.' }}</p>
            <div style="margin-top:28px"><x-tombol :href="route('beranda')">Kembali ke Beranda</x-tombol></div>
        </div>
    </section>
@endsection
