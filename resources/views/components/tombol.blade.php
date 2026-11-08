{{--
    Tombol yang dapat dipakai ulang.
    variasi : primer | garis
    ukuran  : null | kecil
    href    : jika diisi, tombol dirender sebagai tautan <a>
    Contoh  : <x-tombol href="{{ route('modul.index') }}">Mulai Belajar</x-tombol>
--}}
@props(['variasi' => 'primer', 'href' => null, 'ukuran' => null, 'penuh' => false, 'type' => 'button'])

@php
    $kelas = [
        'tombol',
        "tombol--{$variasi}",
        'tombol--kecil' => $ukuran === 'kecil',
        'tombol--penuh' => $penuh,
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($kelas) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($kelas) }}>{{ $slot }}</button>
@endif
