/**
 * Säter A11y: Header hide-on-scroll in landscape
 *
 * WCAG 1.3.4 – Orientation
 * When the device is in landscape with a short viewport (phone), the sticky
 * header is hidden as the user scrolls down and revealed when they scroll up.
 * This frees the majority of the screen for content, matching the audit
 * recommendation to hide the header on scroll rather than only at the footer.
 *
 * Works alongside landscape.css which defines the .is-scrolled-away transition.
 */
(function () {
    'use strict';

    var MEDIA_QUERY  = '(orientation: landscape) and (max-height: 500px)';
    var HIDDEN_CLASS = 'is-scrolled-away';
    var THRESHOLD    = 60; // px scrolled before hiding begins

    var mq = window.matchMedia(MEDIA_QUERY);

    function getStickyHeaders() {
        return document.querySelectorAll(
            '.c-header.c-header--sticky,' +
            '#site-header-flexible-upper.c-header--sticky,' +
            '#site-header-flexible-lower.c-header--sticky'
        );
    }

    function showHeaders() {
        getStickyHeaders().forEach(function (el) {
            el.classList.remove(HIDDEN_CLASS);
        });
    }

    var lastY    = window.scrollY;
    var ticking  = false;

    function onScroll() {
        if (!mq.matches) {
            return;
        }

        var currentY = window.scrollY;
        var scrollingDown = currentY > lastY && currentY > THRESHOLD;

        getStickyHeaders().forEach(function (el) {
            el.classList.toggle(HIDDEN_CLASS, scrollingDown);
        });

        lastY = currentY;
        ticking = false;
    }

    function requestScroll() {
        if (!ticking) {
            window.requestAnimationFrame(onScroll);
            ticking = true;
        }
    }

    // When rotating back to portrait, always restore the header.
    mq.addEventListener('change', function () {
        showHeaders();
        lastY = window.scrollY;
    });

    window.addEventListener('scroll', requestScroll, { passive: true });
}());
