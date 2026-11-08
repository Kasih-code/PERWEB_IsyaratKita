<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bukti Donasi {{ $donasi->kode }}</title>
    <style>
        body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif; color: #263842; max-width: 560px; margin: 32px auto; padding: 0 20px; }
        h1 { font-size: 20px; margin: 0 0 4px; } .sub { color: #67747b; font-size: 13px; margin-bottom: 20px; }
        .total { background: #d6ebf3; border-radius: 12px; padding: 16px 18px; margin-bottom: 20px; }
        .total small { display: block; font-size: 11px; letter-spacing: .04em; text-transform: uppercase; color: #386a80; font-weight: 700; }
        .total strong { font-size: 28px; }
        dl { margin: 0 0 18px; } dl div { display: flex; justify-content: space-between; gap: 16px; padding: 6px 0; font-size: 14px; border-bottom: 1px solid #eef4f6; }
        dt { color: #67747b; } dd { margin: 0; font-weight: 700; text-align: right; }
        .catatan { padding: 12px 14px; border-radius: 8px; font-size: 13px; background: #fff1cc; color: #8a6100; }
        .catatan--ok { background: #e1f3e6; color: #2f7d4b; }
    </style>
</head>
<body>
    <h1>Bukti Donasi IsyaratKita</h1>
    <p class="sub">ID: {{ $donasi->kode }}</p>
    <div class="total"><small>Total donasi</small><strong>{{ $donasi->nominal_rupiah }}</strong></div>
    <dl>
        <div><dt>Nama</dt><dd>{{ $donasi->nama }}</dd></div>
        <div><dt>Email</dt><dd>{{ $donasi->email }}</dd></div>
        <div><dt>WhatsApp</dt><dd>{{ $donasi->whatsapp }}</dd></div>
        <div><dt>Metode</dt><dd>{{ $donasi->metode }}</dd></div>
        <div><dt>Status</dt><dd>{{ $donasi->sudahBerhasil() ? 'Berhasil' : 'Menunggu pembayaran' }}</dd></div>
        <div><dt>Tanggal</dt><dd>{{ $donasi->tanggal->translatedFormat('d F Y, H:i') }} WIB</dd></div>
        <div><dt>Reference</dt><dd>{{ $donasi->referensi ?? '-' }}</dd></div>
        <div><dt>Biaya layanan</dt><dd>{{ \App\Models\Donasi::rupiah($donasi->biaya_layanan) }}</dd></div>
    </dl>
    @if ($donasi->simulasi)
        <p class="catatan"><strong>SIMULASI.</strong> Donasi ini dibuat melalui alur simulasi QRIS tanpa payment gateway. Tidak ada uang yang dipindahkan dan dokumen ini bukan bukti pembayaran resmi.</p>
    @else
        <p class="catatan catatan--ok">Pembayaran terverifikasi oleh payment gateway.</p>
    @endif
</body>
</html>
