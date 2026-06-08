(function ($, config) {
  if (!config || typeof acf === 'undefined') {
    return;
  }

  const post = async (uri, data) => {
    const mf = window.mediaflow;
    if (!mf || !mf.REST_API_URL) {
      return null;
    }

    return fetch(`${mf.REST_API_URL}${uri}`, {
      method: 'POST',
      body: JSON.stringify(data),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-WP-Nonce': mf.WP_NONCE,
      },
    });
  };

  const getTypeField = () => acf.getField(config.typeFieldKey);

  const getIdField = () => acf.getField(config.idFieldKey);

  const getEmbedField = () => acf.getField(config.embedFieldKey);

  const isMediaflowType = () => {
    const typeField = getTypeField();
    return typeField && typeField.val() === config.typeValue;
  };

  const renderToolbar = (container) => {
    const mf = window.mediaflow;
    const hasToken = Boolean(
      config.pluginActive && mf && mf.ACCESS_TOKEN,
    );
    const idField = getIdField();
    const hasSelection = idField && parseInt(idField.val(), 10) > 0;

    container.innerHTML = '';

    if (!config.pluginActive) {
      const notice = document.createElement('div');
      notice.className = 'notice notice-warning inline';
      notice.innerHTML = `<p>${config.i18n.pluginInactive}</p>`;
      container.appendChild(notice);
      return;
    }

    if (!hasToken) {
      const notice = document.createElement('div');
      notice.className = 'notice notice-error inline';
      notice.innerHTML = `<p>${config.i18n.noToken}</p>`;
      if (config.settingsUrl) {
        notice.innerHTML += `<p><a href="${config.settingsUrl}" target="_blank" rel="noopener noreferrer">${config.i18n.settingsLink}</a></p>`;
      }
      container.appendChild(notice);
      return;
    }

    const actions = document.createElement('p');
    actions.className = 'sater-mediaflow-video-actions';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'button button-primary';
    button.textContent = hasSelection
      ? config.i18n.changeVideo
      : config.i18n.openSelector;
    button.addEventListener('click', () => openSelector(container));

    actions.appendChild(button);
    container.appendChild(actions);

    const previewWrap = document.createElement('div');
    previewWrap.className = 'sater-mediaflow-video-preview-wrap';
    previewWrap.hidden = !hasSelection;

    const previewLabel = document.createElement('p');
    previewLabel.className = 'description';
    previewLabel.textContent = config.i18n.previewLabel;

    const preview = document.createElement('div');
    preview.className = 'sater-mediaflow-video-preview embed embed__ratio--16-9';
    preview.setAttribute('data-sater-mediaflow-preview', '');

    previewWrap.appendChild(previewLabel);
    previewWrap.appendChild(preview);
    container.appendChild(previewWrap);

    updatePreview(preview);
  };

  const updatePreview = (previewEl) => {
    if (!previewEl) {
      previewEl = document.querySelector('[data-sater-mediaflow-preview]');
    }

    if (!previewEl) {
      return;
    }

    const embedField = getEmbedField();
    const html = embedField ? embedField.val() : '';

    previewEl.innerHTML = html || '';
    const wrap = previewEl.closest('.sater-mediaflow-video-preview-wrap');

    if (wrap) {
      wrap.hidden = !html;
    }
  };

  const openSelector = (container) => {
    const mf = window.mediaflow;

    if (!mf || !mf.ACCESS_TOKEN || typeof FileSelector === 'undefined') {
      return;
    }

    const idField = getIdField();
    const selectedId = idField ? parseInt(idField.val(), 10) : 0;

    const overlay = document.createElement('div');
    overlay.className = 'sater-mediaflow-video-modal-overlay';

    const modal = document.createElement('div');
    modal.className = 'sater-mediaflow-video-modal light';

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'sater-mediaflow-video-modal-close button';
    closeButton.textContent = '×';
    closeButton.setAttribute('aria-label', 'Close');

    const selectorMount = document.createElement('div');
    selectorMount.className = 'sater-mediaflow-video-modal-selector';

    const close = () => overlay.remove();

    closeButton.addEventListener('click', close);
    overlay.addEventListener('click', (event) => {
      if (event.target === overlay) {
        close();
      }
    });

    modal.appendChild(closeButton);
    modal.appendChild(selectorMount);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    const selectorOptions = {
      accesstoken: mf.ACCESS_TOKEN,
      allowJSVideo: false,
      auth: 'accesstoken',
      forceAltText: JSON.parse(mf.FORCE_ALT_TEXT || 'false'),
      limitFileType: 'mp4,mov,mpg,ts,avi',
      locale: mf.LOCALE,
      success: async (file) => {
        const idFieldRef = getIdField();
        const embedFieldRef = getEmbedField();

        if (idFieldRef) {
          idFieldRef.val(file.id);
        }

        if (embedFieldRef && file.embedCode) {
          embedFieldRef.val(file.embedCode);
        }

        const postId =
          typeof acf !== 'undefined' && acf.get('post_id')
            ? acf.get('post_id')
            : mf.POST_ID;

        if (postId && file.id) {
          await post('usages', {
            mediaflow_id: file.id,
            post_id: postId,
            user: mf.USER,
          });
        }

        close();
        renderToolbar(container);
      },
    };

    if (selectedId > 0) {
      selectorOptions.selectedFile = selectedId;
    }

    new FileSelector(selectorMount, selectorOptions);
  };

  const initContainer = (container) => {
    if (!container || container.dataset.saterMediaflowInit === '1') {
      return;
    }

    container.dataset.saterMediaflowInit = '1';
    renderToolbar(container);

    const embedField = getEmbedField();

    if (embedField) {
      embedField.$input().on('change', () => updatePreview());
    }
  };

  const ensureInjectedPicker = ($typeFieldEl) => {
    const $field = $typeFieldEl.closest('.acf-field');

    if (!$field.length) {
      return;
    }

    let $picker = $field.next('.sater-mediaflow-video-injected');

    if (!$picker.length) {
      $picker = $(
        '<div class="acf-field sater-mediaflow-video-injected"><div class="acf-input"><div class="sater-mediaflow-video-admin" data-sater-mediaflow-video-admin></div></div></div>',
      );
      $field.after($picker);
    }

    const container = $picker.find('[data-sater-mediaflow-video-admin]')[0];

    if (container && isMediaflowType()) {
      $picker.show();
      initContainer(container);
    } else {
      $picker.hide();
    }
  };

  const scan = () => {
    document
      .querySelectorAll('[data-sater-mediaflow-video-admin]')
      .forEach(initContainer);
  };

  const bindTypeField = (typeField) => {
    if (!typeField || typeField.$el.data('saterMediaflowBound') === 1) {
      return;
    }

    typeField.$el.data('saterMediaflowBound', 1);

    const refresh = () => {
      ensureInjectedPicker(typeField.$el);
      scan();
    };

    typeField.$el.find('input').on('change', refresh);
    refresh();
  };

  acf.addAction('ready', () => {
    scan();
    const typeField = getTypeField();
    if (typeField) {
      bindTypeField(typeField);
    }
  });

  acf.addAction('append', () => {
    scan();
    const typeField = getTypeField();
    if (typeField) {
      bindTypeField(typeField);
    }
  });

  acf.addAction('render_field/key=' + config.typeFieldKey, (field) => {
    bindTypeField(field);
  });

  acf.addAction(
    'render_field/key=field_sater_mediaflow_video_message',
    () => {
      scan();
    },
  );
})(jQuery, window.saterMediaflowVideo || null);
