(function () {
    'use strict';

    let loadingCount = 0;
    let navTimer = null;

    function overlay() {
        return document.getElementById('app-loading-overlay');
    }

    function progressBar() {
        return document.getElementById('app-loading-progress');
    }

    window.AppLoading = {
        show(message) {
            clearTimeout(navTimer);
            navTimer = null;
            const el = overlay();
            if (!el) return;
            loadingCount++;
            const text = el.querySelector('.app-loading-text');
            if (text && message) text.textContent = message;
            el.hidden = false;
            el.setAttribute('aria-busy', 'true');
            const bar = progressBar();
            if (bar) {
                bar.classList.add('is-active');
                bar.style.width = '30%';
            }
        },
        hide() {
            clearTimeout(navTimer);
            navTimer = null;
            const el = overlay();
            if (!el) return;
            loadingCount = Math.max(0, loadingCount - 1);
            if (loadingCount === 0) {
                el.hidden = true;
                el.setAttribute('aria-busy', 'false');
                const bar = progressBar();
                if (bar) {
                    bar.style.width = '100%';
                    setTimeout(() => {
                        bar.classList.remove('is-active');
                        bar.style.width = '0%';
                    }, 200);
                }
            }
        },
        forceHide() {
            clearTimeout(navTimer);
            navTimer = null;
            loadingCount = 0;
            const el = overlay();
            if (el) {
                el.hidden = true;
                el.setAttribute('aria-busy', 'false');
            }
            const bar = progressBar();
            if (bar) {
                bar.classList.remove('is-active');
                bar.style.width = '0%';
            }
        },
    };

    function shouldShowNavLoading(anchor) {
        if (!anchor || anchor.target === '_blank' || anchor.hasAttribute('download')) return false;
        if (anchor.dataset.noLoading !== undefined) return false;
        if (anchor.classList.contains('project-tab-link')) return false;
        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return false;
        try {
            const url = new URL(href, window.location.origin);
            return url.origin === window.location.origin;
        } catch (_) {
            return false;
        }
    }

    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!shouldShowNavLoading(link)) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        clearTimeout(navTimer);
        navTimer = setTimeout(() => AppLoading.show('Carregando...'), 120);
    }, true);

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.noLoading !== undefined) return;
        if (form.dataset.ajax !== undefined) return;
        AppLoading.show('Processando...');
    }, true);

    window.addEventListener('pageshow', function (e) {
        clearTimeout(navTimer);
        AppLoading.forceHide();
    });

    document.addEventListener('DOMContentLoaded', function () {
        AppLoading.forceHide();
    });
})();
