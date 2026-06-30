/**
 * Säter A11y: Accessible archive date picker (Duet Date Picker)
 *
 * Replaces native input[type=date] with Duet so the calendar scales with page
 * zoom (WCAG 1.4.4) and supports full keyboard navigation.
 */
(function () {
    'use strict';

    var SV_LOCALIZATION = {
        buttonLabel: 'Välj datum',
        placeholder: 'åååå-mm-dd',
        selectedDateMessage: 'Valt datum är',
        prevMonthLabel: 'Föregående månad',
        nextMonthLabel: 'Nästa månad',
        monthSelectLabel: 'Månad',
        yearSelectLabel: 'År',
        closeLabel: 'Stäng',
        calendarHeading: 'Välj datum',
        dayNames: [
            'söndag', 'måndag', 'tisdag', 'onsdag', 'torsdag', 'fredag', 'lördag'
        ],
        monthNames: [
            'januari', 'februari', 'mars', 'april', 'maj', 'juni',
            'juli', 'augusti', 'september', 'oktober', 'november', 'december'
        ],
        monthNamesShort: [
            'jan', 'feb', 'mar', 'apr', 'maj', 'jun',
            'jul', 'aug', 'sep', 'okt', 'nov', 'dec'
        ],
        locale: 'sv-SE'
    };

    function parseIsoDate(value) {
        if (!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return '';
        }
        return value;
    }

    function notifyFormValidation(form) {
        // Municipio Fields.checkEmpty() only inspects input/textarea/select captured
        // at init. The sync input below is transformed before that runs.
        form.dispatchEvent(new CustomEvent('formEmpty'));
    }

    function createSyncInput(sourceInput) {
        var syncInput = document.createElement('input');
        syncInput.type = 'text';
        syncInput.value = sourceInput.value || '';
        syncInput.className = 'sater-a11y-date-sync u-sr__only';
        syncInput.tabIndex = -1;
        syncInput.setAttribute('aria-hidden', 'true');
        syncInput.setAttribute('autocomplete', 'off');
        return syncInput;
    }

    function initInput(input) {
        if (input.dataset.saterA11yDuet === 'replaced') {
            return null;
        }

        var picker = document.createElement('duet-date-picker');
        var identifier = input.id || ('sater-a11y-date-' + input.name);
        var min = parseIsoDate(input.getAttribute('min'));
        var max = parseIsoDate(input.getAttribute('max'));
        var value = parseIsoDate(input.value);

        picker.identifier = identifier;
        picker.name = input.name;
        picker.direction = 'left';
        picker.firstDayOfWeek = 1;
        picker.localization = SV_LOCALIZATION;

        if (value) {
            picker.value = value;
        }
        if (min) {
            picker.min = min;
        }
        if (max) {
            picker.max = max;
        }

        var syncInput = createSyncInput(input);
        syncInput.value = value || input.value || '';

        input.removeAttribute('id');
        input.removeAttribute('name');
        input.type = 'hidden';
        input.value = '';
        input.dataset.saterA11yDuet = 'replaced';
        input.setAttribute('aria-hidden', 'true');
        input.tabIndex = -1;

        input.parentNode.insertBefore(picker, input);
        input.parentNode.insertBefore(syncInput, input);

        var form = input.closest('form');

        function syncFromPicker() {
            syncInput.value = picker.value || '';
            if (form) {
                notifyFormValidation(form);
            }
        }

        picker.addEventListener('duetChange', syncFromPicker);
        picker.addEventListener('duetClose', syncFromPicker);

        return { picker: picker, syncInput: syncInput, baseMin: min };
    }

    function linkFromTo(fromEntry, toEntry) {
        if (!fromEntry || !toEntry) {
            return;
        }

        var fromPicker = fromEntry.picker;
        var toPicker = toEntry.picker;
        var baseToMin = toEntry.baseMin || '';

        function updateToMin() {
            var fromDate = parseIsoDate(fromPicker.value);
            toPicker.min = fromDate || baseToMin || '';
        }

        fromPicker.addEventListener('duetChange', updateToMin);
        fromEntry.syncInput.addEventListener('change', updateToMin);
        updateToMin();
    }

    function initArchiveDatepickers() {
        var root = document.querySelector('.s-archive-filter');
        if (!root) {
            return;
        }

        var fromInput = root.querySelector('input[name="from"]');
        var toInput = root.querySelector('input[name="to"]');
        var fromEntry = fromInput ? initInput(fromInput) : null;
        var toEntry = toInput ? initInput(toInput) : null;

        linkFromTo(fromEntry, toEntry);

        var form = root.querySelector('form');
        if (form) {
            root.querySelectorAll('.sater-a11y-date-sync').forEach(function (syncInput) {
                if (syncInput.value) {
                    notifyFormValidation(form);
                }
            });
        }
    }

    // Run before Municipio Fields (styleguide) binds validation listeners.
    document.addEventListener('DOMContentLoaded', initArchiveDatepickers, { capture: true });
}());
