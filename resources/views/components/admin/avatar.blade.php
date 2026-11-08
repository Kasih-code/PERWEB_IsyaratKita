{{-- Avatar Admin: foto profil jika ada, jika tidak inisial nama. --}}
@props(['pengguna', 'besar' => false, 'solid' => false])
@php $fotoUrl = \App\Support\Unggahan::url($pengguna->foto); @endphp
<span {{ $attributes->class(['adm-avatar', 'adm-avatar--besar' => $besar, 'adm-avatar--solid' => $solid && ! $fotoUrl]) }}>
    @if ($fotoUrl)
        <img src="{{ $fotoUrl }}" alt="Foto {{ $pengguna->name }}">
    @else
        {{ $pengguna->inisial() }}
    @endif
</span>
