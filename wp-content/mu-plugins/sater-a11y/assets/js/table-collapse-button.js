(function () {
    'use strict';

    var LABEL = 'Växla tabellvisning';

    function syncTableCollapseState(control) {
        if (!control) {
            return;
        }

        var tableRoot = control.closest('.c-table');
        if (!tableRoot) {
            return;
        }

        var isCollapsed = tableRoot.classList.contains('is-collapsed');
        control.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
    }

    function bindTableCollapseControl(control) {
        if (!control || control.dataset.saterA11yTableCollapseBound === 'true') {
            return;
        }

        control.dataset.saterA11yTableCollapseBound = 'true';
        control.setAttribute('tabindex', '0');
        control.setAttribute('role', 'button');
        control.setAttribute('aria-label', LABEL);
        control.removeAttribute('aria-hidden');
        syncTableCollapseState(control);

        control.addEventListener('click', function () {
            requestAnimationFrame(function () {
                syncTableCollapseState(control);
            });
        });

        control.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            control.click();
        });
    }

    function initTableCollapseControls(root) {
        var scope = root || document;
        scope.querySelectorAll('.c-table__collapse-button').forEach(bindTableCollapseControl);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initTableCollapseControls(document);
    });
}());
