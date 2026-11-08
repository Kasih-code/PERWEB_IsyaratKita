/* ==========================================================================
   IsyaratKita - JavaScript khusus User (Fase 2)
   1. Menu profil pada navbar
   2. Popup umum
   3. Pesan singkat (notifikasi)
   4. Quiz modul (satu pertanyaan per layar)
   5. Alur donasi menggunakan Midtrans Snap
   Semua bagian aman dijalankan di halaman Guest.
   ========================================================================== */

(function () {
    'use strict';
    var panelMenu = document.getElementById('panel-navbar');
    var tombolMenu = document.querySelector('[data-tombol-menu]');

    /* ---------- 1. Menu profil navbar ---------- */
    var profilMenu = document.querySelector('[data-profil-menu]');
    var profilTombol = profilMenu ? profilMenu.querySelector('[data-profil-tombol]') : null;
    var profilIsi = profilMenu ? profilMenu.querySelector('[data-profil-isi]') : null;

    if (tombolMenu && panelMenu) {
        tombolMenu.addEventListener('click', function () {
            panelMenu.classList.toggle('aktif');
        });
    }

    if (profilTombol && profilIsi) {
        profilTombol.addEventListener('click', function (event) {
            event.stopPropagation();
            var akanBuka = profilIsi.hasAttribute('hidden');
            profilIsi.toggleAttribute('hidden', !akanBuka);
            profilIsi.classList.toggle('aktif', akanBuka);
            profilTombol.setAttribute('aria-expanded', akanBuka ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
            if (!profilMenu.contains(event.target)) {
                profilIsi.setAttribute('hidden', '');
                profilIsi.classList.remove('aktif');
                profilTombol.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                profilIsi.setAttribute('hidden', '');
                profilIsi.classList.remove('aktif');
                profilTombol.setAttribute('aria-expanded', 'false');
            }
        });
    }

        /* ---------- 1b. Modal umum (Edit Profil) ---------- */
    function bukaModal(modal) {
        if (!modal) { return; }
        modal.classList.add('terbuka');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-terbuka');

        // Tutup menu profil dan panel menu mobile yang mungkin masih terbuka.
        if (profilIsi) {
            profilIsi.setAttribute('hidden', '');
            profilIsi.classList.remove('aktif');
        }
        if (profilTombol) { profilTombol.setAttribute('aria-expanded', 'false'); }
        if (panelMenu) { panelMenu.classList.remove('terbuka', 'aktif'); }
        if (tombolMenu) { tombolMenu.setAttribute('aria-expanded', 'false'); }

        var awal = modal.querySelector('[data-fokus-awal]') || modal.querySelector('input:not([type=hidden])');
        if (awal) { awal.focus(); }
    }

    function tutupModal(modal) {
        if (!modal) { return; }
        modal.classList.remove('terbuka');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-terbuka');
    }

    document.addEventListener('click', function (event) {
        var pemicu = event.target.closest('[data-buka-modal]');
        if (pemicu) {
            bukaModal(document.getElementById(pemicu.getAttribute('data-buka-modal')));
            return;
        }
        if (event.target.closest('[data-tutup-modal]')) {
            tutupModal(event.target.closest('.modal-latar'));
            return;
        }
        if (event.target.classList.contains('modal-latar') && event.target.hasAttribute('data-bisa-tutup-latar')) {
            tutupModal(event.target);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal-latar[data-bisa-tutup-latar].terbuka').forEach(tutupModal);
        }
    });

    // Buka otomatis jika validasi nama gagal (server menambahkan data-buka-otomatis).
    var modalProfil = document.getElementById('modal-profil');
    if (modalProfil && modalProfil.hasAttribute('data-buka-otomatis')) {
        bukaModal(modalProfil);
    }

    /* ---------- 2. Popup umum ---------- */
    function bukaPopup(popup) {
        if (!popup) return;
        popup.classList.add('aktif');
        document.body.classList.add('popup-terbuka');
    }

    function tutupPopup(popup) {
        if (!popup) return;
        popup.classList.remove('aktif');
        document.body.classList.remove('popup-terbuka');
    }

    document.querySelectorAll('[data-popup-buka]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            var namaPopup = tombol.getAttribute('data-popup-buka');
            var popup = document.querySelector('[data-popup="' + namaPopup + '"]');
            bukaPopup(popup);
        });
    });

    document.querySelectorAll('[data-popup-tutup]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            var popup = tombol.closest('[data-popup]');
            tutupPopup(popup);
        });
    });

    document.querySelectorAll('[data-popup]').forEach(function (popup) {
        popup.addEventListener('click', function (event) {
            if (event.target === popup) {
                tutupPopup(popup);
            }
        });
    });

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {
            document.querySelectorAll('[data-popup].aktif').forEach(function (popup) {
                tutupPopup(popup);
            });
        }
    });

    /* ---------- 3. Pesan singkat (notifikasi) ---------- */

    function tampilkanPesan(pesan, jenis) {
        var wadah = document.querySelector('[data-notifikasi]');

        if (!wadah) {
            wadah = document.createElement('div');
            wadah.setAttribute('data-notifikasi', '');
            wadah.className = 'notifikasi';
            document.body.appendChild(wadah);
        }

        wadah.textContent = pesan;
        wadah.classList.remove('berhasil', 'gagal', 'aktif');

        if (jenis) {
            wadah.classList.add(jenis);
        }

        setTimeout(function () {
            wadah.classList.add('aktif');
        }, 10);

        setTimeout(function () {
            wadah.classList.remove('aktif');
        }, 3500);
    }

    window.tampilkanPesan = tampilkanPesan;

    /* Notifikasi flash dari server (components/notifikasi.blade.php):
   tombol X menutup pesan, dan pesan hilang otomatis setelah 6 detik. */
    function sembunyikanNotifikasi(notif) {
        if (!notif || !notif.parentNode) return;
        var wadah = notif.parentNode;
        notif.remove();
        if (wadah.classList.contains('notifikasi-wadah') && !wadah.children.length) {
            wadah.remove();
        }
    }

    document.addEventListener('click', function (event) {
        var tombol = event.target.closest('[data-tutup-notifikasi]');
        if (tombol) {
            sembunyikanNotifikasi(tombol.closest('[data-notifikasi]'));
        }
    });

    document.querySelectorAll('.notifikasi-wadah [data-notifikasi]').forEach(function (notif) {
        setTimeout(function () { sembunyikanNotifikasi(notif); }, 6000);
    });

    /* ---------- 4. Quiz modul ---------- */

    document.querySelectorAll('[data-quiz]').forEach(function (quiz) {
        var daftarPertanyaan = Array.from(quiz.querySelectorAll('[data-pertanyaan]'));
        var nomorPertanyaan = 0;
        var jumlahBenar = 0;
        var tombolLanjut = quiz.querySelector('[data-quiz-lanjut]');
        var tombolSelesai = quiz.querySelector('[data-quiz-selesai]');
        var tampilanHasil = quiz.querySelector('[data-quiz-hasil]');
        var tampilanNomor = quiz.querySelector('[data-quiz-nomor]');
        var tampilanSkor = quiz.querySelector('[data-quiz-skor]');

        if (!daftarPertanyaan.length) return;

        function tampilkanPertanyaan() {
            daftarPertanyaan.forEach(function (pertanyaan, index) {
                pertanyaan.classList.toggle('aktif', index === nomorPertanyaan);
            });

            if (tampilanNomor) {
                tampilanNomor.textContent = (nomorPertanyaan + 1) + ' / ' + daftarPertanyaan.length;
            }

            if (tombolLanjut) {
                tombolLanjut.disabled = true;
            }

            if (tombolSelesai) {
                tombolSelesai.style.display = 'none';
            }
        }

        daftarPertanyaan.forEach(function (pertanyaan) {
            var pilihanJawaban = pertanyaan.querySelectorAll('[data-jawaban]');
            var jawabanBenar = pertanyaan.getAttribute('data-benar');

            pilihanJawaban.forEach(function (pilihan) {
                pilihan.addEventListener('click', function () {
                    pilihanJawaban.forEach(function (item) {
                        item.classList.remove('dipilih', 'benar', 'salah');
                        item.disabled = true;
                    });

                    pilihan.classList.add('dipilih');

                    var jawabanDipilih = pilihan.getAttribute('data-jawaban');

                    if (jawabanDipilih === jawabanBenar) {
                        pilihan.classList.add('benar');
                        jumlahBenar++;

                    } else {
                        pilihan.classList.add('salah');

                        pilihanJawaban.forEach(function (item) {
                            if (item.getAttribute('data-jawaban') === jawabanBenar) {
                                item.classList.add('benar');
                            }
                        });
                    }

                    if (tombolLanjut) {
                        tombolLanjut.disabled = false;
                    }

                    if (nomorPertanyaan === daftarPertanyaan.length - 1 && tombolSelesai) {
                        tombolSelesai.style.display = 'inline-flex';
                    }
                });
            });
        });

        if (tombolLanjut) {
            tombolLanjut.addEventListener('click', function () {
                if (nomorPertanyaan < daftarPertanyaan.length - 1) {
                    nomorPertanyaan++;
                    tampilkanPertanyaan();
                }
            });
        }

        if (tombolSelesai) {
            tombolSelesai.addEventListener('click', function () {
                if (tampilanHasil) {
                    tampilanHasil.style.display = 'block'
                }

                if (tampilanSkor) {
                    var nilai = Math.round((jumlahBenar / daftarPertanyaan.length) * 100);
                    tampilanSkor.textContent = nilai;
                }

                daftarPertanyaan.forEach(function (pertanyaan) {
                    pertanyaan.classList.remove('aktif');
                });

                if (tombolLanjut) {
                    tombolLanjut.style.display = 'none';
                }

                tombolSelesai.style.display = 'none';
            });
        }
        tampilkanPertanyaan();
    });

    /* ---------- 5. Donasi menggunakan Midtrans Snap ---------- */
    var formDonasi = document.querySelector('[data-form-donasi]');

    document.querySelectorAll('[data-nominal]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            var wadah = tombol.closest('.bidang');
            var inputNominal = wadah.querySelector('[data-nominal-terpilih]');

            if (inputNominal) {
                inputNominal.value = tombol.getAttribute('data-nilai');
            }

            wadah.querySelectorAll('[data-nominal]').forEach(function (item) {
                item.classList.remove('aktif');
                item.setAttribute('aria-pressed', 'false');
            });

            tombol.classList.add('aktif');
            tombol.setAttribute('aria-pressed', 'true');
        });
    });

    if (formDonasi) {
        var tombolKirimDonasi = formDonasi.querySelector('[type="submit"]');
        var statusDonasi = document.querySelector('[data-status-donasi]');
        var galatDonasi = document.querySelector('[data-galat-donasi]');
        var aksiSimpan = formDonasi.getAttribute('data-aksi-simpan');
        var aksiStatus = formDonasi.getAttribute('data-aksi-status');
        var tokenCsrf = document.querySelector('meta[name="csrf-token"]');
        tokenCsrf = tokenCsrf ? tokenCsrf.getAttribute('content') : '';

        function tampilkanStatusDonasi(pesan) {
            if (statusDonasi) {
                statusDonasi.textContent = pesan;
            }
        }

        function tampilkanGalatDonasi(pesan) {
            if (galatDonasi) {
                galatDonasi.textContent = pesan;
                galatDonasi.style.display = 'block';
            } else {
                tampilkanPesan(pesan, 'gagal');
            }
        }

        function hapusGalatDonasi() {
            if (galatDonasi) {
                galatDonasi.textContent = '';
                galatDonasi.style.display = 'none';
            }
        }

        function aturTombolDonasi(aktif) {
            if (!tombolKirimDonasi) return;
            tombolKirimDonasi.disabled = !aktif;

            if (aktif) {
                tombolKirimDonasi.removeAttribute('aria-busy');
            } else {
                tombolKirimDonasi.setAttribute('aria-busy', 'true');
            }
        }

        function ambilDataFormDonasi() {
            var data = new FormData(formDonasi);

            return {
                nama: data.get('nama'),
                email: data.get('email'),
                whatsapp: data.get('whatsapp'),
                nominal: data.get('nominal'),
                metode: data.get('metode') || 'QRIS'
            };
        }

        function periksaDataDonasi(data) {
            if (!data.nama || !data.nama.trim()) {
                throw new Error('Nama lengkap wajib diisi.');
            }

            if (!data.email || !data.email.trim()) {
                throw new Error('Email wajib diisi.');
            }

            if (!data.whatsapp || !data.whatsapp.trim()) {
                throw new Error('Nomor WhatsApp wajib diisi.');
            }

            if (!data.nominal || Number(data.nominal) <= 0) {
                throw new Error('Pilih nominal donasi terlebih dahulu.');
            }
        }

        function periksaStatusDonasi(kode, percobaan) {
            percobaan = percobaan || 1;

            if (!aksiStatus) {
                tampilkanStatusDonasi('Pembayaran sedang diproses. Silakan periksa kembali beberapa saat lagi.');
                return;
            }

            var urlStatus = aksiStatus.replace('__KODE__', encodeURIComponent(kode));

            fetch(urlStatus, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })

                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Gagal memeriksa status pembayaran.');
                    }

                    return response.json();
                })

                .then(function (hasil) {
                    if (hasil.status === 'berhasil') {
                        tampilkanStatusDonasi('Terima kasih! Donasi kamu berhasil diterima.');
                        tampilkanPesan('Pembayaran donasi berhasil.', 'berhasil');
                        formDonasi.reset();
                        aturTombolDonasi(true);
                        return;
                    }

                    if (hasil.status === 'gagal') {
                        tampilkanStatusDonasi('Pembayaran belum berhasil. Silakan coba kembali.');
                        aturTombolDonasi(true);
                        return;
                    }

                    if (percobaan < 5) {
                        tampilkanStatusDonasi('Pembayaran sedang dikonfirmasi. Mohon tunggu sebentar...');

                        setTimeout(function () {
                            periksaStatusDonasi(kode, percobaan + 1);
                        }, 2000);

                    } else {
                        tampilkanStatusDonasi('Pembayaran sedang diproses. Silakan periksa kembali beberapa saat lagi.');
                        aturTombolDonasi(true);
                    }
                })

                .catch(function (error) {
                    console.error(error);
                    tampilkanStatusDonasi('Pembayaran sedang diproses. Status belum dapat diperiksa.');
                    aturTombolDonasi(true);
                });
        }

        function bukaPembayaranMidtrans(token, kode) {
            if (!window.snap || typeof window.snap.pay !== 'function') {
                throw new Error('Midtrans Snap belum berhasil dimuat. Silakan muat ulang halaman.');
            }

            window.snap.pay(token, {

                onSuccess: function () {
                    tampilkanStatusDonasi('Pembayaran berhasil. Sedang memperbarui status donasi...');
                    periksaStatusDonasi(kode);
                },

                onPending: function () {
                    tampilkanStatusDonasi('Pembayaran belum selesai. Silakan selesaikan pembayaran sesuai petunjuk Midtrans.');
                    aturTombolDonasi(true);
                },

                onError: function () {
                    tampilkanStatusDonasi('Terjadi kesalahan saat melakukan pembayaran.');
                    tampilkanGalatDonasi('Pembayaran gagal diproses. Silakan coba kembali.');
                    aturTombolDonasi(true);
                },

                onClose: function () {
                    tampilkanStatusDonasi('Jendela pembayaran ditutup. Kamu masih bisa melanjutkan pembayaran jika transaksi belum kedaluwarsa.');
                    aturTombolDonasi(true);
                }
            });
        }

        formDonasi.addEventListener('submit', function (event) {
            event.preventDefault();

            hapusGalatDonasi();

            tampilkanStatusDonasi('');

            var dataDonasi = ambilDataFormDonasi();

            try {
                periksaDataDonasi(dataDonasi);
            } catch (error) {
                tampilkanGalatDonasi(error.message);
                return;
            }

            if (!aksiSimpan) {
                tampilkanGalatDonasi('Alamat penyimpanan donasi belum tersedia.');
                return;
            }

            aturTombolDonasi(false);
            tampilkanStatusDonasi('Menyiapkan transaksi donasi...');

            fetch(aksiSimpan, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': tokenCsrf,
                    'X-Requested-With': 'XMLHttpRequest'                },

                body: JSON.stringify(dataDonasi)
            })

                .then(function (response) {
                    return response.json().then(function (hasil) {
                        if (!response.ok) {
                            var pesan = hasil.message || 'Transaksi donasi gagal dibuat.';
                            if (hasil.errors) {
                                var daftarError = Object.values(hasil.errors).flat();
                                pesan = daftarError.join(' ');
                            }
                            throw new Error(pesan);
                        }
                        return hasil;
                    });
                })

                .then(function (hasil) {
                    if (!hasil.snap_token) {
                        throw new Error('Token pembayaran tidak ditemukan dari server.');
                    }

                    if (!hasil.kode) {
                        throw new Error('Kode transaksi tidak ditemukan dari server.');
                    }

                    tampilkanStatusDonasi('Transaksi berhasil dibuat. Membuka halaman pembayaran...');

                    bukaPembayaranMidtrans(hasil.snap_token, hasil.kode);
                })

                .catch(function (error) {
                    console.error('Kesalahan donasi:', error);
                    tampilkanGalatDonasi(error.message || 'Terjadi kesalahan. Silakan coba kembali.');
                    tampilkanStatusDonasi('');
                    aturTombolDonasi(true);

                });

        });

    }

})();