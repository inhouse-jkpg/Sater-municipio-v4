(function () {
  const config = window.saterPublishValidation;
  if (!config || !config.ajaxUrl) {
    return;
  }

  const form = document.getElementById('post');
  const publishButton = document.getElementById('publish');
  if (!form || !publishButton) {
    return;
  }

  const i18n = config.i18n || {};
  let allowPublish = false;
  let lastFocus = null;
  let dialog = null;

  function getPostId() {
    const field = document.getElementById('post_ID');
    return field ? field.value : '0';
  }

  function getTitle() {
    const field = document.getElementById('title');
    return field ? field.value : '';
  }

  function getContent() {
    if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
      window.tinymce.triggerSave();
    }

    const field = document.getElementById('content');
    return field ? field.value : '';
  }

  function appendNamedFields(body, namePrefix) {
    form.querySelectorAll('input, textarea, select').forEach((el) => {
      if (!el.name || el.name.indexOf(namePrefix) !== 0) {
        return;
      }

      if (el instanceof HTMLInputElement && (el.type === 'checkbox' || el.type === 'radio') && !el.checked) {
        return;
      }

      if (el.disabled) {
        return;
      }

      body.append(el.name, el.value);
    });
  }

  function collectImageIds() {
    const ids = [];
    const thumb = document.getElementById('_thumbnail_id');
    if (thumb && thumb.value && thumb.value !== '-1') {
      ids.push(thumb.value);
    }

    document.querySelectorAll('.acf-field-image input[type="hidden"], .acf-field-focuspoint input[type="hidden"]').forEach((input) => {
      const value = (input.value || '').trim();
      if (!value) {
        return;
      }

      if (/^\d+$/.test(value)) {
        ids.push(value);
        return;
      }

      try {
        const parsed = JSON.parse(value);
        if (parsed && parsed.id) {
          ids.push(String(parsed.id));
        }
      } catch (error) {
        // Ignore non-JSON hidden values.
      }
    });

    return ids;
  }

  function setBusy(isBusy) {
    publishButton.disabled = isBusy;
    publishButton.setAttribute('aria-busy', isBusy ? 'true' : 'false');
    if (isBusy) {
      publishButton.dataset.saterOriginalLabel = publishButton.value;
      publishButton.value = i18n.checking || 'Kontrollerar sidan…';
    } else if (publishButton.dataset.saterOriginalLabel) {
      publishButton.value = publishButton.dataset.saterOriginalLabel;
    }
  }

  function continuePublish() {
    closeDialog();
    allowPublish = true;
    publishButton.click();
  }

  function splitViolations(violations) {
    const errors = [];
    const warnings = [];

    (violations || []).forEach((item) => {
      if (!item || !item.message) {
        return;
      }
      if (item.severity === 'warning') {
        warnings.push(item);
      } else {
        errors.push(item);
      }
    });

    return { errors, warnings };
  }

  function listMarkup(items) {
    return items
      .map((item) => '<li>' + escapeHtml(item.message) + '</li>')
      .join('');
  }

  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
  }

  function buildDialog() {
    if (dialog) {
      return dialog;
    }

    dialog = document.createElement('div');
    dialog.className = 'sater-pv';
    dialog.hidden = true;
    dialog.innerHTML =
      '<div class="sater-pv__backdrop" data-sater-pv-close="1"></div>' +
      '<div class="sater-pv__dialog" role="dialog" aria-modal="true" aria-labelledby="sater-pv-title" tabindex="-1">' +
      '<h2 id="sater-pv-title" class="sater-pv__title"></h2>' +
      '<p class="sater-pv__intro"></p>' +
      '<ul class="sater-pv__errors"></ul>' +
      '<p class="sater-pv__warnings-title" hidden></p>' +
      '<ul class="sater-pv__warnings"></ul>' +
      '<p class="sater-pv__request-error" hidden></p>' +
      '<div class="sater-pv__actions">' +
      '<button type="button" class="button button-primary sater-pv__close"></button>' +
      '<button type="button" class="button button-primary sater-pv__continue" hidden></button>' +
      '</div>' +
      '</div>';

    document.body.appendChild(dialog);

    dialog.addEventListener('click', (event) => {
      const target = event.target;
      if (target && target.getAttribute && target.getAttribute('data-sater-pv-close') === '1') {
        closeDialog();
      }
    });

    dialog.querySelector('.sater-pv__close').addEventListener('click', closeDialog);
    dialog.querySelector('.sater-pv__continue').addEventListener('click', continuePublish);

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && dialog && !dialog.hidden) {
        closeDialog();
      }
    });

    return dialog;
  }

  function showDialog(result, requestError) {
    const root = buildDialog();
    const { errors, warnings } = splitViolations(result && result.violations);
    const hasErrors = errors.length > 0;
    const title = root.querySelector('.sater-pv__title');
    const intro = root.querySelector('.sater-pv__intro');
    const errorList = root.querySelector('.sater-pv__errors');
    const warningTitle = root.querySelector('.sater-pv__warnings-title');
    const warningList = root.querySelector('.sater-pv__warnings');
    const requestErrorEl = root.querySelector('.sater-pv__request-error');
    const closeButton = root.querySelector('.sater-pv__close');
    const continueButton = root.querySelector('.sater-pv__continue');
    const dialogPanel = root.querySelector('.sater-pv__dialog');

    title.textContent = hasErrors || requestError
      ? (i18n.title || 'Sidan kan inte publiceras')
      : (i18n.warningsTitle || 'Kontrollera innan publicering');

    if (requestError) {
      intro.hidden = true;
    } else if (hasErrors) {
      intro.textContent = i18n.intro || 'Åtgärda felen nedan innan du publicerar.';
      intro.hidden = false;
    } else {
      intro.textContent = i18n.warningIntro || 'Du kan publicera, men tänk på följande.';
      intro.hidden = false;
    }

    errorList.innerHTML = listMarkup(errors);
    errorList.hidden = errors.length === 0;

    warningTitle.textContent = i18n.warningsHeading || 'Varningar';
    warningTitle.hidden = !(hasErrors && warnings.length > 0);
    warningList.innerHTML = listMarkup(warnings);
    warningList.hidden = warnings.length === 0;

    if (requestError) {
      requestErrorEl.textContent = requestError;
      requestErrorEl.hidden = false;
    } else {
      requestErrorEl.hidden = true;
    }

    closeButton.textContent = i18n.close || 'Stäng';
    const showPublish = !requestError && !hasErrors && warnings.length > 0;
    root.classList.toggle('sater-pv--can-publish', showPublish);
    continueButton.hidden = !showPublish;
    continueButton.textContent = i18n.publish || 'Publicera';

    lastFocus = document.activeElement;
    root.hidden = false;
    document.body.classList.add('sater-pv-open');
    dialogPanel.focus();
  }

  function closeDialog() {
    if (!dialog || dialog.hidden) {
      return;
    }

    dialog.hidden = true;
    document.body.classList.remove('sater-pv-open');
    if (lastFocus && typeof lastFocus.focus === 'function') {
      lastFocus.focus();
    }
  }

  function runCheck() {
    const body = new URLSearchParams();
    body.set('action', 'sater_publish_validation_check');
    body.set('nonce', config.nonce || '');
    body.set('post_id', getPostId());
    body.set('post_title', getTitle());
    body.set('content', getContent());

    const thumb = document.getElementById('_thumbnail_id');
    if (thumb) {
      body.set('_thumbnail_id', thumb.value);
    }

    collectImageIds().forEach((id) => {
      body.append('image_ids[]', id);
    });

    appendNamedFields(body, 'acf[');
    const hideTitle = form.querySelector('[name="modularity-module-hide-title"]');
    if (hideTitle && hideTitle instanceof HTMLInputElement && hideTitle.checked) {
      body.append('modularity-module-hide-title', hideTitle.value || '1');
    }

    return fetch(config.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      },
      body: body.toString(),
    }).then((response) => response.json());
  }

  function onPublishClick(event) {
    if (allowPublish) {
      allowPublish = false;
      return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
    setBusy(true);

    runCheck()
      .then((payload) => {
        setBusy(false);
        const result = payload && payload.data ? payload.data : null;

        if (!payload || !payload.success || !result) {
          showDialog({ violations: [] }, i18n.requestError);
          return;
        }

        const { errors, warnings } = splitViolations(result.violations);

        if (errors.length === 0 && warnings.length === 0) {
          continuePublish();
          return;
        }

        showDialog(result);
      })
      .catch(() => {
        setBusy(false);
        showDialog({ violations: [] }, i18n.requestError);
      });
  }

  publishButton.addEventListener('click', onPublishClick, true);
})();
