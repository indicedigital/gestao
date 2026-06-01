(function (global) {
    'use strict';

    var STORAGE_KEY = 'app-theme';

    function getTheme() {
        return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function setTheme(theme) {
        var next = theme === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', next);
        document.documentElement.setAttribute('data-bs-theme', next);
        localStorage.setItem(STORAGE_KEY, next);
        global.dispatchEvent(new CustomEvent('app-theme-change', { detail: { theme: next } }));
        syncToggleIcons();
    }

    function toggleTheme() {
        setTheme(getTheme() === 'dark' ? 'light' : 'dark');
    }

    function syncToggleIcons() {
        var dark = getTheme() === 'dark';
        document.querySelectorAll('[data-theme-icon]').forEach(function (icon) {
            icon.className = dark ? 'fas fa-sun' : 'fas fa-moon';
        });
    }

    function initThemeToggle(selector) {
        document.querySelectorAll(selector).forEach(function (btn) {
            btn.addEventListener('click', toggleTheme);
        });
        syncToggleIcons();
    }

    function chartColors() {
        var dark = getTheme() === 'dark';
        return {
            text: dark ? '#8b949e' : '#64748b',
            textStrong: dark ? '#e6edf3' : '#1a202c',
            grid: dark ? 'rgba(48, 54, 61, 0.8)' : 'rgba(226, 232, 240, 0.8)',
            surface: dark ? '#161b22' : '#ffffff',
            primary: dark ? '#7c6cf0' : '#5e72e4',
            success: dark ? '#3dd68c' : '#2dce89',
            danger: dark ? '#ff6b8a' : '#f5365c',
            warning: dark ? '#ff8a65' : '#fb6340',
            info: dark ? '#4dd4f7' : '#11cdef',
        };
    }

    function swalDefaults() {
        var c = chartColors();
        return {
            background: c.surface,
            color: c.textStrong,
            confirmButtonColor: c.primary,
            cancelButtonColor: darkMuted(),
        };
    }

    function darkMuted() {
        return getTheme() === 'dark' ? '#484f58' : '#6c757d';
    }

    global.AppTheme = {
        getTheme: getTheme,
        setTheme: setTheme,
        toggleTheme: toggleTheme,
        initThemeToggle: initThemeToggle,
        chartColors: chartColors,
        swalDefaults: swalDefaults,
    };
})(window);
