@extends('layouts.admin')

@section('judul', 'Laporan Donasi')
@section('menu', 'donasi')
@section('jejak', 'Laporan Donasi')

@section('isi')
    <div class="adm-judul-halaman">
        <div>
            <h2>Laporan Donasi Masuk</h2>
            <p>Pantau transaksi donasi dan ekspor laporan untuk kebutuhan administrasi.</p>
        </div>
        <div class="adm-judul-halaman__aksi">
            <a href="{{ route('admin.donasi.export', request()->query()) }}" class="adm-btn adm-btn--garis"><x-admin.ikon nama="unduh" :ukuran="18" /> Export Laporan</a>
        </div>
    </div>

    @if ($adaSimulasi)
        <div class="adm-info adm-info--kuning"><x-admin.ikon nama="info" :ukuran="18" /> Belum ada payment gateway: donasi dari alur QRIS saat ini adalah simulasi (tidak ada uang yang dipindahkan) dan diberi label "Simulasi".</div>
    @endif

    <section class="adm-statistik adm-statistik--dua" aria-label="Ringkasan donasi">
        <article class="adm-kartu adm-stat">
            <div class="adm-stat__kepala">Total Donasi <span class="adm-stat__ikon"><x-admin.ikon nama="donasi" :ukuran="18" /></span></div>
            <div class="adm-stat__nilai">{{ \App\Models\Donasi::rupiah($totalDonasi) }}</div>
            <div class="adm-stat__catatan adm-stat__catatan--redup">{{ number_format($jumlahBerhasil, 0, ',', '.') }} donasi berhasil</div>
        </article>
        <article class="adm-kartu adm-stat">
            <div class="adm-stat__kepala">Donasi Bulan Ini <span class="adm-stat__ikon"><x-admin.ikon nama="kalender" :ukuran="18" /></span></div>
            <div class="adm-stat__nilai">{{ \App\Models\Donasi::rupiah($bulanIni) }}</div>
            @if ($perubahanBulan !== null)
                <div @class(['adm-stat__catatan', 'adm-stat__catatan--redup' => $perubahanBulan < 0])>{{ $perubahanBulan >= 0 ? '+' : '' }}{{ number_format($perubahanBulan, 1, ',', '.') }}% dari bulan lalu</div>
            @else
                <div class="adm-stat__catatan adm-stat__catatan--redup">Belum ada pembanding bulan lalu</div>
            @endif
        </article>
    </section>

    <form method="GET" action="{{ route('admin.donasi.index') }}" class="adm-filter--lapor adm-filter" role="search">
        <div class="adm-bidang">
            <label class="adm-label" for="cari">Cari donatur</label>
            <div class="adm-input-ikon"><x-admin.ikon nama="cari" :ukuran="18" /><input type="search" id="cari" name="cari" class="adm-input" value="{{ $filter['cari'] }}" placeholder="Nama, email, atau WhatsApp"></div>
        </div>
        <div class="adm-bidang">
            <span class="adm-label" id="label-rentang">Rentang tanggal</span>
            <div class="adm-rentang" role="group" aria-labelledby="label-rentang">
                <x-admin.ikon nama="kalender" :ukuran="18" />
                <input type="date" name="dari" value="{{ $filter['dari'] }}" aria-label="Dari tanggal">
                <span>—</span>
                <input type="date" name="sampai" value="{{ $filter['sampai'] }}" aria-label="Sampai tanggal">
            </div>
        </div>
        <div class="adm-bidang">
            <label class="adm-label" for="status">Status</label>
            <select id="status" name="status" class="adm-select">
                <option value="">Semua status</option>
                <option value="berhasil" @selected($filter['status'] === 'berhasil')>Berhasil</option>
                <option value="menunggu" @selected($filter['status'] === 'menunggu')>Menunggu</option>
            </select>
        </div>
        <div class="adm-bidang">
            <label class="adm-label" for="nominal">Nominal</label>
            <select id="nominal" name="nominal" class="adm-select">
                <option value="">Semua nominal</option>
                @foreach ($rentangNominal as $kunci => [$label])<option value="{{ $kunci }}" @selected($filter['nominal'] === $kunci)>{{ $label }}</option>@endforeach
            </select>
        </div>
        <button type="submit" class="adm-btn adm-btn--primer" style="height:46px">Terapkan</button>
    </form>

    <section class="adm-tabel-kartu">
        <div class="adm-tabel-gulir">
            <table class="adm-tabel">
                <thead><tr><th>Donatur</th><th>Email</th><th>WhatsApp</th><th>Nominal</th><th>Metode</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($daftar as $d)
                        <tr>
                            <td><strong>{{ $d->nama }}</strong></td>
                            <td>{{ $d->email }}</td>
                            <td>{{ $d->whatsapp }}</td>
                            <td><strong>{{ $d->nominal_rupiah }}</strong></td>
                            <td>{{ $d->metode }}
                                @if ($d->simulasi)<span class="adm-lencana adm-lencana--kuning" style="margin-left:4px">Simulasi</span>@endif
                                @unless ($d->sudahBerhasil())<span class="adm-lencana adm-lencana--abu" style="margin-left:4px">Menunggu</span>@endunless
                            </td>
                            <td>{{ $d->tanggal->translatedFormat('d M Y') }}</td>
                            <td class="adm-kolom-aksi">
                                <button type="button" class="adm-aksi-ikon" aria-label="Lihat detail donasi {{ $d->kode }}"
                                        data-donasi-detail
                                        data-kode="{{ $d->kode }}" data-nama="{{ $d->nama }}" data-email="{{ $d->email }}" data-whatsapp="{{ $d->whatsapp }}"
                                        data-nominal="{{ $d->nominal_rupiah }}" data-metode="{{ $d->metode }}"
                                        data-tanggal="{{ $d->tanggal->translatedFormat('d F Y, H:i') }} WIB"
                                        data-referensi="{{ $d->referensi ?? '-' }}" data-biaya="{{ \App\Models\Donasi::rupiah($d->biaya_layanan) }}"
                                        data-berhasil="{{ $d->sudahBerhasil() ? '1' : '0' }}" data-simulasi="{{ $d->simulasi ? '1' : '0' }}"
                                        data-bukti="{{ route('admin.donasi.bukti', $d) }}">
                                    <x-admin.ikon nama="mata" :ukuran="16" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="adm-kosong"><strong>Belum ada donasi</strong>Donasi dari User akan muncul di sini setelah alur donasi dilakukan.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.paginasi :data="$daftar" satuan="donasi" />
    </section>
