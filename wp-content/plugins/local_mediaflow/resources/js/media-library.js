!(function () {
  // Perform a post request against the API.
  const post = async (uri, data) => {
    const { REST_API_URL, WP_NONCE } = window.mediaflow;
    const url = `${REST_API_URL}${uri}`;

    return await fetch(url, {
      method: 'POST',
      body: JSON.stringify(data),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-WP-Nonce': WP_NONCE, // used to authenticate the user
      },
    });
  };

  const View = wp.media.View.extend({
    tagName: 'div',
    className: 'mediaflow-file-selector light', // set the theme color using light class
    attribution: null,

    handleUpload: async function (file) {
      // Upload the file to WordPress.
      const response = await post('files', file);
      const id = await response.json();

      // Register the usage of the file.
      const { POST_ID, USER } = window.mediaflow;
      await post('usages', {
        mediaflow_id: file.id,
        post_id: POST_ID,
        user: USER,
      });

      // Fetch the parent Mediaflow frame state.
      const state = this.controller.state();

      // Redirect the user to the media library page tab.
      if ('mediaflow' === this.controller.content.mode()) {
        this.controller.content.mode('browse');
      }

      // Refresh the media library to make the uploaded image appear.
      if (this.controller.content.get() !== null) {
        this.controller.content
          .get()
          .collection.props.set({ ignore: +new Date() });
        this.controller.content.get().options.selection.reset();
      } else {
        this.controller.library.props.set({ ignore: +new Date() });
      }

      // Fetch and select the attachment in the media library.
      const attachment = wp.media.model.Attachment.get(id);
      state.get('selection').add(attachment);
      this.controller.trigger('library:selection:add');
    },

    initialize() {
      // The keys are added to the window object in the mediaflow.php file using the wp_localize_script function.
      const { ACCESS_TOKEN, FORCE_ALT_TEXT, LOCALE, SETTINGS_URL } =
        window.mediaflow;

      // If the user hasn't added the API key, render an error message.
      if (!ACCESS_TOKEN) {
        this.el.innerHTML = `<div class="notice notice-error inline">
          <p>The Mediaflow library can't be loaded, please <a href="${SETTINGS_URL}">visit the settings page</a> and add your API key.</p>
          </div>`;
        console.error(
          'The client ID, client secret and refresh token keys is required for Mediaflow.',
        );
        return;
      }

      // Initialize and render the file selector.
      new FileSelector(this.el, {
        accesstoken: ACCESS_TOKEN,
        allowJSVideo: false,
        auth: 'accesstoken',
        forceAltText: JSON.parse(FORCE_ALT_TEXT), // parse the boolean string
        limitFileType: 'jpg,jpeg,png,gif,tif,tiff',
        locale: LOCALE,
        success: this.handleUpload.bind(this),
      });
    },
  });

  // Initialize the Mediaflow integration.
  const initialize = (frame) => ({
    // Setup the event listeners in order to render the Mediaflow page.
    bindHandlers() {
      frame.prototype.bindHandlers.apply(this, arguments);
      this.on('content:create', this.toggleSelectButton);
      this.on('content:create:mediaflow', this.renderView, this);
    },

    // Attach the Mediaflow page to the router and tab list.
    browseRouter(routerView) {
      frame.prototype.browseRouter.apply(this, arguments);

      routerView.set({
        mediaflow: {
          text: 'Mediaflow',
          priority: 60,
        },
      });
    },

    // Render the Mediaflow page.
    renderView(contentRegion) {
      contentRegion.view = new View({
        controller: this,
      });
    },

    // Hide and show the select button depending on the active tab.
    toggleSelectButton(event) {
      const buttons = Array.from(
        document.querySelectorAll('.media-button-select'),
      );

      const isMediaflowTab = event?.view?.el.classList.contains(
        'mediaflow-file-selector',
      );

      buttons.forEach(
        (button) =>
          (button.style.visibility = isMediaflowTab ? 'hidden' : 'visible'),
      );
    },
  });

  // Add the Mediaflow page to classic post media frame such as the gallery block.
  const framePost = wp.media.view.MediaFrame.Post;
  wp.media.view.MediaFrame.Post = framePost.extend(initialize(framePost));

  // Add the Mediaflow page to select media frame such as the image block.
  const frameSelect = wp.media.view.MediaFrame.Select;
  wp.media.view.MediaFrame.Select = frameSelect.extend(initialize(frameSelect));
})();
