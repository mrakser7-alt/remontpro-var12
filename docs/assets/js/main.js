/* РемонтПро — интерактивность сайта */
(function () {
    'use strict';

    // ---------- Мобильное меню ----------
    const burger = document.getElementById('burger');
    const nav = document.getElementById('mainNav');
    const overlay = document.getElementById('navOverlay');

    function closeNav() {
        if (!nav) return;
        nav.classList.remove('is-open');
        burger?.classList.remove('is-open');
        burger?.setAttribute('aria-expanded', 'false');
        if (overlay) overlay.hidden = true;
        document.body.classList.remove('no-scroll');
    }

    function openNav() {
        if (!nav) return;
        nav.classList.add('is-open');
        burger?.classList.add('is-open');
        burger?.setAttribute('aria-expanded', 'true');
        if (overlay) overlay.hidden = false;
        document.body.classList.add('no-scroll');
    }

    if (burger && nav) {
        burger.addEventListener('click', function () {
            nav.classList.contains('is-open') ? closeNav() : openNav();
        });
        overlay?.addEventListener('click', closeNav);
        nav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeNav);
        });
        window.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeNav();
        });
    }

    // ---------- Шапка при прокрутке ----------
    const header = document.getElementById('siteHeader');
    const toTop = document.getElementById('toTop');

    function onScroll() {
        const y = window.scrollY || window.pageYOffset;
        header?.classList.toggle('is-stuck', y > 12);
        if (toTop) toTop.hidden = y < 500;
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    toTop?.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // ---------- Слайдер на главной ----------
    const slider = document.querySelector('[data-slider]');
    if (slider) {
        const slides = Array.from(slider.querySelectorAll('.hero__slide'));
        const dots = Array.from(slider.querySelectorAll('.hero__dot'));
        const prev = slider.querySelector('[data-slide-prev]');
        const next = slider.querySelector('[data-slide-next]');
        let index = 0;
        let timer = null;

        function show(i) {
            index = (i + slides.length) % slides.length;
            slides.forEach(function (slide, n) {
                slide.classList.toggle('is-active', n === index);
            });
            dots.forEach(function (dot, n) {
                dot.classList.toggle('is-active', n === index);
                dot.setAttribute('aria-selected', n === index ? 'true' : 'false');
            });
        }

        function start() {
            stop();
            timer = setInterval(function () { show(index + 1); }, 7000);
        }
        function stop() {
            if (timer) clearInterval(timer);
            timer = null;
        }

        dots.forEach(function (dot, n) {
            dot.addEventListener('click', function () { show(n); start(); });
        });
        prev?.addEventListener('click', function () { show(index - 1); start(); });
        next?.addEventListener('click', function () { show(index + 1); start(); });

        slider.addEventListener('mouseenter', stop);
        slider.addEventListener('mouseleave', start);
        document.addEventListener('visibilitychange', function () {
            document.hidden ? stop() : start();
        });

        if (slides.length > 1) {
            show(0);
            start();
        }
    }

    // ---------- Появление блоков при прокрутке ----------
    const revealEls = document.querySelectorAll('[data-reveal]');
    if (revealEls.length) {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px' });
            revealEls.forEach(function (el) { observer.observe(el); });
        } else {
            revealEls.forEach(function (el) { el.classList.add('is-visible'); });
        }
    }

    // ---------- Закрытие уведомлений ----------
    document.querySelectorAll('.flash__close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const flash = btn.closest('.flash');
            if (flash) {
                flash.style.transition = 'opacity .2s ease, transform .2s ease';
                flash.style.opacity = '0';
                flash.style.transform = 'translateY(-6px)';
                setTimeout(function () { flash.remove(); }, 220);
            }
        });
    });

    // ---------- Фильтрация карточек без перезагрузки ----------
    document.querySelectorAll('[data-filter-group]').forEach(function (group) {
        const targetSelector = group.getAttribute('data-filter-target');
        const target = targetSelector ? document.querySelector(targetSelector) : null;
        if (!target) return;
        const items = Array.from(target.querySelectorAll('[data-category]'));
        group.querySelectorAll('[data-filter]').forEach(function (chip) {
            chip.addEventListener('click', function (e) {
                e.preventDefault();
                group.querySelectorAll('[data-filter]').forEach(function (c) { c.classList.remove('is-active'); });
                chip.classList.add('is-active');
                const value = chip.getAttribute('data-filter');
                items.forEach(function (item) {
                    const match = value === 'all' || item.getAttribute('data-category') === value;
                    item.style.display = match ? '' : 'none';
                });
                const empty = target.parentElement?.querySelector('[data-filter-empty]');
                if (empty) {
                    const visible = items.some(function (item) { return item.style.display !== 'none'; });
                    empty.hidden = visible;
                }
            });
        });
    });

    // ---------- Подтверждение опасных действий ----------
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!window.confirm(el.getAttribute('data-confirm') || 'Вы уверены?')) {
                e.preventDefault();
            }
        });
    });

    // ---------- Боковое меню админки ----------
    const adminToggle = document.getElementById('adminToggle');
    const adminSide = document.getElementById('adminSide');
    if (adminToggle && adminSide) {
        adminToggle.addEventListener('click', function () {
            adminSide.classList.toggle('is-open');
        });
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 1024 &&
                adminSide.classList.contains('is-open') &&
                !adminSide.contains(e.target) &&
                !adminToggle.contains(e.target)) {
                adminSide.classList.remove('is-open');
            }
        });
    }

    // ---------- Счётчик символов в textarea ----------
    document.querySelectorAll('[data-counter]').forEach(function (area) {
        const target = document.querySelector(area.getAttribute('data-counter'));
        if (!target) return;
        const max = Number(area.getAttribute('maxlength')) || 1000;
        function update() {
            target.textContent = area.value.length + ' / ' + max;
        }
        area.addEventListener('input', update);
        update();
    });

    // ---------- Плавный скролл к якорям ----------
    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            const id = link.getAttribute('href');
            if (!id || id === '#') return;
            const el = document.querySelector(id);
            if (el) {
                e.preventDefault();
                const offset = 90;
                const top = el.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });
    });
})();