@endsection

@push('modal')
    <div class="adm-modal-latar" id="modal-donasi" role="dialog" aria-modal="true" aria-labelledby="judul-donasi" aria-hidden="true">
        <div class="adm-modal adm-modal--sedang">
            <div class="adm-modal__kepala">
                <div><h2 id="judul-donasi">Detail Donasi</h2><p>ID: <span data-d="kode"></span></p></div>
                <button type="button" class="adm-modal__tutup" data-adm-tutup aria-label="Tutup"><x-admin.ikon nama="tutup" :ukuran="20" /></button>
            </div>
            <div class="adm-modal__isi">
                <div class="adm-donasi-total">
                    <div><small>Total donasi</small><strong data-d="nominal"></strong></div>
                    <span class="adm-lencana adm-lencana--hijau" data-d-status>Berhasil</span>
                </div>
                <div class="adm-rincian">
                    <h3>Informasi Donatur</h3>
                    <dl>
                        <div><dt>Nama</dt><dd data-d="nama"></dd></div>
                        <div><dt>Email</dt><dd data-d="email"></dd></div>
                        <div><dt>WhatsApp</dt><dd data-d="whatsapp"></dd></div>
                    </dl>
                </div>
                <hr class="adm-pemisah">
                <div class="adm-rincian">
                    <h3>Informasi Pembayaran</h3>
                    <dl>
                        <div><dt>Metode</dt><dd data-d="metode"></dd></div>
                        <div><dt>Tanggal</dt><dd data-d="tanggal"></dd></div>
                        <div><dt>Reference</dt><dd data-d="referensi"></dd></div>
                        <div><dt>Biaya layanan</dt><dd data-d="biaya"></dd></div>
                    </dl>
                </div>
                <p class="adm-info adm-info--kuning" data-d-simulasi hidden><x-admin.ikon nama="info" :ukuran="18" /> Donasi simulasi: tidak melalui payment gateway dan tidak ada uang yang dipindahkan.</p>
                <p class="adm-info adm-info--hijau" data-d-gateway hidden><x-admin.ikon nama="centang-lingkaran" :ukuran="18" /> Pembayaran terverifikasi oleh payment gateway.</p>
                <p class="adm-info" data-d-menunggu hidden><x-admin.ikon nama="info" :ukuran="18" /> Donasi ini belum dikonfirmasi dibayar.</p>
            </div>
            <div class="adm-modal__kaki">
                <a href="#" class="adm-btn adm-btn--garis" data-d-bukti><x-admin.ikon nama="unduh" :ukuran="18" /> Unduh Bukti</a>
                <button type="button" class="adm-btn adm-btn--primer" data-adm-tutup>Tutup</button>
            </div>
        </div>
    </div>
@endpush
