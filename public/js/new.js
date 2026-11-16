
/*
 * IsyaratKita — Enhancement Progresif
 *
 * Fitur:
 * 1. Indikator progres scroll halaman.
 * 2. Animasi kemunculan elemen saat scroll.
 * 3. Dukungan aksesibilitas reduced motion.
 *
 * Tidak mengubah logika utama aplikasi.
 */

(function () {
    'use strict';

    // =========================================
    // 1. PENGATURAN AKSESIBILITAS
    // =========================================

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }


    // =========================================
    // 2. MEMBUAT INDIKATOR PROGRES SCROLL
    // =========================================

    var bar = document.createElement('div');

    bar.className = 'ik-progress';
    bar.setAttribute('aria-hidden', 'true');

    document.body.appendChild(bar);


    // =========================================
    // 3. MENGHITUNG PROGRES SCROLL
    // =========================================

    var waiting = false;

    function update() {
        var max = document.documentElement.scrollHeight - window.innerHeight;

        var p = max > 0
            ? Math.min(1, Math.max(0, window.scrollY / max))
            : 0;

        bar.style.transform = 'scaleX(' + p + ')';

        waiting = false;
    }


    // =========================================
    // 4. EVENT SCROLL & RESIZE
    // =========================================

    window.addEventListener('scroll', function () {
        if (!waiting) {
            waiting = true;
            window.requestAnimationFrame(update);
        }
    }, { passive: true });

    window.addEventListener('resize', update, { passive: true });

    update();


    // =========================================
    // 5. MEMERIKSA DUKUNGAN INTERSECTION OBSERVER
    // =========================================

    if (!('IntersectionObserver' in window)) {
        return;
    }


    // =========================================
    // 6. MEMILIH ELEMEN YANG DIBERI ANIMASI
    // =========================================

    var targets = document.querySelectorAll(
        '.hero__konten, ' +
        '.hero__gambar, ' +
        '.tentang__isi > *, ' +
        '.bisindo__isi > *, ' +
        '.kurikulum__kepala, ' +
        '.kurikulum__daftar > *, ' +
        '.ajakan .wadah'
    );


    // =========================================
    // 7. MEMBUAT OBSERVER ANIMASI SCROLL
    // =========================================

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('ik-revealed');

            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.08,
        rootMargin: '0px 0px 25px 0px'
    });


    // =========================================
    // 8. MENGAKTIFKAN ANIMASI PADA ELEMEN
    // =========================================

    targets.forEach(function (el) {

        // Jangan sembunyikan bagian yang sudah
        // terlihat ketika halaman pertama dimuat.

        if (el.getBoundingClientRect().top < window.innerHeight) {
            return;
        }

        el.classList.add('ik-reveal-ready');

        observer.observe(el);
    });

})();
