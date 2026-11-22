/* ------------------------------------------------------------
   THE JOURNEY ARCHIVE — pre-paint boot
   Applies the saved palette and starts the loader failsafe before
   the first paint. Kept external so the PHP pages can use a strict
   Content-Security-Policy without unsafe-inline scripts.
   ------------------------------------------------------------ */
(function () {
    'use strict';

    document.documentElement.classList.add('js');

    try {
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    } catch (error) {
        /* Storage may be unavailable — keep the default dark palette. */
    }

    /* Never leave the loading screen visible if script.js cannot load. */
    window.setTimeout(function () {
        var loader = document.getElementById('loader');
        if (loader) loader.classList.add('is-hidden');
        if (document.body) document.body.classList.add('is-loaded');
    }, 2200);
})();
