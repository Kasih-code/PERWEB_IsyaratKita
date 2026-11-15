/* ==========================================================================
   IsyaratKita - JavaScript frontend (Guest + dasar User)
   1. Menu navbar untuk mobile
   2. Modal login / daftar (Fase 2: form dikirim ke server - AuthController)
   3. Pilihan nominal pada formulir donasi
   Fitur khusus User (menu profil, popup, quiz, simulasi donasi) ada di user.js
   ========================================================================== */
(function () {
    'use strict';

    /* ---------- 1. Menu navbar mobile ---------- */
    var tombolMenu = document.querySelector('[data-tombol-menu]');
    var panelMenu = document.getElementById('panel-navbar');

    if (tombolMenu && panelMenu) {
        tombolMenu.addEventListener('click', function () {
            var terbuka = panelMenu.classList.toggle('terbuka');
            tombolMenu.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
        });

        // Tutup panel otomatis jika layar dibesarkan.
        window.addEventListener('resize', function () {
            if (window.innerWidth > 960) {
                panelMenu.classList.remove('terbuka');
                tombolMenu.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ---------- 2. Modal login ---------- */
    var modalLatar = document.getElementById('modal-login');
    var elemenSebelumModal = null;

    var formLogin = modalLatar ? modalLatar.querySelector('[data-form-login]') : null;

    // Mode modal: 'masuk' (Email + Kata Sandi) atau 'daftar' (Nama + Email + Kata Sandi).
    function aturModeLogin(mode) {
        if (!modalLatar || !formLogin) { return; }
        var daftar = mode === 'daftar';
        var q = function (sel) { return modalLatar.querySelector(sel); };

        formLogin.setAttribute('action', modalLatar.getAttribute(daftar ? 'data-aksi-daftar' : 'data-aksi-masuk'));
        q('[data-input-mode]').value = daftar ? 'daftar' : 'masuk';
        q('[data-teks-judul]').textContent = daftar ? 'Buat Akun Gratis' : 'Selamat Datang';
        q('[data-teks-pengantar]').textContent = daftar
            ? 'Daftar untuk memulai progres belajar BISINDO Anda.'
            : 'Masuk untuk memulai progres belajar BISINDO Anda.';
        q('[data-tombol-kirim]').textContent = daftar ? 'Daftar Sekarang' : 'Masuk Sekarang';
        q('[data-teks-alih-label]').textContent = daftar ? 'Sudah punya akun?' : 'Belum punya akun?';
        q('[data-alih-mode]').textContent = daftar ? 'Masuk' : 'Daftar Gratis';
        q('[data-input-sandi]').setAttribute('autocomplete', daftar ? 'new-password' : 'current-password');
        var bidangNama = q('[data-bidang-nama]');
        var inputNama = q('[data-input-nama]');
        if (bidangNama) { bidangNama.hidden = !daftar; }
        if (inputNama) { inputNama.disabled = !daftar; }
    }

    function bukaModalLogin(pemicu) {
        if (!modalLatar) { return; }
        elemenSebelumModal = document.activeElement;

        // Mode & tujuan setelah login mengikuti tombol yang menekannya.
        if (pemicu) {
            aturModeLogin(pemicu.getAttribute('data-mode-login') === 'daftar' ? 'daftar' : 'masuk');
            var tujuan = pemicu.getAttribute('data-tujuan-login');
            var inputKembali = modalLatar.querySelector('[data-input-kembali]');
            if (tujuan && inputKembali) { inputKembali.value = tujuan; }
        }

        modalLatar.classList.add('terbuka');
        modalLatar.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-terbuka');

        // Tutup panel menu mobile jika sedang terbuka.
        if (panelMenu) { panelMenu.classList.remove('terbuka'); }
        if (tombolMenu) { tombolMenu.setAttribute('aria-expanded', 'false'); }

        var inputPertama = modalLatar.querySelector('input:not([type=hidden]):not(:disabled)');
        if (inputPertama) { inputPertama.focus(); }
    }

    function tutupModalLogin() {
        if (!modalLatar) { return; }
        modalLatar.classList.remove('terbuka');
        modalLatar.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-terbuka');

        var catatan = modalLatar.querySelector('[data-catatan-modal]');
        if (catatan) { catatan.hidden = true; }

        if (elemenSebelumModal && typeof elemenSebelumModal.focus === 'function') {
            elemenSebelumModal.focus();
        }
    }

    if (modalLatar) {
        // Semua elemen dengan atribut data-buka-modal-login membuka modal.
        document.addEventListener('click', function (e) {
            var pemicu = e.target.closest('[data-buka-modal-login]');
            if (pemicu) {
                e.preventDefault();
                bukaModalLogin(pemicu);
            }
        });

        // Tombol X dan klik pada latar gelap menutup modal.
        modalLatar.addEventListener('click', function (e) {
            if (e.target === modalLatar || e.target.closest('[data-tutup-modal-login]')) {
                tutupModalLogin();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (!modalLatar.classList.contains('terbuka')) { return; }

            if (e.key === 'Escape') {
                tutupModalLogin();
                return;
            }

            // Menjaga fokus keyboard tetap di dalam modal.
            if (e.key === 'Tab') {
                var bisaFokus = modalLatar.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])');
                if (!bisaFokus.length) { return; }
                var pertama = bisaFokus[0];
                var terakhir = bisaFokus[bisaFokus.length - 1];
                if (e.shiftKey && document.activeElement === pertama) {
                    e.preventDefault();
                    terakhir.focus();
                } else if (!e.shiftKey && document.activeElement === terakhir) {
                    e.preventDefault();
                    pertama.focus();
                }
            }
        });

        // Form login/daftar dikirim ke server (POST /masuk atau /daftar).
        // Tidak ada preventDefault: validasi dan autentikasi dilakukan Laravel.
        var tombolAlih = modalLatar.querySelector('[data-alih-mode]');
        if (tombolAlih) {
            tombolAlih.addEventListener('click', function () {
                var modeSekarang = modalLatar.querySelector('[data-input-mode]').value;
                aturModeLogin(modeSekarang === 'daftar' ? 'masuk' : 'daftar');
            });
        }

        // "Lupa Sandi?": pemulihan kata sandi belum tersedia, tampilkan keterangan.
        var tautanLupa = modalLatar.querySelector('[data-lupa-sandi]');
        if (tautanLupa) {
            tautanLupa.addEventListener('click', function (e) {
                e.preventDefault();
                var catatan = modalLatar.querySelector('[data-catatan-modal]');
                if (catatan) { catatan.hidden = false; }
            });
        }

        // Tampilkan / sembunyikan kata sandi.
        var tombolMata = modalLatar.querySelector('[data-tombol-mata]');
        var inputSandi = modalLatar.querySelector('[data-input-sandi]');
        if (tombolMata && inputSandi) {
            tombolMata.addEventListener('click', function () {
                var tersembunyi = inputSandi.type === 'password';
                inputSandi.type = tersembunyi ? 'text' : 'password';
                tombolMata.setAttribute('aria-label', tersembunyi ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            });
        }

        // Mode awal ditentukan server (mis. kembali ke mode Daftar setelah galat validasi).
        aturModeLogin(modalLatar.getAttribute('data-mode-awal') === 'daftar' ? 'daftar' : 'masuk');

        // Halaman yang membutuhkan login (atau form dengan galat) membuka modal otomatis.
        if (modalLatar.hasAttribute('data-buka-otomatis')) {
            bukaModalLogin(null);
        }
    }

    /* ---------- 3. Pilihan nominal donasi ---------- */
    var daftarNominal = document.querySelectorAll('[data-nominal]');
    daftarNominal.forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            daftarNominal.forEach(function (t) {
                t.classList.remove('aktif');
                t.setAttribute('aria-pressed', 'false');
            });
            tombol.classList.add('aktif');
            tombol.setAttribute('aria-pressed', 'true');
        });
    });
})();
