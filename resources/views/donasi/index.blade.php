
@extends('layouts.app')

@section('judul', 'Donasi')

@section('isi')
    {{-- ===== Hero ===== --}}
    <x-hero ukuran="donasi" label="Program Inklusi" :gambar="asset('images/umum/donasi.png')">
        <x-slot:judul>Berbagi Kebaikan Bersama IsyaratKita</x-slot:judul>
        Setiap donasi yang Anda berikan dialokasikan penuh untuk membiayai pengadaan modul pembelajaran gratis bagi sekolah-sekolah luar biasa, pelatihan interpreter BISINDO, dan kampanye kepedulian inklusi sosial.
    </x-hero>

    {{-- ===== Formulir donasi ===== --}}
    <section class="seksi--alt donasi-form-seksi">
        <div class="wadah">
            <div class="donasi-form-kepala">
                <h2>Formulir Donasi Setara</h2>
                <p>Silakan isi detail dukungan sukarela Anda di bawah ini.</p>
            </div>

            @if ($statusLogin)
                {{-- ===== User: formulir donasi aktif ===== --}}
                <form
                    class="kartu donasi-kartu"
                    novalidate
                    data-form-donasi
                    data-aksi-simpan="{{ route('donasi.simpan') }}"
                    data-aksi-status="{{ url('/donasi/__KODE__/status') }}"
                >
                    <div class="bidang">
                        <label class="bidang__label" for="donasi-nama">
                            Nama Lengkap
                        </label>
                        <input
                            class="input"
                            type="text"
                            id="donasi-nama"
                            name="nama"
                            value="{{ $penggunaLogin->name }}"
                            placeholder="Andi Wijaya"
                            autocomplete="name"
                            maxlength="100"
                        >
                    </div>

                    <div class="bidang">
                        <label class="bidang__label" for="donasi-email">
                            Email Aktif
                        </label>
                        <input
                            class="input"
                            type="email"
                            id="donasi-email"
                            name="email"
                            value="{{ $penggunaLogin->email }}"
                            placeholder="andi@gmail.com"
                            autocomplete="email"
                            maxlength="255"
                        >
                    </div>

                    <div class="bidang">
                        <label class="bidang__label" for="donasi-wa">
                            Nomor WhatsApp
                        </label>
                        <input
                            class="input"
                            type="tel"
                            id="donasi-wa"
                            name="whatsapp"
                            placeholder="08123456789"
                            autocomplete="tel"
                            inputmode="tel"
                            maxlength="20"
                        >
                    </div>

                    <div class="bidang">
                        <span class="bidang__label" id="label-nominal">
                            Pilih Nominal Donasi (Rp)
                        </span>

                        <div class="nominal" role="group" aria-labelledby="label-nominal">
                            @foreach ($pilihanNominal as $nominal)
                                <button
                                    type="button"
                                    data-nominal
                                    data-nilai="{{ $nominal }}"
                                    aria-pressed="{{ $nominal === $nominalTerpilih ? 'true' : 'false' }}"
                                    @class(['aktif' => $nominal === $nominalTerpilih])
                                >
                                    {{ number_format($nominal, 0, ',', '.') }}
                                </button>
                            @endforeach
                        </div>

                        <input
                            type="hidden"
                            name="nominal"
                            value="{{ $nominalTerpilih }}"
                            data-nominal-terpilih
                        >
                    </div>

                    <x-tombol type="submit" penuh>
                        Lanjutkan Pembayaran Donasi
                    </x-tombol>

                    <p class="donasi-catatan" data-status-donasi aria-live="polite"></p>

                    <p class="donasi-catatan">
                        Terima Kasih! God Bless You Always, Kak.
                    </p>

                    <p
                        class="bidang__galat"
                        data-galat-donasi
                        role="alert"
                        style="display: none;"
                    ></p>
                </form>
            @else
                {{-- ===== Guest: donasi membutuhkan login ===== --}}
                <form class="kartu donasi-kartu" novalidate>
                    <div class="bidang">
                        <label class="bidang__label" for="donasi-nama">
                            Nama Lengkap
                        </label>
                        <input
                            class="input"
                            type="text"
                            id="donasi-nama"
                            placeholder="Andi Wijaya"
                            autocomplete="name"
                        >
                    </div>

                    <div class="bidang">
                        <label class="bidang__label" for="donasi-email">
                            Email Aktif
                        </label>
                        <input
                            class="input"
                            type="email"
                            id="donasi-email"
                            placeholder="andi@gmail.com"
                            autocomplete="email"
                        >
                    </div>

                    <div class="bidang">
                        <label class="bidang__label" for="donasi-wa">
                            Nomor WhatsApp
                        </label>
                        <input
                            class="input"
                            type="tel"
                            id="donasi-wa"
                            placeholder="08123456789"
                            autocomplete="tel"
                        >
                    </div>

                    <div class="bidang">
                        <span class="bidang__label" id="label-nominal">
                            Pilih Nominal Donasi (Rp)
                        </span>

                        <div class="nominal" role="group" aria-labelledby="label-nominal">
                            @foreach ($pilihanNominal as $nominal)
                                <button
                                    type="button"
                                    data-nominal
                                    data-nilai="{{ $nominal }}"
                                    aria-pressed="{{ $nominal === $nominalTerpilih ? 'true' : 'false' }}"
                                    @class(['aktif' => $nominal === $nominalTerpilih])
                                >
                                    {{ number_format($nominal, 0, ',', '.') }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <x-tombol
                        penuh
                        data-buka-modal-login
                        data-tujuan-login="{{ route('donasi.index', [], false) }}"
                    >
                        Lanjutkan Pembayaran Donasi
                    </x-tombol>

                    <p class="donasi-catatan">
                        Terima Kasih! God Bless You Always, Kak.
                    </p>
                </form>
            @endif
        </div>
    </section>
@endsection

@if ($statusLogin)
    @push('scripts')
        @if (config('services.midtrans.is_production'))
            <script
                src="https://app.midtrans.com/snap/snap.js"
                data-client-key="{{ config('services.midtrans.client_key') }}"
            ></script>
        @else
            <script
                src="https://app.sandbox.midtrans.com/snap/snap.js"
                data-client-key="{{ config('services.midtrans.client_key') }}"
            ></script>
        @endif
    @endpush
@endif