document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.sater-mediaflow-poster').forEach(function (poster) {
    if (poster.dataset.saterMediaflowInit === '1') {
      return;
    }

    poster.dataset.saterMediaflowInit = '1';

    poster.addEventListener('click', function () {
      const template = document.getElementById(poster.dataset.embedId);

      if (!template) {
        return;
      }

      poster.parentElement.innerHTML = template.innerHTML;
    });
  });
});
