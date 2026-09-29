/**
 * Säter A11y: YouTube embed keyboard focus (WCAG 2.1.2)
 *
 * YouTube iframes expose many focusable controls (including endscreen links)
 * that can trap keyboard focus inside the player. Remove the iframe from tab
 * order and provide skip / continue links so users can reach the rest of the page.
 */
(function () {
    'use strict';

    var SETUP_ATTR = 'data-sater-a11y-youtube-setup';
    var EXIT_SETUP_ATTR = 'data-sater-a11y-youtube-exit-setup';
    var SKIP_CLASS = 'sater-a11y-youtube-skip';
    var EXIT_CLASS = 'sater-a11y-youtube-exit';
    var TARGET_CLASS = 'sater-a11y-youtube-target';
    var MODAL_SETUP_ATTR = 'data-sater-a11y-youtube-modal-setup';

    function isYouTubeSrc(src) {
        return /youtube\.com|youtu\.be/i.test(src || '');
    }

    function isYouTubeIframe(iframe) {
        var src = iframe.getAttribute('src') || iframe.getAttribute('data-src') || '';

        return isYouTubeSrc(src);
    }

    function getYouTubeIframe(container) {
        var iframe = container.querySelector('.c-acceptance__content iframe');

        if (iframe) {
            return iframe;
        }

        var template = container.querySelector('template');

        if (template && template.content) {
            iframe = template.content.querySelector('iframe');

            if (iframe) {
                return iframe;
            }
        }

        return container.querySelector('iframe');
    }

    function isYouTubeContainer(container) {
        var iframe = getYouTubeIframe(container);

        if (iframe && isYouTubeIframe(iframe)) {
            return true;
        }

        return isYouTubeSrc(container.getAttribute('data-src') || '');
    }

    function getYouTubeWatchUrl(embedSrc) {
        try {
            var url = new URL(embedSrc, window.location.href);
            var id = '';
            var pathMatch = url.pathname.match(/\/embed\/([^/?]+)/);

            if (pathMatch) {
                id = pathMatch[1];
            } else {
                id = url.searchParams.get('v') || '';
            }

            if (id) {
                return 'https://www.youtube.com/watch?v=' + encodeURIComponent(id);
            }
        } catch (error) {
            return embedSrc;
        }

        return embedSrc;
    }

    function getEmbedSrc(container, iframe) {
        if (iframe) {
            var src = iframe.getAttribute('src');

            if (src) {
                return src;
            }
        }

        var dataSrc = container.getAttribute('data-src');

        if (!dataSrc) {
            return '';
        }

        try {
            var urls = JSON.parse(dataSrc);

            if (Array.isArray(urls) && urls[0]) {
                return urls[0];
            }
        } catch (error) {
            return dataSrc;
        }

        return '';
    }

    function focusTarget(target) {
        if (!target) {
            return;
        }

        target.focus({ preventScroll: false });

        if (document.activeElement !== target) {
            target.setAttribute('tabindex', '0');
            target.focus({ preventScroll: false });
            target.setAttribute('tabindex', '-1');
        }
    }

    function bindSkipLink(link, target) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            focusTarget(target);
        });
    }

    function getAcceptanceModal(container) {
        return container.querySelector('dialog');
    }

    function getPreferredModalFocusTarget(dialog) {
        if (!dialog) {
            return null;
        }

        return dialog.querySelector('[data-close]')
            || dialog.querySelector('.c-modal__content a[href]')
            || dialog.querySelector('a[href], button:not([disabled])');
    }

    function focusAcceptanceModal(dialog) {
        var target = getPreferredModalFocusTarget(dialog);

        if (!target) {
            return;
        }

        requestAnimationFrame(function () {
            focusTarget(target);
        });
    }

    function ensureModalFocus(container) {
        var modal = getAcceptanceModal(container);

        if (!modal || container.getAttribute(MODAL_SETUP_ATTR) === 'true') {
            return;
        }

        container.setAttribute(MODAL_SETUP_ATTR, 'true');

        modal.addEventListener('close', function () {
            modal.dataset.saterA11yWasOpen = 'false';
        });

        var modalObserver = new MutationObserver(function () {
            if (!modal.hasAttribute('open')) {
                modal.dataset.saterA11yWasOpen = 'false';
                return;
            }

            if (modal.dataset.saterA11yWasOpen === 'true') {
                return;
            }

            modal.dataset.saterA11yWasOpen = 'true';
            focusAcceptanceModal(modal);
        });

        modalObserver.observe(modal, {
            attributes: true,
            attributeFilter: ['open']
        });
    }

    function ensureSkipNavigation(container) {
        var existingTarget = container.nextElementSibling;

        if (container.getAttribute(SETUP_ATTR) === 'true'
            && existingTarget
            && existingTarget.classList.contains(TARGET_CLASS)
        ) {
            return existingTarget;
        }

        container.setAttribute(SETUP_ATTR, 'true');

        var uid = container.id || ('yt-' + Math.random().toString(36).slice(2, 9));
        var targetId = 'sater-a11y-after-' + uid.replace(/[^a-zA-Z0-9_-]/g, '-');

        if (existingTarget && existingTarget.classList.contains(TARGET_CLASS)) {
            if (!existingTarget.id) {
                existingTarget.id = targetId;
            }

            return existingTarget;
        }

        var target = document.createElement('span');
        target.id = targetId;
        target.className = TARGET_CLASS;
        target.setAttribute('tabindex', '-1');
        container.insertAdjacentElement('afterend', target);

        if (!container.previousElementSibling
            || !container.previousElementSibling.classList.contains(SKIP_CLASS)
        ) {
            var skip = document.createElement('a');
            skip.className = SKIP_CLASS + ' screen-reader-text';
            skip.href = '#' + targetId;
            skip.textContent = 'Hoppa förbi videon';
            bindSkipLink(skip, target);
            container.insertAdjacentElement('beforebegin', skip);
        }

        return target;
    }

    function ensureExitLinks(container, iframe) {
        if (container.getAttribute(EXIT_SETUP_ATTR) === 'true') {
            return;
        }

        var content = container.querySelector('.c-acceptance__content');

        if (!content || !content.contains(iframe)) {
            return;
        }

        container.setAttribute(EXIT_SETUP_ATTR, 'true');
        iframe.setAttribute('tabindex', '-1');

        var target = container.nextElementSibling;

        if (!target || !target.classList.contains(TARGET_CLASS)) {
            target = ensureSkipNavigation(container);
        }

        if (content.querySelector('.' + EXIT_CLASS)) {
            return;
        }

        var exit = document.createElement('p');
        exit.className = EXIT_CLASS;

        var continueLink = document.createElement('a');
        continueLink.href = '#' + target.id;
        continueLink.textContent = 'Fortsätt läsa sidan efter videon';
        bindSkipLink(continueLink, target);

        var watchLink = document.createElement('a');
        watchLink.href = getYouTubeWatchUrl(getEmbedSrc(container, iframe));
        watchLink.target = '_blank';
        watchLink.rel = 'noopener noreferrer';
        watchLink.textContent = 'Se videon på YouTube (öppnas i nytt fönster)';

        exit.appendChild(continueLink);
        exit.appendChild(document.createTextNode(' \u00b7 '));
        exit.appendChild(watchLink);

        content.appendChild(exit);
    }

    function setupContainer(container) {
        if (!isYouTubeContainer(container)) {
            return;
        }

        ensureSkipNavigation(container);
        ensureModalFocus(container);

        var iframe = container.querySelector('.c-acceptance__content iframe');

        if (iframe && isYouTubeIframe(iframe)) {
            ensureExitLinks(container, iframe);
        }
    }

    function init() {
        document.querySelectorAll('.c-acceptance--video').forEach(setupContainer);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    var observer = new MutationObserver(init);
    observer.observe(document.body, { childList: true, subtree: true });
}());
