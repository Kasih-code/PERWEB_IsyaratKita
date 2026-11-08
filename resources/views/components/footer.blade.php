{{-- Footer yang tampil di setiap halaman. Data tautan: DataDummy::kolomFooter() --}}
@php
    $kolomFooter = \App\Data\DataDummy::kolomFooter();
    $mediaSosial = [
        ['nama' => 'Facebook',  'ikon' => 'facebook'],
        ['nama' => 'Instagram', 'ikon' => 'instagram'],
        ['nama' => 'Twitter',   'ikon' => 'twitter'],
        ['nama' => 'YouTube',   'ikon' => 'youtube'],
    ];
@endphp

<footer class="footer">
    <div class="wadah">
        <div class="footer__atas">
            <div class="footer__merek">
                <x-logo />
                <p class="footer__deskripsi">
                    Platform e-learning interaktif Bahasa Isyarat Indonesia (BISINDO) yang didesain hangat, ramah, dan inklusif untuk masa depan Indonesia yang setara.
                </p>
                <div class="footer__sosial">
                    @foreach ($mediaSosial as $sosial)
                        <a href="#" aria-label="{{ $sosial['nama'] }}"><x-ikon :nama="$sosial['ikon']" :ukuran="16" /></a>
                    @endforeach
                </div>
            </div>

            @foreach ($kolomFooter as $judulKolom => $daftarTautan)
                <div class="footer__kolom">
                    <h2 class="footer__kolom-judul">{{ $judulKolom }}</h2>
                    <ul>
                        @foreach ($daftarTautan as $tautan)
                            <li>
                                <a href="{{ $tautan['rute'] ? route($tautan['rute']) : '#' }}">{{ $tautan['nama'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="footer__bawah">
            <span>&copy; {{ date('Y') }} IsyaratKita. Hak Cipta Dilindungi Undang-Undang.</span>
            <div class="footer__hukum">
                <a href="#">Kebijakan Privasi</a>
                <a href="#">Syarat &amp; Ketentuan</a>
            </div>
        </div>
    </div>
</footer>
