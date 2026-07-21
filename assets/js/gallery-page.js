(function ($) {
    'use strict';

    function revealTiles($scope) {
        var $tiles = ($scope && $scope.length ? $scope : $('#galleryMosaic .gallery-tile')).filter(':visible');
        if (!$tiles.length) {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            $tiles.addClass('is-in');
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    $(entry.target).addClass('is-in');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        $tiles.each(function () {
            observer.observe(this);
        });
    }

    function bindParallaxTilt() {
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion) {
            return;
        }

        $('#galleryMosaic').on('mousemove', '.gallery-tile', function (e) {
            var $tile = $(this);
            var rect = this.getBoundingClientRect();
            var x = (e.clientX - rect.left) / rect.width;
            var y = (e.clientY - rect.top) / rect.height;
            var tiltX = ((0.5 - y) * 8).toFixed(2);
            var tiltY = ((x - 0.5) * 8).toFixed(2);
            $tile.css('transform', 'perspective(900px) rotateX(' + tiltX + 'deg) rotateY(' + tiltY + 'deg) translateY(-4px) scale(1.02)');
            $tile.find('img').css('transform', 'scale(1.08) translate(' + ((x - 0.5) * 8) + 'px, ' + ((y - 0.5) * 8) + 'px)');
        }).on('mouseleave', '.gallery-tile', function () {
            $(this).css('transform', '');
            $(this).find('img').css('transform', '');
        });
    }

    function initFancybox() {
        if (!$.fancybox) {
            return;
        }

        $('[data-fancybox^="gallery-cat-"]').fancybox({
            buttons: ['slideShow', 'fullScreen', 'thumbs', 'zoom', 'close'],
            loop: true,
            protect: true,
            animationEffect: 'zoom-in-out',
            transitionEffect: 'slide',
            idleTime: 4,
            thumbs: { autoStart: false },
            caption: function () {
                return $(this).data('caption') || '';
            }
        });
    }

    function pulseCount() {
        var $count = $('[data-gallery-count]');
        if (!$count.length) {
            return;
        }
        $count.addClass('is-pulse');
        setTimeout(function () {
            $count.removeClass('is-pulse');
        }, 900);
    }

    function updateVisibleCount() {
        var visible = $('#galleryMosaic .gallery-tile:visible').length;
        $('[data-gallery-count]').text(visible);
        pulseCount();
        $('#galleryEmptyFilter').toggleClass('d-none', visible > 0);
    }

    function bindCategoryFilter() {
        $('#galleryFilterBar').on('click', '.gallery-filter-btn', function () {
            var $btn = $(this);
            var filter = String($btn.data('filter'));
            $btn.addClass('is-active').siblings().removeClass('is-active');

            var $tiles = $('#galleryMosaic .gallery-tile');
            $tiles.removeClass('is-in').css('transform', '');

            if (filter === 'all') {
                $tiles.stop(true, true).fadeIn(220);
            } else {
                $tiles.each(function () {
                    var $tile = $(this);
                    var cat = String($tile.data('category'));
                    if (cat === filter) {
                        $tile.stop(true, true).fadeIn(220);
                    } else {
                        $tile.stop(true, true).fadeOut(180);
                    }
                });
            }

            setTimeout(function () {
                revealTiles($('#galleryMosaic .gallery-tile:visible'));
                updateVisibleCount();
            }, 240);
        });
    }

    $(function () {
        revealTiles();
        bindParallaxTilt();
        initFancybox();
        bindCategoryFilter();
        pulseCount();
        $('.gallery-page-head').addClass('is-ready');
    });
})(jQuery);
