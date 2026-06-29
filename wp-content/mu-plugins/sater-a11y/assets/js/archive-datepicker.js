/**
 * Säter A11y: Zoom-friendly archive date picker
 *
 * Replaces native input[type=date] with jQuery UI Datepicker so the calendar
 * scales with page zoom (WCAG 1.4.4).
 */
(function ($) {
    'use strict';

    if (!$.datepicker) {
        return;
    }

    $.datepicker.regional.sv = {
        closeText: 'Stäng',
        prevText: 'Förra',
        nextText: 'Nästa',
        currentText: 'Idag',
        monthNames: [
            'januari', 'februari', 'mars', 'april', 'maj', 'juni',
            'juli', 'augusti', 'september', 'oktober', 'november', 'december'
        ],
        monthNamesShort: [
            'jan', 'feb', 'mar', 'apr', 'maj', 'jun',
            'jul', 'aug', 'sep', 'okt', 'nov', 'dec'
        ],
        dayNamesShort: ['sön', 'mån', 'tis', 'ons', 'tor', 'fre', 'lör'],
        dayNames: [
            'söndag', 'måndag', 'tisdag', 'onsdag', 'torsdag', 'fredag', 'lördag'
        ],
        dayNamesMin: ['sö', 'må', 'ti', 'on', 'to', 'fr', 'lö'],
        weekHeader: 'Ve',
        dateFormat: 'yy-mm-dd',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };

    $.datepicker.setDefaults($.datepicker.regional.sv);

    function parseIsoDate(value) {
        if (!value) {
            return null;
        }
        try {
            return $.datepicker.parseDate('yy-mm-dd', value);
        } catch (e) {
            return null;
        }
    }

    function buildOptions($input) {
        var min = parseIsoDate($input.attr('min'));
        var max = parseIsoDate($input.attr('max'));

        return {
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: '-100:+10',
            showOtherMonths: true,
            selectOtherMonths: true,
            minDate: min,
            maxDate: max,
            beforeShow: function (input, inst) {
                inst.dpDiv.addClass('sater-a11y-datepicker');
            }
        };
    }

    function initInput($input) {
        if ($input.data('saterA11yDatepicker')) {
            return $input;
        }

        var value = $input.val();
        var placeholder = $input.attr('placeholder') || 'yyyy-mm-dd';

        $input.attr({
            type: 'text',
            autocomplete: 'off',
            inputmode: 'numeric',
            placeholder: placeholder
        });

        $input.datepicker(buildOptions($input));

        if (value) {
            var parsed = parseIsoDate(value);
            if (parsed) {
                $input.datepicker('setDate', parsed);
            }
        }

        $input.data('saterA11yDatepicker', true);
        return $input;
    }

    function linkFromTo($from, $to) {
        if (!$from.length || !$to.length) {
            return;
        }

        function updateToMin(dateText) {
            var fromDate = parseIsoDate(dateText || $from.val());
            $to.datepicker('option', 'minDate', fromDate);
        }

        $from.on('change', function () {
            updateToMin($from.val());
        });

        $from.datepicker('option', 'onSelect', function (dateText) {
            updateToMin(dateText);
        });

        updateToMin($from.val());
    }

    function initArchiveDatepickers() {
        var $root = $('.s-archive-filter');
        if (!$root.length) {
            return;
        }

        var $from = initInput($root.find('input[type="date"][name="from"]'));
        var $to = initInput($root.find('input[type="date"][name="to"]'));

        linkFromTo($from, $to);
    }

    $(initArchiveDatepickers);
}(jQuery));
