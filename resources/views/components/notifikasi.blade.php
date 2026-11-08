{{--
    Pesan singkat (flash) setelah suatu aksi.
    Sumber: session('sukses') dan session('galat'). Hilang otomatis lewat public/js/user.js.
--}}
@if (session('sukses') || session('galat'))
    <div class="notifikasi-wadah" role="status" aria-live="polite">
        @if (session('sukses'))
            <div class="notifikasi notifikasi--sukses" data-notifikasi>
                <x-ikon nama="centang" :ukuran="18" :tebal="2.5" />
                <span>{{ session('sukses') }}</span>
                <button type="button" class="notifikasi__tutup" data-tutup-notifikasi aria-label="Tutup pesan"><x-ikon nama="tutup" :ukuran="14" /></button>
            </div>
        @endif
        @if (session('galat'))
            <div class="notifikasi notifikasi--galat" data-notifikasi>
                <x-ikon nama="info" :ukuran="18" />
                <span>{{ session('galat') }}</span>
                <button type="button" class="notifikasi__tutup" data-tutup-notifikasi aria-label="Tutup pesan"><x-ikon nama="tutup" :ukuran="14" /></button>
            </div>
        @endif
    </div>
@endif
