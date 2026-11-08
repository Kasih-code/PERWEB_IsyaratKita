/* ==========================================================================
   IsyaratKita - JavaScript Admin CMS (Fase 3)
   Tanpa library. Fitur: sidebar mobile, dropdown, modal, konfirmasi hapus, toast,
   unggah gambar + pratinjau, Editor Modul (blok materi), Editor Soal (opsi jawaban),
   detail donasi, kekuatan kata sandi.
   Validasi resmi tetap dilakukan server (Laravel); JS hanya membantu tampilan.
   ========================================================================== */
(function () {
    'use strict';

    var BATAS_BERKAS = 5 * 1024 * 1024;

    function qs(sel, akar) { return (akar || document).querySelector(sel); }
    function qsa(sel, akar) { return Array.prototype.slice.call((akar || document).querySelectorAll(sel)); }

    /* ---------- Modal ---------- */
    var modalAktif = null;
    var pemicuModal = null;

    function bukaModal(modal, pemicu) {
        if (!modal) { return; }
        tutupSisi();
        modalAktif = modal;
        pemicuModal = pemicu || document.activeElement;
        modal.classList.add('terbuka');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-terbuka');
        var fokus = qs('input:not([type=hidden]):not([type=file]), select, textarea, button', modal);
        if (fokus) { window.setTimeout(function () { fokus.focus(); }, 30); }
    }

    function tutupModal(modal) {
        modal = modal || modalAktif;
        if (!modal) { return; }
        modal.classList.remove('terbuka');
        modal.setAttribute('aria-hidden', 'true');
        if (!qs('.adm-modal-latar.terbuka')) { document.body.classList.remove('modal-terbuka'); }
        if (pemicuModal && pemicuModal.focus) { pemicuModal.focus(); }
        modalAktif = null;
    }

    document.addEventListener('click', function (e) {
        var buka = e.target.closest('[data-adm-buka]');
        if (buka) { e.preventDefault(); bukaModal(document.getElementById(buka.getAttribute('data-adm-buka')), buka); closeDropdown(); return; }

        var tutup = e.target.closest('[data-adm-tutup]');
        if (tutup) { tutupModal(tutup.closest('.adm-modal-latar')); return; }

        if (e.target.classList && e.target.classList.contains('adm-modal-latar')) { tutupModal(e.target); }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (modalAktif) { tutupModal(); } else { closeDropdown(); tutupSisi(); }
            return;
        }
        // Jebakan fokus sederhana di dalam modal.
        if (e.key === 'Tab' && modalAktif) {
            var daftar = qsa('a[href], button:not([disabled]), input:not([type=hidden]):not([disabled]), select, textarea', modalAktif)
                .filter(function (el) { return el.offsetParent !== null; });
            if (!daftar.length) { return; }
            var awal = daftar[0], akhir = daftar[daftar.length - 1];
            if (e.shiftKey && document.activeElement === awal) { e.preventDefault(); akhir.focus(); }
            else if (!e.shiftKey && document.activeElement === akhir) { e.preventDefault(); awal.focus(); }
        }
    });

    qsa('.adm-modal-latar.terbuka').forEach(function (m) { document.body.classList.add('modal-terbuka'); modalAktif = m; });

    /* ---------- Konfirmasi hapus (modal bersama) ---------- */
    document.addEventListener('click', function (e) {
        var tombol = e.target.closest('[data-adm-hapus]');
        if (!tombol) { return; }
        var modal = document.getElementById('modal-hapus');
        if (!modal) { return; }
        qs('[data-hapus-form]', modal).setAttribute('action', tombol.getAttribute('data-aksi'));
        qs('[data-hapus-judul]', modal).textContent = tombol.getAttribute('data-judul') || 'Hapus data?';
        qs('[data-hapus-teks]', modal).textContent = tombol.getAttribute('data-teks') || '';
        qs('[data-hapus-tombol]', modal).textContent = tombol.getAttribute('data-tombol') || 'Ya, Hapus';
        bukaModal(modal, tombol);
    });

    /* ---------- Sidebar (tablet/ponsel) ---------- */
    var sisi = document.getElementById('adm-sisi');
    function tutupSisi() { if (sisi) { sisi.classList.remove('terbuka'); } }
    document.addEventListener('click', function (e) {
        var pemicu = e.target.closest('[data-adm-sisi]');
        if (pemicu && sisi) { sisi.classList.toggle('terbuka'); e.stopPropagation(); return; }
        if (sisi && sisi.classList.contains('terbuka') && !e.target.closest('#adm-sisi')) { tutupSisi(); }
    });

    /* ---------- Dropdown ---------- */
    function closeDropdown(kecuali) {
        qsa('.adm-dropdown').forEach(function (d) {
            if (d !== kecuali && !d.hidden) {
                d.hidden = true;
                var pemicu = qs('[data-adm-dropdown="' + d.id + '"]');
                if (pemicu) { pemicu.setAttribute('aria-expanded', 'false'); }
            }
        });
    }
    document.addEventListener('click', function (e) {
        var pemicu = e.target.closest('[data-adm-dropdown]');
        if (pemicu) {
            var d = document.getElementById(pemicu.getAttribute('data-adm-dropdown'));
            var tampil = d.hidden;
            closeDropdown(d);
            d.hidden = !tampil;
            pemicu.setAttribute('aria-expanded', tampil ? 'true' : 'false');
            return;
        }
        if (!e.target.closest('.adm-dropdown')) { closeDropdown(); }
        else if (e.target.closest('.adm-dropdown [data-blok-tambah]')) { closeDropdown(); }
    });

    /* ---------- Toast ---------- */
    qsa('[data-adm-toast]').forEach(function (toast) {
        var sembunyi = function () { toast.remove(); };
        var t = qs('[data-adm-toast-tutup]', toast);
        if (t) { t.addEventListener('click', sembunyi); }
        window.setTimeout(sembunyi, 6000);
    });

    /* ---------- Lihat/sembunyikan kata sandi ---------- */
    qsa('[data-adm-lihat-sandi]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            var input = document.getElementById(tombol.getAttribute('data-adm-lihat-sandi'));
            if (!input) { return; }
            var tampil = input.type === 'password';
            input.type = tampil ? 'text' : 'password';
            tombol.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });
    });

    /* ---------- Bantuan unggah gambar ---------- */
    // Memeriksa ukuran/tipe di sisi klien (server tetap memvalidasi ulang).
    function berkasValid(input, maksByte) {
        var f = input.files && input.files[0];
        if (!f) { return true; }
        var tipeOk = /^image\/(jpeg|png|webp)$/.test(f.type);
        var ukuranOk = f.size <= (maksByte || BATAS_BERKAS);
        if (!tipeOk || !ukuranOk) {
            input.value = '';
            window.alert(!tipeOk ? 'Format gambar harus JPG, PNG, atau WebP.' : 'Ukuran gambar maksimal ' + Math.round((maksByte || BATAS_BERKAS) / 1048576) + ' MB.');
            return false;
        }
        return true;
    }

    function pratinjau(input, img, kosong, maksByte) {
        if (!berkasValid(input, maksByte)) { return false; }
        var f = input.files && input.files[0];
        if (!f) { return false; }
        if (img) {
            if (img._url) { URL.revokeObjectURL(img._url); }
            img._url = URL.createObjectURL(f);
            img.src = img._url;
            img.hidden = false;
        }
        if (kosong) { kosong.hidden = true; }
        return true;
    }

    qsa('[data-dropzone]').forEach(function (zona) {
        ['dragenter', 'dragover'].forEach(function (n) { zona.addEventListener(n, function (e) { e.preventDefault(); zona.classList.add('seret'); }); });
        ['dragleave', 'drop'].forEach(function (n) { zona.addEventListener(n, function () { zona.classList.remove('seret'); }); });
        zona.addEventListener('drop', function (e) {
            var input = qs('input[type=file]', zona);
            if (e.dataTransfer && e.dataTransfer.files.length && input) {
                e.preventDefault();
                try { input.files = e.dataTransfer.files; input.dispatchEvent(new Event('change', { bubbles: true })); } catch (err) { /* browser lama */ }
            }
        });
    });

    /* ---------- Kelola Asset ---------- */
    var formAsset = qs('[data-asset-form]');
    if (formAsset) {
        var modalAsset = document.getElementById('modal-asset');
        var inpKata = qs('#asset-kata', formAsset);
        var inpStatus = qs('#asset-status', formAsset);
        var inpBerkas = qs('[data-asset-berkas]', formAsset);
        var imgPrev = qs('[data-asset-pratinjau]', formAsset);
        var kosongPrev = qs('[data-asset-pratinjau-kosong]', formAsset);
        var namaBerkas = qs('[data-asset-nama-berkas]', formAsset);
        var inpMetode = qs('[data-asset-metode]', formAsset);
        var inpAksiForm = qs('[data-asset-aksi-form]', formAsset);

        var aturPratinjau = function (url, nama) {
            if (url) { imgPrev.src = url; imgPrev.hidden = false; kosongPrev.hidden = true; }
            else { imgPrev.removeAttribute('src'); imgPrev.hidden = true; kosongPrev.hidden = false; }
            namaBerkas.textContent = nama || 'Belum ada gambar';
        };
        var bersihkanGalat = function () {
            qsa('.adm-galat', formAsset).forEach(function (g) { g.remove(); });
            qsa('.adm-input--galat', formAsset).forEach(function (g) { g.classList.remove('adm-input--galat'); });
        };

        qs('[data-asset-tambah]').addEventListener('click', function (e) {
            bersihkanGalat();
            formAsset.setAttribute('action', formAsset.getAttribute('data-aksi-tambah'));
            inpMetode.value = 'POST';
            inpAksiForm.value = formAsset.getAttribute('data-aksi-tambah');
            inpKata.value = ''; inpStatus.value = 'aktif'; inpBerkas.value = '';
            aturPratinjau(null, null);
            bukaModal(modalAsset, e.currentTarget);
        });

        document.addEventListener('click', function (e) {
            var tombol = e.target.closest('[data-asset-edit]');
            if (!tombol) { return; }
            bersihkanGalat();
            var aksi = tombol.getAttribute('data-aksi');
            formAsset.setAttribute('action', aksi);
            inpMetode.value = 'PUT';
            inpAksiForm.value = aksi;
            inpKata.value = tombol.getAttribute('data-kata');
            inpStatus.value = tombol.getAttribute('data-status');
            inpBerkas.value = '';
            aturPratinjau(tombol.getAttribute('data-url'), tombol.getAttribute('data-berkas'));
            bukaModal(modalAsset, tombol);
        });

        inpBerkas.addEventListener('change', function () {
            if (pratinjau(inpBerkas, imgPrev, kosongPrev)) { namaBerkas.textContent = inpBerkas.files[0].name; }
            else if (!inpBerkas.files.length) { namaBerkas.textContent = imgPrev.hidden ? 'Belum ada gambar' : namaBerkas.textContent; }
        });
    }

    /* ---------- Detail donasi ---------- */
    var modalDonasi = document.getElementById('modal-donasi');
    if (modalDonasi) {
        document.addEventListener('click', function (e) {
            var t = e.target.closest('[data-donasi-detail]');
            if (!t) { return; }
            ['kode', 'nama', 'email', 'whatsapp', 'nominal', 'metode', 'tanggal', 'referensi', 'biaya'].forEach(function (k) {
                var el = qs('[data-d="' + k + '"]', modalDonasi);
                if (el) { el.textContent = t.getAttribute('data-' + k); }
            });
            var berhasil = t.getAttribute('data-berhasil') === '1';
            var simulasi = t.getAttribute('data-simulasi') === '1';
            var lencana = qs('[data-d-status]', modalDonasi);
            lencana.textContent = berhasil ? 'Berhasil' : 'Menunggu';
            lencana.className = 'adm-lencana ' + (berhasil ? 'adm-lencana--hijau' : 'adm-lencana--abu');
            qs('[data-d-simulasi]', modalDonasi).hidden = !simulasi;
            qs('[data-d-gateway]', modalDonasi).hidden = simulasi || !berhasil;
            qs('[data-d-menunggu]', modalDonasi).hidden = berhasil;
            qs('[data-d-bukti]', modalDonasi).setAttribute('href', t.getAttribute('data-bukti'));
            bukaModal(modalDonasi, t);
        });
    }

    /* ---------- Editor Modul: cover + blok materi ---------- */
    var formModul = qs('[data-editor-modul]');
    if (formModul) {
        var daftar = qs('[data-blok-daftar]', formModul);
        var kosong = qs('[data-blok-kosong]', formModul);
        var jumlah = qs('[data-blok-jumlah]', formModul);
        var penghitung = 0;

        // Cover
        var inpCover = qs('[data-cover-berkas]', formModul);
        if (inpCover) {
            inpCover.addEventListener('change', function () {
                pratinjau(inpCover, qs('[data-cover-pratinjau]', formModul), qs('[data-cover-kosong]', formModul));
            });
        }

        var semuaBlok = function () { return qsa('[data-blok]', daftar); };

        // Nama input diurutkan ulang: blok[0], blok[1], ... sesuai urutan di layar.
        var susunUlang = function () {
            var blok = semuaBlok();
            blok.forEach(function (b, i) {
                qsa('[name]', b).forEach(function (el) {
                    el.name = el.name.replace(/^blok\[[^\]]*\]/, 'blok[' + i + ']');
                });
                var naik = qs('[data-blok-aksi="naik"]', b), turun = qs('[data-blok-aksi="turun"]', b);
                if (naik) { naik.disabled = i === 0; }
                if (turun) { turun.disabled = i === blok.length - 1; }
            });
            jumlah.textContent = blok.length;
            kosong.hidden = blok.length > 0;
        };

        var buatBlok = function (tipe) {
            var cetakan = qs('template[data-blok-template="' + tipe + '"]');
            if (!cetakan) { return null; }
            var wadah = document.createElement('div');
            wadah.innerHTML = cetakan.innerHTML.replace(/__I__/g, 'n' + (penghitung++));
            return wadah.firstElementChild;
        };

        qsa('[data-blok-tambah]', formModul).forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                var blok = buatBlok(tombol.getAttribute('data-blok-tambah'));
                if (!blok) { return; }
                daftar.appendChild(blok);
                susunUlang();
                blok.scrollIntoView({ behavior: 'smooth', block: 'center' });
                var fokus = qs('input[type=text], input[type=url], textarea', blok);
                if (fokus) { fokus.focus({ preventScroll: true }); }
            });
        });

        daftar.addEventListener('click', function (e) {
            var tombol = e.target.closest('[data-blok-aksi]');
            if (!tombol) { return; }
            var blok = tombol.closest('[data-blok]');
            var aksi = tombol.getAttribute('data-blok-aksi');

            if (aksi === 'naik' && blok.previousElementSibling) { daftar.insertBefore(blok, blok.previousElementSibling); }
            if (aksi === 'turun' && blok.nextElementSibling) { daftar.insertBefore(blok.nextElementSibling, blok); }
            if (aksi === 'hapus') { blok.remove(); }
            if (aksi === 'salin') {
                var salinan = blok.cloneNode(true);
                var idAsli = qs('[data-blok-id]', blok).value;
                qs('[data-blok-id]', salinan).value = '';
                qs('[data-blok-salin]', salinan).value = idAsli; // server menyalin berkas gambar bila ada
                // Salinan tidak membawa berkas yang baru dipilih (belum tersimpan).
                var berkas = qs('[data-blok-berkas]', salinan);
                if (berkas) { berkas.value = ''; }
                if (!idAsli) {
                    var img = qs('[data-blok-gambar]', salinan), k = qs('[data-blok-gambar-kosong]', salinan);
                    if (img) { img.removeAttribute('src'); img.hidden = true; }
                    if (k) { k.hidden = false; }
                }
                blok.parentNode.insertBefore(salinan, blok.nextSibling);
            }
            susunUlang();
        });

        // Pratinjau gambar pada blok.
        daftar.addEventListener('change', function (e) {
            var inp = e.target.closest('[data-blok-berkas]');
            if (!inp) { return; }
            var blok = inp.closest('[data-blok]');
            if (pratinjau(inp, qs('[data-blok-gambar]', blok), qs('[data-blok-gambar-kosong]', blok))) {
                var label = qs('[data-blok-label-gambar]', blok);
                if (label) { label.textContent = 'Ganti Gambar'; }
            }
        });

        // Seret & lepas (drag and drop) - hanya dimulai dari ikon pegangan.
        var diseret = null;
        daftar.addEventListener('mousedown', function (e) {
            var g = e.target.closest('[data-blok-geser]');
            if (g) { g.closest('[data-blok]').setAttribute('draggable', 'true'); }
        });
        daftar.addEventListener('dragstart', function (e) {
            var blok = e.target.closest ? e.target.closest('[data-blok]') : null;
            if (!blok || blok.getAttribute('draggable') !== 'true') { e.preventDefault(); return; }
            diseret = blok;
            blok.classList.add('diseret');
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', 'blok'); } catch (err) { /* abaikan */ }
        });
        daftar.addEventListener('dragover', function (e) {
            if (!diseret) { return; }
            e.preventDefault();
            var target = e.target.closest('[data-blok]');
            qsa('.sasaran', daftar).forEach(function (b) { b.classList.remove('sasaran'); });
            if (target && target !== diseret) { target.classList.add('sasaran'); }
        });
        daftar.addEventListener('drop', function (e) {
            if (!diseret) { return; }
            e.preventDefault();
            var target = e.target.closest('[data-blok]');
            if (target && target !== diseret) {
                var kotak = target.getBoundingClientRect();
                var sesudah = e.clientY > kotak.top + kotak.height / 2;
                daftar.insertBefore(diseret, sesudah ? target.nextSibling : target);
            }
            susunUlang();
        });
        daftar.addEventListener('dragend', function () {
            if (diseret) { diseret.classList.remove('diseret'); diseret.setAttribute('draggable', 'false'); }
            qsa('.sasaran', daftar).forEach(function (b) { b.classList.remove('sasaran'); });
            diseret = null;
        });

        susunUlang();
    }

    /* ---------- Editor Soal: pilihan jawaban ---------- */
    var formSoal = qs('[data-editor-soal]');
    if (formSoal) {
        var wadahOpsi = qs('[data-opsi-daftar]', formSoal);
        var maks = parseInt(formSoal.getAttribute('data-maks'), 10) || 6;
        var selTipe = qs('[data-tipe-jawaban]', formSoal);
        var hitungOpsi = 0;

        var semuaOpsi = function () { return qsa('[data-opsi]', wadahOpsi); };

        var terapkanTipe = function () {
            var tipe = selTipe.value;
            var teks = tipe === 'teks' || tipe === 'teks_gambar';
            var gambar = tipe === 'gambar' || tipe === 'teks_gambar';
            semuaOpsi().forEach(function (o) {
                qsa('[data-bagian-teks]', o).forEach(function (el) { el.hidden = !teks; });
                qsa('[data-bagian-label-gambar]', o).forEach(function (el) { el.hidden = teks; });
                qsa('[data-bagian-gambar]', o).forEach(function (el) { el.hidden = !gambar; });
            });
        };

        var susunOpsi = function () {
            var opsi = semuaOpsi();
            var ada = false;
            opsi.forEach(function (o, i) {
                qsa('[name]', o).forEach(function (el) {
                    if (el.name === 'benar') { el.value = i; }
                    else { el.name = el.name.replace(/^pilihan\[[^\]]*\]/, 'pilihan[' + i + ']'); }
                });
                qs('[data-opsi-huruf]', o).textContent = String.fromCharCode(65 + i);
                var radio = qs('input[name="benar"]', o);
                if (radio.checked) { ada = true; }
                o.classList.toggle('adm-opsi--benar', radio.checked);
                qs('[data-opsi-hapus]', o).hidden = opsi.length <= 2;
            });
            if (!ada && opsi.length) { qs('input[name="benar"]', opsi[0]).checked = true; opsi[0].classList.add('adm-opsi--benar'); }
            var tambah = qs('[data-opsi-tambah]', formSoal);
            tambah.disabled = opsi.length >= maks;
        };

        qs('[data-opsi-tambah]', formSoal).addEventListener('click', function () {
            if (semuaOpsi().length >= maks) { return; }
            var cetakan = qs('template[data-opsi-template]');
            var wadah = document.createElement('div');
            wadah.innerHTML = cetakan.innerHTML.replace(/__I__/g, 'n' + (hitungOpsi++));
            wadahOpsi.appendChild(wadah.firstElementChild);
            terapkanTipe();
            susunOpsi();
        });

        wadahOpsi.addEventListener('click', function (e) {
            var hapus = e.target.closest('[data-opsi-hapus]');
            if (hapus && semuaOpsi().length > 2) {
                var o = hapus.closest('[data-opsi]');
                var adalahBenar = qs('input[name="benar"]', o).checked;
                o.remove();
                if (adalahBenar) { var pertama = qs('input[name="benar"]', semuaOpsi()[0]); pertama.checked = true; }
                susunOpsi();
            }
        });

        wadahOpsi.addEventListener('change', function (e) {
            if (e.target.matches('input[name="benar"]')) { susunOpsi(); return; }
            var inp = e.target.closest('[data-opsi-berkas]');
            if (inp) {
                var o = inp.closest('[data-opsi]');
                pratinjau(inp, qs('[data-opsi-gambar]', o), qs('[data-opsi-gambar-kosong]', o));
            }
        });

        selTipe.addEventListener('change', terapkanTipe);

        // Gambar pertanyaan
        var inpSoal = qs('[data-soal-berkas]', formSoal);
        if (inpSoal) {
            inpSoal.addEventListener('change', function () {
                var wadah = qs('[data-soal-gambar-wadah]', formSoal);
                if (pratinjau(inpSoal, qs('[data-soal-gambar]', formSoal), null)) { wadah.hidden = false; }
            });
        }

        terapkanTipe();
        susunOpsi();
    }

    /* ---------- Edit profil: pratinjau foto + kekuatan kata sandi ---------- */
    var inpFoto = qs('[data-foto-berkas]');
    if (inpFoto) {
        inpFoto.addEventListener('change', function () {
            if (!berkasValid(inpFoto, 2 * 1024 * 1024)) { return; }
            var img = qs('[data-foto-pratinjau]');
            var avatar = qs('[data-foto-avatar]');
            if (pratinjau(inpFoto, img, null, 2 * 1024 * 1024) && avatar) { avatar.hidden = true; }
        });
    }

    var kekuatan = qs('[data-kekuatan]');
    var inpSandi = qs('[data-sandi-baru]');
    if (kekuatan && inpSandi) {
        var label = qs('[data-kekuatan-label]', kekuatan);
        var batang = qsa('.adm-kekuatan__bar i', kekuatan);
        var hitung = function () {
            var s = inpSandi.value, skor = 0;
            if (s.length >= 8) { skor++; }
            if (/[a-z]/.test(s) && /[A-Z]/.test(s)) { skor++; }
            if (/\d/.test(s)) { skor++; }
            if (/[^A-Za-z0-9]/.test(s) || s.length >= 14) { skor++; }
            if (!s.length) { skor = 0; }
            kekuatan.setAttribute('data-tingkat', skor);
            batang.forEach(function (b, i) { b.classList.toggle('aktif', i < skor); });
            label.textContent = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][skor];
        };
        inpSandi.addEventListener('input', hitung);
        hitung();
    }
})();
