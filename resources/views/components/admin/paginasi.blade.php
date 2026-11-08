{{-- Paginasi bergaya desain Admin. $data = LengthAwarePaginator, $satuan = "asset"/"Modul"/"donasi" --}}
@props(['data', 'satuan'])
<div class="adm-tabel-kaki">
    <span class="adm-tabel-kaki__info">
        @if ($data->total() > 0)
            Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }} {{ $satuan }}
        @else
            Tidak ada {{ $satuan }} yang ditampilkan
        @endif
    </span>
    @if ($data->hasPages())
        <nav class="adm-halaman" aria-label="Halaman">
            @if ($data->onFirstPage())
                <span class="adm-btn adm-btn--garis" aria-disabled="true">Sebelumnya</span>
            @else
                <a class="adm-btn adm-btn--garis" href="{{ $data->previousPageUrl() }}" rel="prev">Sebelumnya</a>
            @endif

            @php
                $sekarang = $data->currentPage(); $akhir = $data->lastPage();
                $awal = max(1, $sekarang - 2); $ujung = min($akhir, $sekarang + 2);
            @endphp
            @for ($i = $awal; $i <= $ujung; $i++)
                @if ($i === $sekarang)
                    <span class="adm-halaman__nomor adm-halaman__nomor--aktif" aria-current="page">{{ $i }}</span>
                @else
                    <a class="adm-halaman__nomor" href="{{ $data->url($i) }}">{{ $i }}</a>
                @endif
            @endfor

            @if ($data->hasMorePages())
                <a class="adm-btn adm-btn--garis" href="{{ $data->nextPageUrl() }}" rel="next">Berikutnya</a>
            @else
                <span class="adm-btn adm-btn--garis" aria-disabled="true">Berikutnya</span>
            @endif
        </nav>
    @else
        <div class="adm-halaman">
            <span class="adm-btn adm-btn--garis" aria-disabled="true">Sebelumnya</span>
            <span class="adm-halaman__nomor adm-halaman__nomor--aktif">1</span>
            <span class="adm-btn adm-btn--garis" aria-disabled="true">Berikutnya</span>
        </div>
    @endif
</div>
