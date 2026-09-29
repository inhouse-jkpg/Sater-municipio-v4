(function () {
    'use strict';

    var SETUP_ATTR = 'data-sater-a11y-gallery-setup';
    var OPEN_PREFIX = 'Öppna bild';

    function getGalleryTriggerLabel(trigger) {
        var caption = trigger.getAttribute('data-caption') || '';
        var image = trigger.querySelector('img');
        var alt = image ? image.getAttribute('alt') || '' : '';
        var label = caption || alt;

        return label ? OPEN_PREFIX + ': ' + label : OPEN_PREFIX;
    }

    function hideRedundantGalleryContent(trigger) {
        trigger.querySelectorAll('img, figcaption').forEach(function (element) {
            element.setAttribute('aria-hidden', 'true');
        });
    }

    function bindGalleryTrigger(trigger) {
        if (!trigger || trigger.getAttribute(SETUP_ATTR) === 'true') {
            return;
        }

        trigger.setAttribute(SETUP_ATTR, 'true');
        trigger.setAttribute('tabindex', '0');
        trigger.setAttribute('role', 'button');
        trigger.setAttribute('aria-label', getGalleryTriggerLabel(trigger));
        hideRedundantGalleryContent(trigger);

        trigger.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            trigger.click();
        });
    }

    function initGalleryTriggers(root) {
        var scope = root || document;

        scope.querySelectorAll('.c-gallery [data-open][data-large-img]').forEach(bindGalleryTrigger);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initGalleryTriggers(document);
    });
}());
