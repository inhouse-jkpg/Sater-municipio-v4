/**
 * Keyboard / focus fixes for Cookies and Content Security Policy modal.
 *
 * Opens via MutationObserver on body/html class + panel class changes.
 * Focuses "Acceptera alla" first, then traps Tab inside the visible dialog.
 */
(function () {
    'use strict';

    var wasOpen   = false;
    var lastFocus = null;
    var lastBox   = null;

    function isOpen() {
        return document.body.classList.contains('modal-cacsp-open') ||
            document.documentElement.classList.contains('modal-cacsp-open');
    }

    function getContainer() {
        return document.querySelector('.modal-cacsp-position');
    }

    function getVisibleBox() {
        var c = getContainer();
        return c && c.querySelector('.modal-cacsp-box.modal-cacsp-box-show');
    }

    function getFocusables() {
        var box = getVisibleBox();
        if (!box) {
            return [];
        }

        return Array.prototype.filter.call(
            box.querySelectorAll('a[href], button:not([disabled])'),
            function (el) {
                if (el.hasAttribute('inert') || el.closest('[inert]')) {
                    return false;
                }
                var s = window.getComputedStyle(el);
                return s.display !== 'none' && s.visibility !== 'hidden';
            }
        );
    }

    function ringOn(el) {
        if (!el || !el.style) {
            return;
        }
        el.style.setProperty('outline', '3px solid #4d90fe', 'important');
        el.style.setProperty('outline-offset', '2px', 'important');
        el.style.setProperty('box-shadow', 'none', 'important');
    }

    function ringOff(el) {
        if (!el || !el.style) {
            return;
        }
        el.style.removeProperty('outline');
        el.style.removeProperty('outline-offset');
        el.style.removeProperty('box-shadow');
    }

    function tryFocus(el) {
        if (!el || typeof el.focus !== 'function') {
            return false;
        }
        try {
            el.focus({ preventScroll: true });
        } catch (e) {
            el.focus();
        }
        ringOn(el);
        return document.activeElement === el;
    }

    /**
     * Preferred land-on-open target:
     * 1. Acceptera alla / Accept all
     * 2. any other action button
     * 3. first settings toggle
     * 4. first focusable in the box
     */
    function getPreferredTarget() {
        var box = getVisibleBox();
        if (!box) {
            return null;
        }

        var accept = box.querySelector(
            '.modal-cacsp-btn-accept, .modal-cacsp-btn-accept-all'
        );
        if (accept) {
            return accept;
        }

        var btn = box.querySelector('.modal-cacsp-btns .modal-cacsp-btn');
        if (btn) {
            return btn;
        }

        var toggle = box.querySelector('a.modal-cacsp-toggle-switch:not(.disabled)');
        if (toggle) {
            return toggle;
        }

        var items = getFocusables();
        return items[0] || null;
    }

    function focusPreferred() {
        return tryFocus(getPreferredTarget());
    }

    function syncHiddenPanels() {
        var c = getContainer();
        if (!c) {
            return;
        }

        Array.prototype.forEach.call(
            c.querySelectorAll('.modal-cacsp-box'),
            function (b) {
                if (b.classList.contains('modal-cacsp-box-show')) {
                    b.removeAttribute('inert');
                    b.removeAttribute('aria-hidden');
                } else {
                    b.setAttribute('inert', '');
                    b.setAttribute('aria-hidden', 'true');
                }
            }
        );
    }

    function handleOpen() {
        var c = getContainer();
        if (!c) {
            return;
        }

        lastFocus = document.activeElement;

        c.removeAttribute('inert');
        c.removeAttribute('aria-hidden');
        c.setAttribute('role', 'dialog');
        c.setAttribute('aria-modal', 'true');

        var box = getVisibleBox();
        var header = box && box.querySelector('.modal-cacsp-box-header');
        if (header) {
            if (!header.id) {
                header.id = 'sater-a11y-cacsp-title';
            }
            c.setAttribute('aria-labelledby', header.id);
        }

        syncHiddenPanels();
        lastBox = box;

        // Retry after CACSP opacity transition.
        if (!focusPreferred()) {
            setTimeout(focusPreferred, 100);
            setTimeout(focusPreferred, 350);
        }
    }

    function handleClose() {
        var c = getContainer();
        if (c) {
            c.setAttribute('inert', '');
            c.setAttribute('aria-hidden', 'true');
        }
        if (lastFocus && document.contains(lastFocus)) {
            tryFocus(lastFocus);
            ringOff(lastFocus);
        }
        lastFocus = null;
        lastBox = null;
    }

    function syncState() {
        var open = isOpen();

        if (open && !wasOpen) {
            wasOpen = true;
            handleOpen();
            return;
        }

        if (!open && wasOpen) {
            wasOpen = false;
            handleClose();
            return;
        }

        if (!open) {
            return;
        }

        // Panel switch (info <-> settings) while still open.
        var box = getVisibleBox();
        syncHiddenPanels();
        if (box && box !== lastBox) {
            lastBox = box;
            focusPreferred();
        }
    }

    function onKeydown(event) {
        if (!isOpen()) {
            return;
        }

        var c = getContainer();
        if (!c) {
            return;
        }

        var items = getFocusables();
        var active = document.activeElement;
        var idx = items.indexOf(active);

        if (idx === -1) {
            event.preventDefault();
            event.stopImmediatePropagation();
            focusPreferred();
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        event.preventDefault();
        ringOff(active);

        var next = event.shiftKey
            ? (idx === 0 ? items[items.length - 1] : items[idx - 1])
            : (idx === items.length - 1 ? items[0] : items[idx + 1]);

        tryFocus(next);
    }

    function onFocusIn(event) {
        if (!isOpen()) {
            return;
        }

        var c = getContainer();
        if (!c) {
            return;
        }

        if (c.contains(event.target)) {
            ringOn(event.target);
            return;
        }

        setTimeout(function () {
            if (isOpen() && c && !c.contains(document.activeElement)) {
                focusPreferred();
            }
        }, 0);
    }

    function onFocusOut(event) {
        var c = getContainer();
        if (c && c.contains(event.target)) {
            ringOff(event.target);
        }
    }

    function init() {
        var c = getContainer();
        if (!c) {
            return;
        }

        // Start closed: keep dialog out of tab order until CACSP opens it.
        if (!isOpen()) {
            c.setAttribute('inert', '');
            c.setAttribute('aria-hidden', 'true');
        }

        syncState();

        document.addEventListener('keydown', onKeydown, true);
        document.addEventListener('focusin', onFocusIn, true);
        document.addEventListener('focusout', onFocusOut, true);

        var mo = new MutationObserver(syncState);
        mo.observe(document.body, {
            attributes: true,
            attributeFilter: ['class']
        });
        mo.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
        mo.observe(c, {
            attributes: true,
            attributeFilter: ['class'],
            subtree: true
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
