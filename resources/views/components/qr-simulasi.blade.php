{{--
    Gambar kode QR CONTOH untuk simulasi donasi.
    BUKAN kode QRIS sungguhan dan tidak dapat dipindai/dibayar: pola dibuat
    deterministik (tiga penanda sudut + modul acak tetap) hanya sebagai ilustrasi.
--}}
@php
    $n = 25;
    $matriks = array_fill(0, $n, array_fill(0, $n, false));

    // Tiga penanda sudut 7x7 (kiri-atas, kanan-atas, kiri-bawah).
    $penanda = function (int $x0, int $y0) use (&$matriks) {
        for ($y = 0; $y < 7; $y++) {
            for ($x = 0; $x < 7; $x++) {
                $tepi = $x === 0 || $x === 6 || $y === 0 || $y === 6;
                $inti = $x >= 2 && $x <= 4 && $y >= 2 && $y <= 4;
                $matriks[$y0 + $y][$x0 + $x] = $tepi || $inti;
            }
        }
    };
    $penanda(0, 0);
    $penanda($n - 7, 0);
    $penanda(0, $n - 7);

    // Modul acak-tetap di luar area penanda + pemisahnya.
    $benih = 20261009;
    for ($y = 0; $y < $n; $y++) {
        for ($x = 0; $x < $n; $x++) {
            $dalamPenanda = ($x < 8 && $y < 8) || ($x >= $n - 8 && $y < 8) || ($x < 8 && $y >= $n - 8);
            if ($dalamPenanda) { continue; }
            $benih = ($benih * 1103515245 + 12345) % 2147483648;
            $matriks[$y][$x] = (($benih >> 16) & 3) !== 0 ? (($benih >> 8) & 1) === 1 : false;
        }
    }
@endphp

<svg {{ $attributes->class('qr-simulasi') }} viewBox="0 0 {{ $n }} {{ $n }}" xmlns="http://www.w3.org/2000/svg"
     shape-rendering="crispEdges" role="img" aria-label="Contoh kode QR untuk simulasi (bukan kode pembayaran sungguhan)">
    <rect width="{{ $n }}" height="{{ $n }}" fill="#fff"/>
    @foreach ($matriks as $y => $baris)
        @foreach ($baris as $x => $hitam)
            @if ($hitam)<rect x="{{ $x }}" y="{{ $y }}" width="1" height="1" fill="#111"/>@endif
        @endforeach
    @endforeach
</svg>
