{{-- Judul seksi bertingkat: label kecil + judul + deskripsi. Contoh: <x-judul-seksi label="Kurikulum Kami" judul="..." deskripsi="..." /> --}}
@props(['label' => null, 'judul', 'deskripsi' => null])

<div {{ $attributes }}>
    @if ($label)<span class="label-kecil">{{ $label }}</span>@endif
    <h2 class="judul-seksi">{{ $judul }}</h2>
    @if ($deskripsi)<p>{{ $deskripsi }}</p>@endif
</div>
