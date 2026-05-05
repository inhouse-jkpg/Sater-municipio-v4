/**
 * Refresh evenemangskategorier option counts when archive date inputs change
 * (counts are otherwise only correct after a full GET submit).
 */
(function () {
  'use strict';

  if (typeof saterEventsTaxCounts === 'undefined' || !saterEventsTaxCounts.restUrl) {
    return;
  }

  var root = document.querySelector('.s-archive-filter');
  if (!root) {
    return;
  }

  var fromInput = root.querySelector('[name="from"]');
  var toInput = root.querySelector('[name="to"]');
  var select =
    root.querySelector('select[name="evenemangskategorier[]"]') ||
    root.querySelector('select[name="evenemangskategorier"]');

  if (!select) {
    return;
  }

  function stripCountSuffix(text) {
    return String(text).replace(/\s*\(\d+\)\s*$/, '');
  }

  function applyCounts(counts) {
    if (!counts || typeof counts !== 'object') {
      return;
    }
    var opts = select.options;
    for (var i = 0; i < opts.length; i++) {
      var opt = opts[i];
      if (!opt.value) {
        continue;
      }
      var slug = opt.value;
      var base = stripCountSuffix(opt.text);
      var n = Object.prototype.hasOwnProperty.call(counts, slug) ? Number(counts[slug]) : 0;
      if (Number.isNaN(n)) {
        n = 0;
      }
      opt.text = base + ' (' + n + ')';
    }
    select.dispatchEvent(new Event('change', { bubbles: true }));
  }

  var timer;
  function scheduleFetch() {
    clearTimeout(timer);
    timer = setTimeout(fetchCounts, 320);
  }

  function fetchCounts() {
    var from = fromInput && fromInput.value ? String(fromInput.value) : '';
    var to = toInput && toInput.value ? String(toInput.value) : '';
    var url =
      saterEventsTaxCounts.restUrl +
      (saterEventsTaxCounts.restUrl.indexOf('?') === -1 ? '?' : '&') +
      'from=' +
      encodeURIComponent(from) +
      '&to=' +
      encodeURIComponent(to);

    fetch(url, { credentials: 'same-origin' })
      .then(function (r) {
        if (!r.ok) {
          throw new Error('bad status');
        }
        return r.json();
      })
      .then(function (data) {
        if (data && data.counts) {
          applyCounts(data.counts);
        }
      })
      .catch(function () {});
  }

  if (fromInput) {
    fromInput.addEventListener('change', scheduleFetch);
    fromInput.addEventListener('input', scheduleFetch);
  }
  if (toInput) {
    toInput.addEventListener('change', scheduleFetch);
    toInput.addEventListener('input', scheduleFetch);
  }

  if ((fromInput && fromInput.value) || (toInput && toInput.value)) {
    scheduleFetch();
  }
})();
