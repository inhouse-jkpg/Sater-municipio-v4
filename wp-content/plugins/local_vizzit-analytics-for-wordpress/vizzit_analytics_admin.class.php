<?php
/**
 * Admin User Interface Class
 * Version 0.3
 */
if( !class_exists( 'Vizzit_Analytics_Admin' ) ) {

  class Vizzit_Analytics_Admin extends Vizzit_Analytics_Core {

    var $hook 		= 'vizzit-analytics-for-wordpress';
    var $filename	= 'vizzit-analytics-for-wordpress/vizzitanalytics.php';
    var $longname	= 'Vizzit Analytics for WordPress';
    var $shortname	= 'Vizzit Analytics';
    var $vaicon		= 'images/va_icon.png';
    var $vaicon32	= 'images/va_icon32.png';
    var $homepage	= 'http://www.vizzit.se/modules/wordpress/';

    var $msgUpdate	= ''; // used to e.g. display the "settings updated" message on top


    var $vizzit_analytics_options = array(
      'general' => array(
        'va_customer_id'                => '', // the customer-id (unique identifier)
        'va_test_mode'                  => '', // is this a test-installation?
        'va_debug_logging'              => '', // Extended debug logging?
        'va_webservice'                 => 'on', // allow access via webservice?
        'va_crypt_iv'                   => '', // the crypt-iv
        'va_identity'                   => '', // identity key
        'va_encryption'                 => '', // encryption key
        'va_public_key'                 => '', // public key
        'va_private_key'                => '', // private key
        'va_anonymize_ip'               => '', // anonymize IP-address
        'va_anonymize_usernames'        => '', // anonymize Usernames - tag-variable
        'va_append_username'            => '', // add the username as tag-variable
        'va_time_on_page'               => '', // add measure time on page tag-variable
        'va_disable_cookie_auto'        => '',
        'va_disable_auto_integration'   => '',
        'va_hidden_pref_no_upload'      => '',
        'va_hidden_pref_no_file_delete' => ''
      ),
      'page_tree_structure' => array(
        'va_structure_include_all'    => 'on', // include all page types when processing the tree structure?
        'va_structure_include_public' => 'on', // include all public page types when processing the tree structure?
        'va_structure_include_pages'	=> 'on', // include pages when processing the tree structure?
        'va_structure_include_posts'	=> 'on', // include posts when processing the tree structure?
        'va_structure_include_wordpress_system' => 'on', // include wordpress system such as feed etc. when processing the tree structure?
        'va_structure_include_wp_template' => 'on', // include wordpress template instead of page type?
        'va_structure_metadata'       => 'off', // include meta data
        'va_structure_metadata_page_username' => '', // field name for retrieval of meta data page responsible username
        'va_structure_metadata_page_email' => '', // field name for retrieval of meta data page responsible email
        'va_structure_metadata_content_username' => '', // field name for retrieval of meta data content responsible username
        'va_structure_metadata_content_email' => '' // field name for retrieval of meta data content responsible email
      ),
      'page_display_options' => array(
        'va_display_vds'				=> '',
        'va_display_v2'					=> '',
        'va_display_vms'				=> '',
        'va_display_vwm' 				=> '',
        'va_display_portal'     => 'on'
      ),
      'page_schedule' => array(
        'va_scheduler' 					=> 'on' // scheduler for processing and sending the tree structure
      )
    );




    /**
     * Constructur, load all required stuff.
     */
    function __construct()
    {
      parent::__construct();

      $this->plugin_url = plugins_url( '', __FILE__ ) . '/';

      // Register e.g. localization
      add_action('init', 		          	array(&$this, 'vizzit_analytics_init') );

      // Register some styles/scripts etc.
      add_action( 'admin_init', 			array(&$this, 'vizzit_analytics_admin_init') );

      // Register the settings page
      if($this->is_network)
        add_action('network_admin_menu', array(&$this, 'register_settings_page'));
      else
        add_action('admin_menu', array(&$this, 'register_settings_page'));

      // Drop a warning on each page of the admin when Vizzit Analytics hasn't been configured
      add_action( 'admin_footer',     array(&$this, 'warning') );

      // Save settings
      add_action( 'admin_init',       array(&$this, 'save_settings') );

    } // end __construct()


    /**
     * Stuff which needs to be in a separate function to be processed
     * NEW stuff
     */
    function vizzit_analytics_admin_init() {
      add_action('admin_head', array(&$this, 'vizzit_echo_variables'));
      wp_enqueue_style( 'va_styles', $this->plugin_url . VAWP_DIR_ASSETS . 'styles.css', false, VAWP_VERSION );
      wp_enqueue_script( 'va_scripts', $this->plugin_url . VAWP_DIR_ASSETS . 'scripts.js', array( 'jquery' ), VAWP_VERSION );
    }

    function vizzit_echo_variables() {
      $options = $this->get_options();

      if(!$options)
        return;

      $privateKey = $options['va_private_key'];
      if($privateKey == '')
        return;

      $username = $this->currentUser;
      $pageId = $this->get_parameter_postId();

      echo '<script type="text/javascript">
        var $vizzit_username = "'.$username.'";
        var $vizzit_page_id = "'.$pageId.'";
        var $vizzit_private_key = "'.$privateKey.'";
        </script>';

      // Add Vizzit button to admin bar
      add_action('admin_bar_menu', array(&$this, 'vizzit_analytics_add_admin_bar_button'));

      wp_enqueue_script( 'va_overlay', $this->plugin_url . VAWP_DIR_ASSETS . 'vizzit.access.js', array('jquery'), VAWP_VERSION );
    }

    /**
     * Adds a Vizzit button to the bar, which we use to add
     * our own access buttons to Portal and This Page
     * @param WP_Hook $wp_admin_bar
     * @return void
     */
    function vizzit_analytics_add_admin_bar_button($wp_admin_bar)
    {
      $args = array(
        'id' => 'vizzit-button',
        'meta' => array(
          'class' => 'menupop'
        )
      );
      $wp_admin_bar->add_node($args);
    }


    /**
     * Return url to admin page (hook = plugin)
     */
    function plugin_options_url() {
      if(is_network_admin())
        return network_admin_url( 'admin.php?page=' . $this->hook );
      else
        return admin_url( 'admin.php?page=' . $this->hook );
    }


    /**
     * Print warning if not set at least "zz_customer_id"
     */
    function warning() {
      // get options
      $options = $this->get_options();

      // display warning if customer-ID is not set/is empty
      if( !isset( $options[ 'va_customer_id' ] ) || empty( $options[ 'va_customer_id' ] ) ) {
        // TODO: string needs to be globalized (prepared with the __() or _e() functions)
        echo '<div id="message" class="error"><p><strong>' . $this->longname . ' is not active.</strong> You must add your <a href="' . $this->plugin_options_url() . '" title="">Customer-ID</a> before it can work.</p></div>';
      }

      // display warning if test_mode
      if( $options[ 'va_test_mode' ] == 'on' ) {
        // TODO: string needs to be globalized (prepared with the __() or _e() functions)
        echo '<div id="message" class="updated"><p><strong>' . $this->longname . ' is running in TEST_MODE.</strong></p></div>';
      }

      $vizzit_dir_tmp = dirname( __FILE__ ) . '/' . VAWP_DIR_TMP_FILES;
      if( !is_writable( $vizzit_dir_tmp ) ) {
        // TODO: string needs to be globalized (prepared with the __() or _e() functions)
        echo '<div id="message" class="error"><p><strong>Directory <code>' . $vizzit_dir_tmp . '</code> is not writable.</strong></p><p>This is needed for storing temporary files during structure processing.</p></div>';
      }

    } // end warning()


    /**
     * Create a Checkbox input field
     */
    function form_checkbox( $id, $label = '', $message = '' ) {
      $options = $this->get_options();

      $checked 	= ( $options[$id] == 'on' ) ? 'checked="checked"' : '';
      $label 	= ( $label != '' ) ? ' <label for="' . $id . '">' . $label . '</label>' : '';
      $data		= ( $message != '' ) ? 'data-message="' . $message . '"' : '';

      return '<input type="checkbox" id="' . $id . '" name="' . $id . '"'. $checked .' '.$data.' />' . $label;
    } // end form_checkbox()


    /**
     * Create a Text input field
     */
    function form_textinput( $id ) {
      $options = $this->get_options();

      // Always set options as at least an empty string to prevent ugly warnings
      if(!isset($options[$id]))
        $options[$id] = '';

      return '<input class="text" type="text" id="' . $id . '" name="' . $id . '" size="30" value="' . $options[ $id ] . '" />';
    } // end form_textinput()


    /**
     * Create a dropdown field
     */
    function form_select( $id, $options, $multiple = false ) {
      $opt = $this->get_options();

      $output = '<select class="select" name="' . $id . '" id="' . $id . '">';
      foreach( $options as $val => $name ) {
        $sel = '';
        if( $opt[$id] == $val ) {
          $sel = ' selected="selected"';
        }
        if( $name == '' ) {
          $name = $val;
        }

        $output .= '<option value="' . $val . '"' . $sel . '>' . $name . '</option>';
      }

      $output .= '</select>';

      return $output;
    } // end form_select()


    /**
     * Render save-button
     */
    function form_save_button() {
      return '<br /><br class="clear" /><input type="submit" class="button-primary" name="submit" value="' . __( 'Update Vizzit Analytics Settings', VAWP_LOCALE_HOOK ) . '" /><br class="clear" />';
    } // end form_save_button()


    /**
     * Create a form table from an array of rows
     */
    function form_table( $rows ) {
      $content = '<table class="form-table">';
      $i = 1;

      foreach( $rows as $row ) {
        $class 	= '';
        $class .= 'va_row';
        $class .= ($i % 2 == 0) ? ' even' : '';

        $content .= '<tr id="' . $row[ 'id' ] . '_row" class="' . $class . '">';
          $content .= '<th valign="top" scope="row">';
            if( isset( $row[ 'id' ] ) && $row[ 'id' ] != '' ) {
              $content .= '<label for="' . $row[ 'id' ] . '">' . $row[ 'label' ] . '</label>';
            } else {
              $content .= $row['label'];
            }
          $content .= '</th>';
          $content .= '<td valign="top">';
            $content .= $row['content'];
            $content .= ( isset($row['desc']) && !empty($row['desc']) ) ? ' </td><td><span class="description">'.$row['desc'].'</span>' : '';
          $content .= '</td>';
        $content .= '</tr>';

        $i++;
      }

      $content .= '</table>';

      return $content;
    } // end form_table()

    /**
     * Returns "pageid" for current page
     */
    function get_parameter_postId() {
      global $post;

      $postId = '';

      if($post !== NULL)
        $postId  = $post->ID;

      if(!isset($postId) && isset($_GET['post']))
        $postId = $_GET['post'];

      return $postId;
    } // end get_parameter_postId()

    /**
     * Custom box in edit for pages/posts - Content
     */
    function custom_box_edit() {
      global $post;
      $options = $this->get_options();
      $current_user = ( $options[ 'va_anonymize_usernames' ] == 'on' ) ? md5( $this->currentUser ) : $this->currentUser; // check if needed to anonymize username

      // Use nonce for verification
      echo '<p>';
       echo 'Disabled, will be removed.';
      echo '</p>';
    } // end custom_box_init()

    /**
     * Register the admin menu
     */
    function register_settings_page() {
      // Set help texts to show on top of each setting page (via ajax-dropdown)
      $va_help_menu_page = '<p>General settings for the Vizzit Analytics WordPress Plugin </p>'; // help text for the main settings page
      $va_help_submenu_page_1 = '<p>Settings for what parts of the sites structure that Vizzit Analytics should process </p>'; // help text for tree-structure
      $va_help_submenu_page_2 = '<p>Settings for where Vizzit should be accessible </p>'; // help text for display-options
      $va_help_submenu_page_3 = '<p>Here you can activate and deactivate the scheduling of the processing and also start a manual process</p>'; // help text for schedule
      $screen = get_current_screen();

      // main menu - as well as the first sub-item
      $va_menu_page = add_menu_page('Vizzit Analytics Settings', __( 'Vizzit Analytics', VAWP_LOCALE_HOOK ), 'manage_options', $this->hook, array(&$this, 'settings_page'), plugin_dir_url( __FILE__ ) . 'images/va_icon.png' );
      
      // adding context-help to this page
      if($va_menu_page && $screen) {
        $screen->add_help_tab(array(
            'id' => 'vizzit-settings-help-1',
            'title' => 'Vizzit Analytics Settings',
            'content' => $va_help_menu_page
        ));
      }

      // chose which tree-node to use, if just pages or blog-items as well
      $va_submenu_page_1 = add_submenu_page( $this->hook, __( 'Vizzit Analytics Settings - Tree Structure', VAWP_LOCALE_HOOK ), __( 'Tree Structure', VAWP_LOCALE_HOOK ), 'manage_options', $this->hook . '-tree-structure', array(&$this, 'settings_page_tree_structure') );
      
      // adding context-help to this page
      if($va_submenu_page_1 && $screen) {
        $screen->add_help_tab(array(
            'id' => 'vizzit-settings-help-2',
            'title' => 'Vizzit Analytics Settings - Tree Structure',
            'content' => $va_help_submenu_page_1
        ));
      }

      // process structure manually, maybe backup as well
      $va_submenu_page_3 = add_submenu_page( $this->hook, __( 'Vizzit Analytics Settings - Schedule', VAWP_LOCALE_HOOK ), __( 'Schedule', VAWP_LOCALE_HOOK ), 'manage_options', $this->hook . '-schedule', array(&$this, 'settings_page_schedule') );
      
      // adding context-help to this page
      if($va_submenu_page_3 && $screen) {
        $screen->add_help_tab(array(
            'id' => 'vizzit-settings-help-4',
            'title' => 'Vizzit Analytics Settings - Display Options',
            'content' => $va_help_submenu_page_3
        ));
      }
    } // end register_settings_page

    /**
     * Register the network admin menu
     */
    function register_network_settings_page() {
      // Set help texts to show on top of each setting page (via ajax-dropdown)
      $va_help_menu_page = '<p>General settings for the Vizzit Analytics WordPress Plugin </p>'; // help text for the main settings page
      $va_help_submenu_page_1 = '<p>Settings for what parts of the sites structure that Vizzit Analytics should process </p>'; // help text for tree-structure
      $va_help_submenu_page_2 = '<p>Settings for where Vizzit should be accessible </p>'; // help text for display-options
      $va_help_submenu_page_3 = '<p>Here you can activate and deactivate the scheduling of the processing and also start a manual process</p>'; // help text for schedule
      $screen = get_current_screen();

      // main menu - as well as the first sub-item
      $va_menu_page = add_menu_page('Vizzit Analytics Settings', __( 'Vizzit Analytics', VAWP_LOCALE_HOOK ), 'manage_options', $this->hook, array(&$this, 'network_settings_page'), plugin_dir_url( __FILE__ ) . 'images/va_icon.png' );
      
      // adding context-help to this page
      if($va_menu_page && $screen) {
        $screen->add_help_tab(array(
            'id' => 'vizzit-settings-help-1',
            'title' => 'Vizzit Analytics Settings',
            'content' => $va_help_menu_page
        ));
      }

      // chose which tree-node to use, if just pages or blog-items as well
      $va_submenu_page_1 = add_submenu_page( $this->hook, __( 'Vizzit Analytics Settings - Tree Structure', VAWP_LOCALE_HOOK ), __( 'Tree Structure', VAWP_LOCALE_HOOK ), 'manage_options', $this->hook . '-tree-structure', array(&$this, 'settings_page_tree_structure') );
      
      // adding context-help to this page
      if($va_submenu_page_1 && $screen) {
        $screen->add_help_tab(array(
            'id' => 'vizzit-settings-help-2',
            'title' => 'Vizzit Analytics Settings - Tree Structure',
            'content' => $va_help_submenu_page_1
        ));
      }
      
      // adding context-help to this page
      if($va_submenu_page_2 && $screen) {
        $screen->add_help_tab(array(
            'id' => 'vizzit-settings-help-3',
            'title' => 'Vizzit Analytics Settings - Display Options',
            'content' => $va_help_submenu_page_2
        ));
      }

      // process structure manually, maybe backup as well
      $va_submenu_page_3 = add_submenu_page( $this->hook, __( 'Vizzit Analytics Settings - Schedule', VAWP_LOCALE_HOOK ), __( 'Schedule', VAWP_LOCALE_HOOK ), 'manage_options', $this->hook . '-schedule', array(&$this, 'settings_page_schedule') );
      
      // adding context-help to this page
      if($va_submenu_page_3 && $screen) {
        $screen->add_help_tab(array(
            'id' => 'vizzit-settings-help-4',
            'title' => 'Vizzit Analytics Settings - Display Options',
            'content' => $va_help_submenu_page_3
        ));
      }
    } // end register_network_settings_page

    /**
     * Setting up the Settings page
     */
    function settings_page() {
      //must check that the user has the required capability
      if( !current_user_can( 'manage_options' ) ) die( __( 'You cannot edit the Vizzit Analytics for WordPress options.', VAWP_LOCALE_HOOK ) );

      // get options
      $options = $this->get_options();

      echo $this->msgUpdate; // show message
      $this->msgUpdate = ''; // reset message


      // set defaults if nothing set at all
      if( !isset( $options['va_customer_id'] ) || $options['va_customer_id'] == '' ) {
        $options = $this->set_defaults();
      }


      echo '<div class="wrap">';
        echo '<div class="icon32"><img src="' . $this->plugin_url . $this->vaicon32 . '" width="32" height="32" alt="va_icon" title="Vizzit Analytics" /></div>';
        echo "<h2>" . __( 'Vizzit Analytics General Settings', VAWP_LOCALE_HOOK ) . "</h2>";

        echo '<p>';
          _e( 'Thank you for choosing ' . $this->longname . ' as your Weblog-Statistic-Tool.', VAWP_LOCALE_HOOK );
        echo '</p>';
        ?>
      <form action="<?php echo $this->plugin_options_url(); ?>" method="post" id="vizzit-analytics-conf">
        <input type="hidden" name="plugin" value="vizzit-analytics-for-wordpress" />
        <input type="hidden" name="plugin-setting" value="general" />

        <?php
          $rows[] = array(
            'id' 		=> 'va_customer_id',
            'label' 	=> __( 'Customer-ID', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Please insert the Customer-ID provided by Vizzit. This can be found in the email that was sent along with the module.', VAWP_LOCALE_HOOK ),
            'content' 	=> $this->form_textinput( 'va_customer_id' )
          );

          $rows[] = array(
            'id' 		=> 'va_test_mode',
            'label' 	=> __( 'Test Installation', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Setting for non live test sites', VAWP_LOCALE_HOOK ),
            'content' 	=> $this->form_checkbox( 'va_test_mode', '', __( 'Do you really want to enable the test mode?', VAWP_LOCALE_HOOK ) )
          );

          $rows[] = array(
            'id'    => 'va_debug_logging',
            'label'   => __( 'Enable debug logs', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Enables extended debug logs', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_debug_logging', '' )
          );

          $rows[] = array(
            'id' 		=> 'va_webservice',
            'label' 	=> __( 'Enable Webservice', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Grant access for Vizzit to contact the Vizzit Analytics WordPress Plugin', VAWP_LOCALE_HOOK ),
            'content' 	=> $this->form_checkbox( 'va_webservice', '' )
          );
          #IV
          $rows[] = array(
            'id'    => 'va_crypt_iv',
            'label'   => __( 'Private IV', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Key generated from Vizzit Analytics', VAWP_LOCALE_HOOK )  . '</code>',
            'content'   => $this->form_textinput( 'va_crypt_iv' )
          );
          #PRIVATE-KEY AKA IDENTITY SETTINGS
          $rows[] = array(
            'id' 		=> 'va_identity',
            'label' 	=> __( 'Identity', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Identity generated from Vizzit Analytics', VAWP_LOCALE_HOOK ) . ' e.g. <code>' . "HnwDTEkdpq517" . '</code>',
            'content' 	=> $this->form_textinput( 'va_identity' )
          );
          #IV-KEY AKA ENCRYPTION SETTINGS
          $rows[] = array(
            'id' 		=> 'va_encryption',
            'label' 	=> __( 'Encryption', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Key generated from Vizzit Analytics', VAWP_LOCALE_HOOK ) . ' e.g. <code>' . "yY8lc4C2MLDkq7f042qVJSREY" . '</code>',
            'content' 	=> $this->form_textinput( 'va_encryption' )
          );
          # Public key
          $rows[] = array(
            'id'    => 'va_public_key',
            'label'   => __( 'Public key', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Public key generated from Vizzit Analytics', VAWP_LOCALE_HOOK ) . '</code>',
            'content'   => $this->form_textinput( 'va_public_key' )
          );
          # Private key
          $rows[] = array(
            'id'    => 'va_private_key',
            'label'   => __( 'Private key', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Private key generated from Vizzit Analytics', VAWP_LOCALE_HOOK ) . '</code>',
            'content'   => $this->form_textinput( 'va_private_key' )
          );

          $rows[] = array(
            'id' 		=> 'va_anonymize_ip',
            'label' 	=> __( 'Anonymize IP-Addresses', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Anonymize the information sent by the tracker objects by removing the last octet of the IP address prior to its storage.', VAWP_LOCALE_HOOK ),
            'content' 	=> $this->form_checkbox( 'va_anonymize_ip' )
          );

          $rows[] = array(
            'id' 		=> 'va_anonymize_usernames',
            'label' 	=> __( 'Anonymize Usernames', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Encrypt usernames ', VAWP_LOCALE_HOOK ),
            'content' 	=> $this->form_checkbox( 'va_anonymize_usernames' )
          );

          $rows[] = array(
            'id' 		=> 'va_append_username',
            'label' 	=> __( 'Append Username', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Appending the current user information.', VAWP_LOCALE_HOOK ),
            'content' 	=> $this->form_checkbox( 'va_append_username' )
          );

          $rows[] = array(
            'id' 		=> 'va_time_on_page',
            'label' 	=> __( 'Measure Time On Page', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Enables tracking the time the user spent on each page.', VAWP_LOCALE_HOOK ),
            'content' 	=> $this->form_checkbox( 'va_time_on_page' )
          );

          $rows[] = array(
            'id'    => 'va_disable_cookie_auto',
            'label'   => __( 'Disable automatic cookies', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Set cookie manually by calling <code>$vizzit.cookie.set();</code> via e.g. an onclick event on your consent button.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_disable_cookie_auto' )
          );

          $rows[] = array(
            'id'    => 'va_disable_auto_integration',
            'label'   => __( 'Disable automatic activation of javascript integration', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Activate manually by calling <code>$vizzit$.integration.run();</code> via e.g. an onclick event on your consent button.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_disable_auto_integration' )
          );

          /*
          $rows[] = array(
            'id' 		=> '',
            'label' 	=> __( 'Insert Vizzit Tag Script', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Should the Vizzit tag script be applied to all WordPress pages/Blog-Posts?', VAWP_LOCALE_HOOK ),
            'content' 	=>
              $this->form_checkbox( 'va_insert_tag_on_pages', __( 'on Pages', VAWP_LOCALE_HOOK ) ) . '<br />' .
              $this->form_checkbox( 'va_insert_tag_on_posts', __( 'on Blog-Post', VAWP_LOCALE_HOOK ) )
          );
          */

          echo $this->form_table( $rows ) . $this->form_save_button();

        echo '</form>';
      echo '</div>';
    } // end zz_settings()

    /**
     * Setting up the Network Settings page
     */
    function network_settings_page() {
      //must check that the user has the required capability
      if( !current_user_can( 'manage_options' ) ) die( __( 'You cannot edit the Vizzit Analytics for WordPress options.', VAWP_LOCALE_HOOK ) );

      // get options
      $options = $this->get_options();

      echo $this->msgUpdate; // show message
      $this->msgUpdate = ''; // reset message


      // set defaults if nothing set at all
      if( !isset( $options['va_customer_id'] ) || $options['va_customer_id'] == '' ) {
        $options = $this->set_defaults();
      }

      echo '<div class="wrap">';
        echo '<div class="icon32"><img src="' . $this->plugin_url . $this->vaicon32 . '" width="32" height="32" alt="va_icon" title="Vizzit Analytics" /></div>';
        echo "<h2>" . __( 'Vizzit Analytics General Settings', VAWP_LOCALE_HOOK ) . "</h2>";

        echo '<p>';
          _e( 'Thank you for choosing ' . $this->longname . ' as your Weblog-Statistic-Tool.', VAWP_LOCALE_HOOK );
        echo '</p>';
        ?>
      <form action="<?php echo $this->plugin_options_url(); ?>" method="post" id="vizzit-analytics-conf">
        <input type="hidden" name="plugin" value="vizzit-analytics-for-wordpress" />
        <input type="hidden" name="plugin-setting" value="general" />

        <?php
          $rows[] = array(
            'id'    => 'va_customer_id',
            'label'   => __( 'Customer-ID', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Please insert the Customer-ID provided by Vizzit. This can be found in the email that was sent along with the module.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_textinput( 'va_customer_id' )
          );

          $rows[] = array(
            'id'    => 'va_test_mode',
            'label'   => __( 'Test Installation', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Setting for non live test sites', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_test_mode', '', __( 'Do you really want to enable the test mode?', VAWP_LOCALE_HOOK ) )
          );

          $rows[] = array(
            'id'    => 'va_webservice',
            'label'   => __( 'Enable Webservice', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Grant access for Vizzit to contact the Vizzit Analytics WordPress Plugin', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_webservice', '' )
          );
          #IV NETWORK
          $rows[] = array(
            'id'    => 'va_crypt_iv',
            'label'   => __( 'Private IV', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Key generated from Vizzit Analytics', VAWP_LOCALE_HOOK )  . '</code>',
            'content'   => $this->form_textinput( 'va_crypt_iv' )
          );
          #PRIVATE-KEY AKA IDENTITY NETWORK-SETTINGS
          $rows[] = array(
            'id'    => 'va_identity',
            'label'   => __( 'Identity', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Identity generated from Vizzit Analytics', VAWP_LOCALE_HOOK ) . ' e.g. <code>' . "HnwDTEkdpq517" . '</code>',
            'content'   => $this->form_textinput( 'va_identity' )
          );
          #IV-KEY AKA ENCRYPTION NETWORK-SETTINGS
          $rows[] = array(
            'id'    => 'va_encryption',
            'label'   => __( 'Encryption', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Key generated from Vizzit Analytics', VAWP_LOCALE_HOOK ) . ' e.g. <code>' . "yY8lc4C2MLDkq7f042qVJSREY" . '</code>',
            'content'   => $this->form_textinput( 'va_encryption' )
          );
          # Public key
          $rows[] = array(
            'id'    => 'va_public_key',
            'label'   => __( 'Public key', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Public key generated from Vizzit Analytics', VAWP_LOCALE_HOOK ) . '</code>',
            'content'   => $this->form_textinput( 'va_public_key' )
          );

          # Private key
          $rows[] = array(
            'id'    => 'va_private_key',
            'label'   => __( 'Private key', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Private key generated from Vizzit Analytics', VAWP_LOCALE_HOOK ) . '</code>',
            'content'   => $this->form_textinput( 'va_private_key' )
          );

          $rows[] = array(
            'id'    => 'va_anonymize_ip',
            'label'   => __( 'Anonymize IP-Addresses', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Anonymize the information sent by the tracker objects by removing the last octet of the IP address prior to its storage.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_anonymize_ip' )
          );

          $rows[] = array(
            'id'    => 'va_anonymize_usernames',
            'label'   => __( 'Anonymize Usernames', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Encrypt usernames ', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_anonymize_usernames' )
          );

          $rows[] = array(
            'id'    => 'va_append_username',
            'label'   => __( 'Append Username', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Appending the current user information.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_append_username' )
          );

          $rows[] = array(
            'id'    => 'va_time_on_page',
            'label'   => __( 'Measure Time On Page', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Enables tracking the time the user spent on each page.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_time_on_page' )
          );

          $rows[] = array(
            'id'    => 'va_disable_cookie_auto',
            'label'   => __( 'Disable automatic cookies', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Set cookie manually by calling <code>$vizzit.cookie.set();</code> via e.g. an onclick event on your consent button.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_disable_cookie_auto' )
          );

          $rows[] = array(
            'id'    => 'va_disable_auto_integration',
            'label'   => __( 'Disable automatic activation of javascript integration', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Activate manually by calling <code>$vizzit$.integration.run();</code> via e.g. an onclick event on your consent button.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_disable_auto_integration' )
          );

          /*
          $rows[] = array(
            'id'    => '',
            'label'   => __( 'Insert Vizzit Tag Script', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Should the Vizzit tag script be applied to all WordPress pages/Blog-Posts?', VAWP_LOCALE_HOOK ),
            'content'   =>
              $this->form_checkbox( 'va_insert_tag_on_pages', __( 'on Pages', VAWP_LOCALE_HOOK ) ) . '<br />' .
              $this->form_checkbox( 'va_insert_tag_on_posts', __( 'on Blog-Post', VAWP_LOCALE_HOOK ) )
          );
          */

          echo $this->form_table( $rows ) . $this->form_save_button();

        echo '</form>';
      echo '</div>';
    } // end zz_settings()

    /**
     * Function for saving settings
     */
    function save_settings() {
      $options = $this->get_options();
      $optionsRequired 	= array();

      // just do nothing if processed manually - see function settings_page_schedule() for details
      if( isset( $_POST[ 'submit-process-manually' ] ) ) { return; }

      // when all required parameters are set
      if(
        isset( $_POST[ 'submit' ] ) &&
        isset( $_POST[ 'plugin' ] ) && $_POST[ 'plugin' ] == 'vizzit-analytics-for-wordpress' && // is it the Vizzit plugin?
        isset( $_POST[ 'plugin-setting'] ) && !empty( $_POST[ 'plugin-setting' ] ) // check for current plugin-settings-page to update
      ) {
        // display message if not enough rights to save settings
        if( !current_user_can( 'manage_options' ) ) die( __( 'You cannot edit the Vizzit Analytics for WordPress options.', VAWP_LOCALE_HOOK ) );

        // set the required options to update
        $optionsRequired = array_keys( $this->vizzit_analytics_options[ $_POST[ 'plugin-setting' ] ] );

        // is the array set?
        if( count( $optionsRequired ) > 0 ) {
          // loop the required options and
          foreach( $optionsRequired as $option_name ) {
            if( isset( $_POST[ $option_name ] ) ) {
              // validate va_customer_id
              if( $option_name == 'va_customer_id' ) {
                $_POST[ $option_name ] = trim( $_POST[ $option_name ] );
                $_POST[ $option_name ] = preg_replace( '/[^0-9a-z_]/i', '', $_POST[ $option_name ] );
                $options[ $option_name ] = $_POST[ $option_name ];
              } else {
                $options[ $option_name ] = trim( $_POST[ $option_name ] );
              }
            } else {
              $options[ $option_name ] = '';
            }
          } // foreach( $optionsRequired )

          // save options
          $this->update_options($options);

          // set message to display
          $this->msgUpdate = '<div class="updated"><p><strong>' . __( 'Vizzit Analytics settings saved.', VAWP_LOCALE_HOOK ) . '</strong></p></div>';
        } // end if( count( $optionsRequired ) > 0 )

      } // end if( isset( $_POST ) ... )
    } // end save_settings()


    /**
     * Setting up Tree structure page
     */
    function settings_page_tree_structure() {
      global $wpdb;

      //must check that the user has the required capability
      if( !current_user_can( 'manage_options' ) ) die( __( 'You cannot edit the Vizzit Analytics for WordPress options.', VAWP_LOCALE_HOOK ) );

      // get options
      $options = $this->get_options();

      echo $this->msgUpdate; // show message
      $this->msgUpdate = ''; // reset message

      echo '<div class="wrap">';
        echo '<div class="icon32"><img src="' . $this->plugin_url . $this->vaicon32 . '" width="32" height="32" alt="va_icon" title="Vizzit Analytics" /></div>';
        echo "<h2>" . __( 'Vizzit Analytics Tree Structure', VAWP_LOCALE_HOOK ) . "</h2>";
        
        echo '<p>';
          _e( 'Settings for what parts of the sites structure that ' . $this->longname . ' should process.', VAWP_LOCALE_HOOK );
        echo '</p>';

        ?>
      <form action="<?php echo $this->plugin_options_url() . '-tree-structure'; ?>" method="post" id="vizzit-analytics-conf">
        <input type="hidden" name="plugin" value="vizzit-analytics-for-wordpress" />
        <input type="hidden" name="plugin-setting" value="page_tree_structure" />

        <?php
          $rows[] = array(
            'id'    => 'va_structure_include_all',
            'label'   => __( 'Include All', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Includes <code>All Post Types</code> when processing tree structure (overrides below options)', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_structure_include_all' )
          );
          $rows[] = array(
            'id'    => 'va_structure_include_public',
            'label'   => __( 'Include All Public Post Types', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Includes <code>All Public Post Types</code> when processing tree structure (overrides below options)', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_structure_include_public' )
          );
          $rows[] = array(
            'id' 		=> 'va_structure_include_pages',
            'label' 	=> __( 'Include Pages', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Includes <code>WordPress Pages</code> when processing tree structure', VAWP_LOCALE_HOOK ),
            'content' 	=> $this->form_checkbox( 'va_structure_include_pages' )
          );
          $rows[] = array(
            'id' 		=> 'va_structure_include_posts',
            'label' 	=> __( 'Include Posts', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Includes <code>WordPress Posts</code> when processing tree structure', VAWP_LOCALE_HOOK ),
            'content' 	=> $this->form_checkbox( 'va_structure_include_posts' )
          );

          $rows[] = array(
            'id' 		=> 'va_structure_include_wordpress_system',
            'label' 	=> __( 'Include WordPress special URLs', VAWP_LOCALE_HOOK ),
            'desc' 		=> __( 'Includes <code>WordPress special URLs</code> such as Newsfeed etc. when processing tree structure', VAWP_LOCALE_HOOK ),
            'content' 	=> $this->form_checkbox( 'va_structure_include_wordpress_system' )
          );
          $rows[] = array(
            'id'    => 'va_structure_include_wp_template',
            'label'   => __( 'Include WordPress template', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Includes <code>WordPress template data</code> per page. when processing tree structure', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_structure_include_wp_template' )
          );

          $rows[] = array(
            'id'    => 'va_structure_metadata',
            'label'   => __( 'Enable metadata', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Enable <code>structure metadata</code>. Enter field names for custom fields below.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_checkbox( 'va_structure_metadata' )
          );
          $rows[] = array(
            'id'    => 'va_structure_metadata_page_username',
            'label'   => __( 'Metadata, page responsible (username)', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Enter field name for retrieval of <code>username for page responsible</code>.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_textinput( 'va_structure_metadata_page_username' )
          );
          $rows[] = array(
            'id'    => 'va_structure_metadata_page_email',
            'label'   => __( 'Metadata, page responsible (email)', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Enter field name for retrieval of <code>email for page responsible</code>.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_textinput( 'va_structure_metadata_page_email' )
          );
          $rows[] = array(
            'id'    => 'va_structure_metadata_content_username',
            'label'   => __( 'Metadata, content responsible (username)', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Enter field name for retrieval of <code>username for content responsible</code>.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_textinput( 'va_structure_metadata_content_username' )
          );
          $rows[] = array(
            'id'    => 'va_structure_metadata_content_email',
            'label'   => __( 'Metadata, content responsible (email)', VAWP_LOCALE_HOOK ),
            'desc'    => __( 'Enter field name for retrieval of <code>email for content responsible</code>.', VAWP_LOCALE_HOOK ),
            'content'   => $this->form_textinput( 'va_structure_metadata_content_email' )
          );

          echo $this->form_table( $rows ) . $this->form_save_button();

        echo '</form>';

      echo '</div>';
    } // end settings_page_tree_structure()

    /**
     * Schedueling options
     */
    function settings_page_schedule() {
      //must check that the user has the required capability
      if( !current_user_can( 'manage_options' ) ) die( __( 'You cannot edit the Vizzit Analytics for WordPress options.', VAWP_LOCALE_HOOK ) );

      // get options
      $options = $this->get_options();

      echo $this->msgUpdate; // show message
      $this->msgUpdate = ''; // reset message

      echo '<div class="wrap">';
        echo '<div class="icon32"><img src="' . $this->plugin_url . $this->vaicon32 . '" width="32" height="32" alt="va_icon" title="Vizzit Analytics" /></div>';
        echo "<h2>" . __( 'Vizzit Analytics Schedule', VAWP_LOCALE_HOOK ) . "</h2>";

        echo '<p>';
          _e( 'Here you can deactivate and activate the schedueling for ' . $this->longname . ' you can also find the history for each proccess and also start a manual process of the structure.', VAWP_LOCALE_HOOK );
        echo '</p>';

        ?>
      <form action="<?php echo $this->plugin_options_url() . '-schedule'; ?>" method="post" id="vizzit-analytics-conf">
        <input type="hidden" name="plugin" value="vizzit-analytics-for-wordpress" />
        <input type="hidden" name="plugin-setting" value="page_schedule" />
        <?php

            $scheduler = wp_get_schedule( $this->e_schedule );
            $desc = ( $scheduler !== false ) ? ' Next run (' . $scheduler . '): ' .wp_next_scheduled( $this->e_schedule ). date( 'Y-m-d H:i:s', wp_next_scheduled( $this->e_schedule ) ) : 'Next run: currently disabled';#. ' TESTOUTPUT-IDENTITY ' .$options[ 'va_identity' ];
            $rows[] = array(
              'id' 		=> 'va_scheduler',
              'label' 	=> __( 'Schedule processing', VAWP_LOCALE_HOOK ),
              'desc' 	=> $desc,
              'content'	=> $this->form_checkbox( 'va_scheduler' )
            );

            $rows[] = array(
              'id' 		=> '',
              'label' 	=> __( 'Process Manually', VAWP_LOCALE_HOOK ),
              'desc' 	=> __( 'Start a manually processing directly by pressing this button. The page-structure will be processed and uploaded to Vizzit for further processing.', VAWP_LOCALE_HOOK ),
              'content' => '<input type="submit" class="button-secondary" name="submit-process-manually" value="' . __( 'Process Manually', VAWP_LOCALE_HOOK ) . '" />'
            );

          echo $this->form_table( $rows ) . $this->form_save_button();
        echo '</form>';

      // if processed manually, run the job and display result
      if( isset( $_POST[ 'submit-process-manually' ] ) ) {
        try {
          #$dir = dirname(__FILE__) . '/';
          #exec("cd $dir; php vizzit-analytics.cron.php MANUAL > /dev/null 2>/dev/null &1");
          $this->vizzit_analytics_process( 'MANUAL' );
        }
        catch (Exception $e) {
          // Fetch Exception information
          $message = $e->getMessage(); 
          $stacktrace = $e->getTraceAsString();

          // Fetch and set job information, as a failed job.
          $sequenceNumber = $this->get_last_sequence_number(false);
          $this->process_msg_count['error']++;

          // Print Exception in the scheduler
          echo '<br />';
          echo "<h2>Exception caught during manuall process for #$sequenceNumber: $message</h2>";
          echo "<pre>$stacktrace</pre>";

          // Log the exception in using extended debug logging
          $this->debug_log("Exception encountered while processing manually: $message");
          $this->debug_log("Stacktrace: $stacktrace");

          // Write job status to the database
          $this->process_history_insert(array('sequence_number' => $sequenceNumber, 'va_exec' => 'MANUAL'));
        }
      }

      echo '<br /><br /><hr />';
      echo '<h2>' . __( 'Vizzit Analytics Schedule History', VAWP_LOCALE_HOOK ) . '</h2>';

      // get the history rows from db-table (default limit = 10) - needs empty array, otherwise php warning
      echo $this->history_table( $this->get_schedule_history_rows( array() ) );

      echo '</div>';
    } // end settings_page_schedule()


    /**
     * Set default values for Vizzit_Analytics options - see class-variables
     */
    function set_defaults() {
      // set the defaults based on class-variable-definition
      $options = array_merge(
        $this->vizzit_analytics_options[ 'general' ],
        $this->vizzit_analytics_options[ 'page_tree_structure' ],
        $this->vizzit_analytics_options[ 'page_display_options' ],
        $this->vizzit_analytics_options[ 'page_schedule' ],
        array( 'va_version' => VAWP_VERSION ) // the version of the Vizzit Analytics Plugin
      );

      $this->update_options($options);

      return $options;
    } // end set_defaults()

  } // end class Vizzit_Analytics_Admin

}
?>
