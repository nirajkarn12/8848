document.addEventListener('DOMContentLoaded', function () {
    const foamIntro = document.getElementById('foamIntro');
    if (foamIntro) {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const alreadySeen = sessionStorage.getItem('foamIntroSeen') === '1';

        const finishIntro = function () {
            foamIntro.classList.add('is-leaving');
            setTimeout(function () {
                foamIntro.classList.add('is-done');
                setTimeout(function () { foamIntro.remove(); }, 380);
            }, 280);
            try { sessionStorage.setItem('foamIntroSeen', '1'); } catch (e) {}
        };

        if (reduceMotion || alreadySeen) {
            foamIntro.remove();
        } else {
            // Hand slides in + foam bursts, then exits (~1 second total)
            setTimeout(finishIntro, 1000);
        }
    }

    const loader = document.getElementById('pageLoader');
    if (loader) {
        window.addEventListener('load', function () {
            loader.classList.add('is-hidden');
            setTimeout(function () { loader.remove(); }, 250);
        });
    }

    const header = document.querySelector('.site-header');
    if (header) {
        const toggleHeaderState = function () {
            header.classList.toggle('scrolled', window.scrollY > 18);
        };
        toggleHeaderState();
        window.addEventListener('scroll', toggleHeaderState, { passive: true });
    }

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    if (window.Swiper) {
        if (document.querySelector('.heroSwiper')) {
            new Swiper('.heroSwiper', {
                slidesPerView: 1,
                loop: true,
                autoplay: { delay: 5500, disableOnInteraction: false },
                pagination: { el: '.heroSwiper .swiper-pagination', clickable: true },
                navigation: {
                    nextEl: '.heroSwiper .swiper-button-next',
                    prevEl: '.heroSwiper .swiper-button-prev'
                },
                effect: 'fade',
                fadeEffect: { crossFade: true },
                speed: 800
            });
        }
        if (document.querySelector('.teamSwiper')) {
            new Swiper('.teamSwiper', {
                slidesPerView: 1.15,
                spaceBetween: 18,
                loop: true,
                autoplay: { delay: 4200, disableOnInteraction: false },
                pagination: { el: '.team-pagination', clickable: true },
                navigation: {
                    nextEl: '.teamSwiper .swiper-button-next',
                    prevEl: '.teamSwiper .swiper-button-prev'
                },
                breakpoints: {
                    576: { slidesPerView: 2, spaceBetween: 18 },
                    768: { slidesPerView: 3, spaceBetween: 20 },
                    992: { slidesPerView: 4, spaceBetween: 22 }
                }
            });
        }
    }

    const revealItems = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
    revealItems.forEach(function (item) {
        observer.observe(item);
    });

    document.querySelectorAll('img').forEach(function (img) {
        if (img.classList.contains('hero-slide-bg')) {
            img.setAttribute('loading', 'eager');
            return;
        }
        if (!img.hasAttribute('loading')) {
            img.setAttribute('loading', 'lazy');
        }
    });

    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        const toggleBackToTop = function () {
            backToTop.classList.toggle('visible', window.scrollY > 480);
        };
        toggleBackToTop();
        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        backToTop.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    const showToast = function (message, type) {
        let toast = document.getElementById('siteToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'siteToast';
            toast.className = 'site-toast';
            document.body.appendChild(toast);
        }
        toast.className = 'site-toast show ' + (type || 'success');
        toast.textContent = message;
        clearTimeout(toast.timer);
        toast.timer = setTimeout(function () {
            toast.classList.remove('show');
        }, 2600);
    };

    document.querySelectorAll('[data-quick-view]').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.dataset.quickView;
            fetch('ajax/quick-view.php?id=' + productId)
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    document.getElementById('quickViewBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('quickViewModal')).show();
                });
        });
    });

    document.querySelectorAll('.add-to-cart').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            fetch('ajax/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=add&product_id=' + productId + '&quantity=1'
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (data.success) {
                    const badge = document.querySelector('[data-cart-count]');
                    if (badge) {
                        badge.textContent = data.count;
                    }
                    showToast(data.message || 'Added to booking', 'success');
                } else {
                    showToast(data.message || 'Unable to add service', 'danger');
                }
            });
        });
    });

    const searchInput = document.getElementById('headerSearchInput');
    const searchResults = document.getElementById('searchResults');
    if (searchInput && searchResults) {
        let timer;
        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            const term = this.value.trim();
            if (term.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            timer = setTimeout(function () {
                fetch('ajax/search.php?q=' + encodeURIComponent(term))
                    .then(function (r) { return r.text(); })
                    .then(function (html) {
                        searchResults.innerHTML = html;
                        searchResults.style.display = 'block';
                    });
            }, 200);
        });
        document.addEventListener('click', function () {
            searchResults.style.display = 'none';
        });
        searchInput.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }
});
