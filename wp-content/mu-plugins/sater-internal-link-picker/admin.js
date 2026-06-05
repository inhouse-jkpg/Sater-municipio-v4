(function ($) {
    if (typeof acf === 'undefined' || typeof acf.addFilter !== 'function') {
        return;
    }

    var groupOrder = (window.saterInternalLinkPicker && window.saterInternalLinkPicker.groupOrder) || [];

    function groupPriority(label) {
        var index = groupOrder.indexOf(label);
        return index === -1 ? 999 : index;
    }

    function reorderGroupedResults(results) {
        if (!Array.isArray(results) || results.length < 2) {
            return results;
        }

        var hasGroups = results.some(function (item) {
            return item && Array.isArray(item.children);
        });

        if (!hasGroups) {
            return results;
        }

        return results.slice().sort(function (a, b) {
            return groupPriority(a.text) - groupPriority(b.text);
        });
    }

    acf.addFilter('select2_ajax_results', function (json) {
        if (!json || !Array.isArray(json.results)) {
            return json;
        }

        json.results = reorderGroupedResults(json.results);

        return json;
    });
})(jQuery);
