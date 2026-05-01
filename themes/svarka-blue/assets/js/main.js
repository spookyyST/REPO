(function () {
    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    }

    ready(function () {
        var mobileToggle = document.getElementById('mobile-toggle');
        var mobileClose = document.getElementById('mobile-close');
        var mobileMenu = document.getElementById('mobile-menu');
        var mobileBackdrop = document.getElementById('mobile-backdrop');
        var siteHeader = document.getElementById('site-header');
        var referenceHero = document.querySelector('.reference-hero');
        var filterChips = document.querySelectorAll('[data-filter-chip]');
        var previewMain = document.querySelector('[data-preview-main]');
        var previewThumbs = document.querySelectorAll('[data-preview-thumb]');
        var cookieBanner = document.querySelector('[data-cookie-banner]');
        var cookieAccept = document.querySelector('[data-cookie-accept]');
        var partnersViewport = document.querySelector('.reference-partners__viewport');
        var partnersTrack = document.querySelector('.reference-partners__track');
        var raySteps = document.querySelectorAll('[data-ray-step]');
        var aboutRevealItems = document.querySelectorAll('[data-about-reveal]');
        var newsBook = document.querySelector('[data-news-book]');
        var newsPrev = document.querySelector('[data-news-prev]');
        var newsNext = document.querySelector('[data-news-next]');
        var cookieKey = (window.svarkaBlue && window.svarkaBlue.cookieConsentKey) || 'svarka_blue_cookie_consent';
        var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function refreshIcons() {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        }

        function animateCount(node) {
            if (!node || node.getAttribute('data-count-done') === 'true') {
                return;
            }

            var target = parseInt(node.getAttribute('data-count-to') || '0', 10);
            var suffix = node.getAttribute('data-count-suffix') || '';

            if (!target) {
                return;
            }

            node.setAttribute('data-count-done', 'true');

            var duration = 1800;
            var startTime = 0;

            function tick(timestamp) {
                if (!startTime) {
                    startTime = timestamp;
                }

                var progress = Math.min(1, (timestamp - startTime) / duration);
                var eased = 1 - Math.pow(1 - progress, 3);
                node.textContent = Math.round(target * eased) + suffix;

                if (progress < 1) {
                    window.requestAnimationFrame(tick);
                }
            }

            window.requestAnimationFrame(tick);
        }

        function updateHeaderState() {
            if (!siteHeader || !referenceHero) {
                return;
            }

            siteHeader.classList.toggle('is-scrolled', window.scrollY > 36);
        }

        if (siteHeader && referenceHero) {
            siteHeader.classList.add('site-header--overlay');
            updateHeaderState();
            window.addEventListener('scroll', updateHeaderState, { passive: true });
            window.addEventListener('resize', updateHeaderState);
        }

        function openMenu() {
            if (!mobileMenu || !mobileToggle || !mobileBackdrop) {
                return;
            }

            mobileMenu.classList.add('is-open');
            mobileBackdrop.classList.add('is-visible');
            mobileMenu.setAttribute('aria-hidden', 'false');
            mobileToggle.setAttribute('aria-expanded', 'true');
            document.body.classList.add('has-mobile-menu');
        }

        function closeMenu() {
            if (!mobileMenu || !mobileToggle || !mobileBackdrop) {
                return;
            }

            mobileMenu.classList.remove('is-open');
            mobileBackdrop.classList.remove('is-visible');
            mobileMenu.setAttribute('aria-hidden', 'true');
            mobileToggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('has-mobile-menu');
        }

        if (mobileToggle) {
            mobileToggle.addEventListener('click', function () {
                if (mobileMenu && mobileMenu.classList.contains('is-open')) {
                    closeMenu();
                    return;
                }

                openMenu();
            });
        }

        if (mobileClose) {
            mobileClose.addEventListener('click', closeMenu);
        }

        if (mobileBackdrop) {
            mobileBackdrop.addEventListener('click', closeMenu);
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });

        filterChips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                chip.classList.toggle('is-active');
            });
        });

        previewThumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                if (!previewMain) {
                    return;
                }

                previewMain.src = thumb.getAttribute('data-preview-thumb');
            });
        });

        function hasCookieConsent() {
            try {
                return window.localStorage.getItem(cookieKey) === 'accepted';
            } catch (error) {
                return document.cookie.indexOf(cookieKey + '=accepted') !== -1;
            }
        }

        function persistCookieConsent() {
            try {
                window.localStorage.setItem(cookieKey, 'accepted');
            } catch (error) {
                document.cookie = cookieKey + '=accepted; path=/; max-age=' + (60 * 60 * 24 * 365) + '; SameSite=Lax';
            }
        }

        if (cookieBanner && !hasCookieConsent()) {
            cookieBanner.classList.add('is-visible');
            cookieBanner.setAttribute('aria-hidden', 'false');
        }

        if (cookieAccept) {
            cookieAccept.addEventListener('click', function () {
                persistCookieConsent();
                if (cookieBanner) {
                    cookieBanner.classList.remove('is-visible');
                    cookieBanner.setAttribute('aria-hidden', 'true');
                }
            });
        }

        if (partnersViewport && partnersTrack && !prefersReducedMotion) {
            var partnerOffset = 0;
            var partnerSpeed = 0;
            var partnerTargetSpeed = 0.18;
            var partnerLastTime = 0;
            var partnerLoopWidth = 0;

            function measurePartners() {
                partnerLoopWidth = Math.max(1, partnersTrack.scrollWidth / 2);
            }

            function animatePartners(timestamp) {
                if (!partnerLastTime) {
                    partnerLastTime = timestamp;
                }

                var delta = Math.min(32, timestamp - partnerLastTime);
                partnerLastTime = timestamp;
                partnerSpeed += (partnerTargetSpeed - partnerSpeed) * 0.028;
                partnerOffset = (partnerOffset + partnerSpeed * delta) % partnerLoopWidth;
                partnersTrack.style.transform = 'translate3d(' + (-partnerOffset) + 'px, 0, 0)';
                window.requestAnimationFrame(animatePartners);
            }

            measurePartners();
            partnersViewport.addEventListener('mouseenter', function () {
                partnerTargetSpeed = 0;
            });
            partnersViewport.addEventListener('mouseleave', function () {
                partnerTargetSpeed = 0.18;
            });
            window.addEventListener('resize', measurePartners);
            window.requestAnimationFrame(animatePartners);
        }

        if (raySteps.length) {
            if ('IntersectionObserver' in window && !prefersReducedMotion) {
                var rayObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        var delay = parseInt(entry.target.getAttribute('data-delay') || '0', 10);
                        var timer = parseInt(entry.target.getAttribute('data-ray-timer') || '0', 10);

                        if (entry.isIntersecting) {
                            if (timer) {
                                window.clearTimeout(timer);
                            }

                            timer = window.setTimeout(function () {
                                entry.target.classList.add('is-visible');
                            }, delay);
                            entry.target.setAttribute('data-ray-timer', String(timer));
                            return;
                        }

                        if (timer) {
                            window.clearTimeout(timer);
                        }
                        entry.target.classList.remove('is-visible');
                        entry.target.removeAttribute('data-ray-timer');
                    });
                }, { threshold: 0.25 });

                raySteps.forEach(function (step) {
                    rayObserver.observe(step);
                });
            } else {
                raySteps.forEach(function (step) {
                    step.classList.add('is-visible');
                });
            }
        }

        if (aboutRevealItems.length) {
            if ('IntersectionObserver' in window && !prefersReducedMotion) {
                var aboutObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        entry.target.classList.add('is-visible');
                        animateCount(entry.target.querySelector('[data-count-to]'));
                        aboutObserver.unobserve(entry.target);
                    });
                }, { threshold: 0.22 });

                aboutRevealItems.forEach(function (item, index) {
                    item.style.transitionDelay = (index * 140) + 'ms';
                    aboutObserver.observe(item);
                });
            } else {
                aboutRevealItems.forEach(function (item) {
                    item.classList.add('is-visible');
                    animateCount(item.querySelector('[data-count-to]'));
                });
            }
        }

        if (newsBook && window.St && typeof window.St.PageFlip === 'function') {
            var newsPageFlip = new window.St.PageFlip(newsBook, {
                width: 520,
                height: 660,
                size: 'stretch',
                minWidth: 300,
                maxWidth: 1040,
                minHeight: 440,
                maxHeight: 680,
                showCover: false,
                usePortrait: true,
                mobileScrollSupport: false,
                flippingTime: 900,
                drawShadow: true,
                maxShadowOpacity: 0.35
            });

            newsPageFlip.loadFromHTML(newsBook.querySelectorAll('.reference-news-page'));
            newsBook.classList.add('is-ready');

            if (newsPrev) {
                newsPrev.addEventListener('click', function () {
                    newsPageFlip.flipPrev();
                });
            }

            if (newsNext) {
                newsNext.addEventListener('click', function () {
                    newsPageFlip.flipNext();
                });
            }
        }

        function getCatalogSection() {
            return document.querySelector('.section--workbench');
        }

        function catalogUrlFromForm(form) {
            var url = new URL(form.getAttribute('action') || window.location.href, window.location.origin);
            var data = new FormData(form);

            url.search = '';
            data.forEach(function (value, key) {
                if (String(value).trim() !== '') {
                    url.searchParams.append(key, value);
                }
            });

            return url.toString();
        }

        var catalogRequestId = 0;

        function loadCatalog(url, pushState) {
            var currentSection = getCatalogSection();

            if (!currentSection || !window.fetch || !window.DOMParser) {
                window.location.href = url;
                return;
            }

            var requestId = ++catalogRequestId;
            currentSection.classList.add('is-loading');

            window.fetch(url, {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Catalog request failed');
                    }
                    return response.text();
                })
                .then(function (html) {
                    if (requestId !== catalogRequestId) {
                        return;
                    }

                    var parsed = new window.DOMParser().parseFromString(html, 'text/html');
                    var nextSection = parsed.querySelector('.section--workbench');

                    if (!nextSection) {
                        throw new Error('Catalog section missing');
                    }

                    currentSection.replaceWith(nextSection);

                    if (pushState) {
                        window.history.pushState({ svarkaCatalog: true }, '', url);
                    }

                    refreshIcons();
                })
                .catch(function () {
                    window.location.href = url;
                });
        }

        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (!form.matches('.catalog-filter-form, .catalog-toolbar__search, .catalog-toolbar__sort')) {
                return;
            }

            event.preventDefault();
            loadCatalog(catalogUrlFromForm(form), true);
        });

        document.addEventListener('change', function (event) {
            var control = event.target;

            if (!control.matches('.catalog-toolbar__sort select') || !control.form) {
                return;
            }

            loadCatalog(catalogUrlFromForm(control.form), true);
        });

        document.addEventListener('click', function (event) {
            var link = event.target.closest('.catalog-tabs a, .filter-panel__head a, .catalog-pagination a');

            if (!link || !getCatalogSection()) {
                return;
            }

            var url = new URL(link.href, window.location.origin);

            if (url.origin !== window.location.origin) {
                return;
            }

            event.preventDefault();
            loadCatalog(url.toString(), true);
        });

        window.addEventListener('popstate', function () {
            if (getCatalogSection()) {
                loadCatalog(window.location.href, false);
            }
        });

        refreshIcons();
    });
}());
