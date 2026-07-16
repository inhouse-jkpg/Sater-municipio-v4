/**
 * Arrow-key navigation for Municipio primary / mobile nav.
 *
 * Tab order is unchanged. Arrow keys move between items; Down opens a
 * submenu (triggering Municipio's async fetch when needed); Escape closes.
 */
(function () {
    'use strict';

    var NAV_ROOT_SELECTOR = '#main-menu, #menu-mobile, #drawer-menu, .c-nav.c-nav--depth-1';
    var ITEM_SELECTOR = '.c-nav__item';
    var LINK_SELECTOR = 'a.c-nav__link, a.c-button';
    var TOGGLE_SELECTOR = '.c-nav__toggle';
    var FOCUSABLE_SELECTOR = 'a.c-nav__link, a.c-button, button.c-nav__toggle, .c-nav__toggle';
    var FETCH_TIMEOUT_MS = 8000;

    function closestNavRoot(element) {
        if (!element || !element.closest) {
            return null;
        }

        return element.closest(NAV_ROOT_SELECTOR);
    }

    function closestItem(element) {
        return element && element.closest ? element.closest(ITEM_SELECTOR) : null;
    }

    function getParentList(item) {
        return item && item.parentElement && item.parentElement.classList.contains('c-nav')
            ? item.parentElement
            : null;
    }

    function isPreloaderNav(list) {
        return !!(list && list.classList && list.classList.contains('preloader'));
    }

    function isPreloaderItem(item) {
        if (!item || !item.classList) {
            return false;
        }

        return item.classList.contains('u-preloader')
            || Array.prototype.some.call(item.classList, function (className) {
                return className.indexOf('u-preloader') === 0;
            });
    }

    /**
     * Real submenu only. Municipio leaves a preloader <ul class="c-nav preloader">
     * as the first child after async fetch; querySelector would pick that and
     * focus would never reach the actual links.
     */
    function getDirectSubmenu(item) {
        if (!item) {
            return null;
        }

        var lists = item.querySelectorAll(':scope > ul.c-nav');
        var real = null;

        Array.prototype.forEach.call(lists, function (list) {
            if (!isPreloaderNav(list)) {
                real = list;
            }
        });

        return real;
    }

    function getToggle(item) {
        return item ? item.querySelector(':scope > .c-nav__item-wrapper ' + TOGGLE_SELECTOR) : null;
    }

    function getItemLink(item) {
        return item ? item.querySelector(':scope > .c-nav__item-wrapper ' + LINK_SELECTOR) : null;
    }

    function getSiblingItems(item) {
        var list = getParentList(item);
        if (!list) {
            return [];
        }

        return Array.prototype.slice.call(list.children).filter(function (child) {
            return child.classList
                && child.classList.contains('c-nav__item')
                && !isPreloaderItem(child);
        });
    }

    function getFocusableInItem(item) {
        if (!item) {
            return [];
        }

        var wrapper = item.querySelector(':scope > .c-nav__item-wrapper');
        if (!wrapper) {
            return [];
        }

        return Array.prototype.slice.call(wrapper.querySelectorAll(FOCUSABLE_SELECTOR)).filter(isVisible);
    }

    function isVisible(element) {
        return !!(element && (element.offsetWidth || element.offsetHeight || element.getClientRects().length));
    }

    function focusElement(element) {
        if (element && typeof element.focus === 'function') {
            element.focus();
        }
    }

    function openItem(item) {
        if (!item) {
            return;
        }

        item.classList.add('is-open');

        var toggle = getToggle(item);
        if (toggle) {
            toggle.setAttribute('aria-pressed', 'true');
            toggle.setAttribute('aria-expanded', 'true');
        }

        var list = getParentList(item);
        if (list && list.classList.contains('c-nav--horizontal')) {
            getSiblingItems(item).forEach(function (sibling) {
                if (sibling !== item) {
                    closeItem(sibling);
                }
            });
        }
    }

    function closeItem(item) {
        if (!item) {
            return;
        }

        item.classList.remove('is-open');

        var toggle = getToggle(item);
        if (toggle) {
            toggle.setAttribute('aria-pressed', 'false');
            toggle.setAttribute('aria-expanded', 'false');
        }

        item.querySelectorAll(ITEM_SELECTOR + '.is-open').forEach(closeItem);
    }

    function closeOpenAncestorsExcept(item) {
        var root = closestNavRoot(item);
        if (!root) {
            return;
        }

        root.querySelectorAll(ITEM_SELECTOR + '.is-open').forEach(function (openItemEl) {
            if (!openItemEl.contains(item) && openItemEl !== item) {
                closeItem(openItemEl);
            }
        });
    }

    function waitForSubmenu(item) {
        return new Promise(function (resolve) {
            var existing = getDirectSubmenu(item);
            if (existing) {
                resolve(existing);
                return;
            }

            var settled = false;
            var timeoutId;
            var observer;

            function finish(submenu) {
                if (settled) {
                    return;
                }

                settled = true;
                if (observer) {
                    observer.disconnect();
                }
                clearTimeout(timeoutId);
                resolve(submenu || null);
            }

            observer = new MutationObserver(function () {
                var submenu = getDirectSubmenu(item);
                if (submenu) {
                    finish(submenu);
                    return;
                }

                // Fetch finished but no real submenu (or only preloader left).
                if (item.classList.contains('has-fetched') && !item.classList.contains('is-fetching')) {
                    finish(getDirectSubmenu(item));
                }
            });

            observer.observe(item, {
                childList: true,
                attributes: true,
                attributeFilter: ['class'],
            });

            timeoutId = setTimeout(function () {
                finish(getDirectSubmenu(item));
            }, FETCH_TIMEOUT_MS);
        });
    }

    function ensureSubmenuLoaded(item) {
        var existing = getDirectSubmenu(item);
        if (existing) {
            return Promise.resolve(existing);
        }

        if (!item.classList.contains('js-async-children-data') && !item.classList.contains('has-async')) {
            return Promise.resolve(null);
        }

        if (item.classList.contains('has-fetched')) {
            return Promise.resolve(getDirectSubmenu(item));
        }

        if (item.classList.contains('is-fetching')) {
            return waitForSubmenu(item);
        }

        var asyncToggle = item.querySelector('.js-async-children') || getToggle(item);
        if (!asyncToggle) {
            return Promise.resolve(null);
        }

        asyncToggle.click();
        return waitForSubmenu(item);
    }

    function focusFirstInSubmenu(item) {
        var submenu = getDirectSubmenu(item);
        if (!submenu) {
            return false;
        }

        var childItems = submenu.querySelectorAll(':scope > ' + ITEM_SELECTOR);
        var i;
        var focusables;

        for (i = 0; i < childItems.length; i++) {
            if (isPreloaderItem(childItems[i])) {
                continue;
            }

            focusables = getFocusableInItem(childItems[i]);
            if (focusables.length) {
                focusElement(focusables[0]);
                return true;
            }
        }

        var fallback = submenu.querySelector(FOCUSABLE_SELECTOR);
        if (fallback) {
            var fallbackItem = closestItem(fallback);
            if (!fallbackItem || !isPreloaderItem(fallbackItem)) {
                focusElement(fallback);
                return true;
            }
        }

        return false;
    }

    function openAndFocusSubmenu(item) {
        return ensureSubmenuLoaded(item).then(function (submenu) {
            if (!submenu && !item.classList.contains('has-children')) {
                return;
            }

            openItem(item);
            focusFirstInSubmenu(item);
        });
    }

    function moveToSiblingItem(item, direction, options) {
        var siblings = getSiblingItems(item);
        var index = siblings.indexOf(item);
        if (index < 0 || siblings.length < 2) {
            return false;
        }

        var wrap = !options || options.wrap !== false;
        var nextIndex = index + direction;

        if (wrap) {
            nextIndex = (nextIndex + siblings.length) % siblings.length;
        } else if (nextIndex < 0 || nextIndex >= siblings.length) {
            return false;
        }

        var nextItem = siblings[nextIndex];
        var focusables = getFocusableInItem(nextItem);

        if (!focusables.length) {
            return false;
        }

        closeOpenAncestorsExcept(nextItem);
        focusElement(focusables[0]);
        return true;
    }

    function focusParentItem(item) {
        var parentList = getParentList(item);
        if (!parentList) {
            return false;
        }

        var parentItem = closestItem(parentList.parentElement);
        if (!parentItem || parentItem === item) {
            return false;
        }

        closeItem(parentItem);
        var focusables = getFocusableInItem(parentItem);
        if (focusables.length) {
            focusElement(focusables[focusables.length - 1]);
            return true;
        }

        return false;
    }

    function isHorizontalContext(item) {
        var list = getParentList(item);
        return !!(list && list.classList.contains('c-nav--horizontal'));
    }

    function isTopLevelItem(item) {
        var list = getParentList(item);
        return !!(list && list.classList.contains('c-nav--depth-1'));
    }

    function handleKeydown(event) {
        var key = event.key;
        if (
            key !== 'ArrowLeft' &&
            key !== 'ArrowRight' &&
            key !== 'ArrowUp' &&
            key !== 'ArrowDown' &&
            key !== 'Escape' &&
            key !== 'Home' &&
            key !== 'End'
        ) {
            return;
        }

        var target = event.target;
        if (!closestNavRoot(target)) {
            return;
        }

        var item = closestItem(target);
        if (!item) {
            return;
        }

        var horizontal = isHorizontalContext(item);
        var hasChildren = item.classList.contains('has-children');
        var isOpen = item.classList.contains('is-open');

        if (key === 'Escape') {
            if (isOpen) {
                event.preventDefault();
                closeItem(item);
                focusElement(getToggle(item) || getItemLink(item) || getFocusableInItem(item)[0]);
                return;
            }

            if (focusParentItem(item)) {
                event.preventDefault();
            }
            return;
        }

        if (key === 'Home' || key === 'End') {
            var siblings = getSiblingItems(item);
            if (!siblings.length) {
                return;
            }

            event.preventDefault();
            var edgeItem = key === 'Home' ? siblings[0] : siblings[siblings.length - 1];
            var edgeFocusables = getFocusableInItem(edgeItem);
            if (edgeFocusables.length) {
                focusElement(edgeFocusables[0]);
            }
            return;
        }

        if (horizontal) {
            if (key === 'ArrowLeft') {
                event.preventDefault();
                moveToSiblingItem(item, -1);
                return;
            }

            if (key === 'ArrowRight') {
                event.preventDefault();
                moveToSiblingItem(item, 1);
                return;
            }

            if (key === 'ArrowDown') {
                if (hasChildren || getDirectSubmenu(item) || item.classList.contains('has-async')) {
                    event.preventDefault();
                    openAndFocusSubmenu(item);
                }
                return;
            }

            if (key === 'ArrowUp') {
                if (isOpen) {
                    event.preventDefault();
                    closeItem(item);
                    focusElement(getToggle(item) || getItemLink(item));
                    return;
                }

                if (!isTopLevelItem(item) && focusParentItem(item)) {
                    event.preventDefault();
                }
            }
            return;
        }

        // Vertical nav (drawer / submenu).
        if (key === 'ArrowDown') {
            event.preventDefault();
            moveToSiblingItem(item, 1, { wrap: isTopLevelItem(item) });
            return;
        }

        if (key === 'ArrowUp') {
            event.preventDefault();
            if (!moveToSiblingItem(item, -1, { wrap: isTopLevelItem(item) })) {
                focusParentItem(item);
            }
            return;
        }

        if (key === 'ArrowRight') {
            if (hasChildren || getDirectSubmenu(item) || item.classList.contains('has-async')) {
                event.preventDefault();
                openAndFocusSubmenu(item);
            }
            return;
        }

        if (key === 'ArrowLeft') {
            if (isOpen) {
                event.preventDefault();
                closeItem(item);
                focusElement(getToggle(item) || getItemLink(item));
                return;
            }

            if (focusParentItem(item)) {
                event.preventDefault();
            }
        }
    }

    function bindNavRoots(root) {
        var scope = root || document;
        var roots = scope.querySelectorAll
            ? scope.querySelectorAll(NAV_ROOT_SELECTOR)
            : [];

        Array.prototype.forEach.call(roots, function (navRoot) {
            if (navRoot.getAttribute('data-sater-a11y-nav-keyboard') === 'true') {
                return;
            }

            navRoot.setAttribute('data-sater-a11y-nav-keyboard', 'true');
            navRoot.addEventListener('keydown', handleKeydown);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindNavRoots(document);
    });
}());
