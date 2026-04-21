<?php
/**
 * Advanced module settings
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Admin\Settings;

use SmartCrawl\Controllers\Assets;
use SmartCrawl\Controllers\Robots;
use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Services\Service;
use SmartCrawl\Controllers\White_Label;

/**
 * Init Advanced Settings
 */
class Advanced extends Admin_Settings {

	use Singleton;

	/**
	 * Validate submitted options
	 *
	 * @param array $input Raw input.
	 *
	 * @return array Validated input
	 */
	public function validate( $input ) {
		// Start with old values for all the options.
		$result = self::get_specific_options( $this->option_name );

		$save_woo = \smartcrawl_get_array_value( $input, 'save_woo' );
		if ( $save_woo ) {
			$woo_input = \smartcrawl_get_array_value( $input, 'woo-settings' );
			if ( $woo_input ) {
				$woo_data = new \SmartCrawl\Woocommerce\Data();
				$woo_data->save_data( json_decode( $woo_input, true ) );

				return $result;
			}
		}

		$save_redirects = isset( $input['save_redirects'] ) && $input['save_redirects'];
		if ( $save_redirects ) {
			$result['redirect-attachments']             = ! empty( $input['redirect-attachments'] );
			$result['redirect-attachments-images_only'] = ! empty( $input['redirect-attachments-images_only'] );

			if ( isset( $input['redirections-code'] ) ) {
				$this->validate_and_save_redirection_options( $input );
			}

			return $result;
		}

		if ( ! empty( $input['save_robots'] ) ) {
			$this->validate_and_save_robots_options( $input );

			return $result;
		}

		if ( ! empty( $input['save_breadcrumb'] ) ) {
			$this->validate_and_save_breadcrumb_options( $input );

			return $result;
		}

		$service = $this->get_site_service();

		if ( ! empty( $input['wds_autolinks-setup'] ) ) {
			$result['wds_autolinks-setup'] = true;
		}

		if ( $service->is_member() ) {
			// Booleans.
			$booleans = array(
				'comment',
				'onlysingle',
				'allowfeed',
				'casesens',
				'customkey_preventduplicatelink',
				'target_blank',
				'rel_nofollow',
				'allow_empty_tax',
				'excludeheading',
				'exclude_no_index',
				'exclude_image_captions',
				'disable_content_cache',
			);

			foreach ( $booleans as $bool ) {
				$result[ $bool ] = ! empty( $input[ $bool ] );
			}

			$result['insert']  = array();
			$result['link_to'] = array();
			$post_type_names   = array_keys( self::get_post_types() );
			if ( ! empty( $input['insert'] ) ) {
				// Accept only allowed types.
				$result['insert'] = array_intersect( (array) $input['insert'], array_merge( $post_type_names, array( 'comment', 'product_cat' ) ) );
			}
			if ( ! empty( $input['link_to'] ) ) {
				// Accept only allowed types.
				foreach ( $post_type_names as $post_type ) {
					if ( in_array( 'l' . $post_type, (array) $input['link_to'], true ) ) {
						$result['link_to'][] = 'l' . $post_type;
					}
				}
				foreach ( get_taxonomies() as $taxonomy ) {
					$tax = get_taxonomy( $taxonomy );
					$key = strtolower( $tax->labels->name );
					if ( in_array( 'l' . $key, (array) $input['link_to'], true ) ) {
						$result['link_to'][] = 'l' . $key;
					}
				}
			}

			// Numerics.
			$numeric = array(
				'cpt_char_limit',
				'tax_char_limit',
				'link_limit',
				'single_link_limit',
			);
			foreach ( $numeric as $num ) {
				if ( isset( $input[ $num ] ) ) {
					if ( is_numeric( $input[ $num ] ) ) {
						$result[ $num ] = (int) $input[ $num ];
					} elseif ( empty( $input[ $num ] ) ) {
						$result[ $num ] = '';
					} else {
						add_settings_error( $this->option_name, 'numeric-limits', __( 'Limit values must be numeric', 'wds' ) );
					}
				}
			}

			// Strings.
			$strings = array(
				'ignore',
				'ignorepost',
			);
			foreach ( $strings as $str ) {
				if ( isset( $input[ $str ] ) ) {
					$result[ $str ] = sanitize_text_field( $input[ $str ] );
				}
			}

			// Arrays.
			$arrays = array( 'excluded_urls' );
			foreach ( $arrays as $array_key ) {
				if ( isset( $input[ $array_key ] ) ) {
					// Remove empty values.
					$array_value = array_filter(
						(array) $input[ $array_key ],
						function ( $value ) {
							return ! empty( $value );
						}
					);
					// Remove duplicates.
					$array_value = array_unique( $array_value );
					// Sanitize values.
					$result[ $array_key ] = array_map( 'sanitize_text_field', $array_value );
				}
			}

			// Custom keywords, they need newlines.
			if ( isset( $input['customkey'] ) ) {
				$str                 = wp_check_invalid_utf8( $input['customkey'] );
				$str                 = wp_pre_kses_less_than( $str );
				$str                 = wp_strip_all_tags( $str );
				$result['customkey'] = $str;

				$found = false;
				while ( preg_match( '/%[a-f0-9]{2}/i', $str, $match ) ) {
					$str   = str_replace( $match[0], '', $str );
					$found = true;
				}
				if ( $found ) {
					$str = trim( preg_replace( '/ +/', ' ', $str ) );
				}
			}
		}

		$previous = Settings::get_component_options( $this->name );

		if ( isset( $input['disable-adv-tools'] ) ) {
			$result['disable-adv-tools'] = ! empty( $input['disable-adv-tools'] );
		} else {
			$result['disable-adv-tools'] = (bool) \smartcrawl_get_array_value( $previous, 'disable-adv-tools' );
		}

		do_action( 'smartcrawl_before_save_tools', $previous, $result );

		return $result;
	}

	/**
	 * Process extra options
	 *
	 * @param array $input Raw input.
	 */
	private function validate_and_save_redirection_options( $input ) {
		$settings                      = Settings::get_specific_options( 'wds_settings_options' );
		$settings['redirections-code'] = (int) $input['redirections-code'];
		Settings::update_specific_options( 'wds_settings_options', $settings );

		do_action( 'smartcrawl_after_save_redirects' );
	}

	/**
	 * Validate and save robots options.
	 *
	 * @param array $input Input.
	 *
	 * @return void
	 */
	private function validate_and_save_robots_options( $input ) {
		$robots_options = Settings::get_specific_options( 'wds_robots_options' );

		$robots_options['sitemap_directive_disabled'] = ! empty( $input['sitemap_directive_disabled'] );
		$robots_options['custom_sitemap_url']         = esc_url_raw( $input['custom_sitemap_url'] );
		$robots_options['custom_directives']          = sanitize_textarea_field( $input['custom_directives'] );

		Settings::update_specific_options( 'wds_robots_options', $robots_options );
	}

	/**
	 * Validate and save breadcrumb options.
	 *
	 * @param array $input Input.
	 *
	 * @return void
	 */
	private function validate_and_save_breadcrumb_options( $input ) {
		$breadcrumb_options = Settings::get_specific_options( 'wds_breadcrumb_options' );
		unset( $breadcrumb_options['save_breadcrumb'] );

		// Text fields.
		$inputs = array( 'separator', 'custom_sep', 'prefix', 'home_label' );
		foreach ( $inputs as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$breadcrumb_options[ $key ] = sanitize_text_field( $input[ $key ] );
			}
		}

		// Labels.
		$labels = array( 'post', 'page', 'archive', 'search', '404' );
		foreach ( $labels as $key ) {
			if ( isset( $input['labels'][ $key ] ) ) {
				$breadcrumb_options['labels'][ $key ] = \smartcrawl_sanitize_preserve_macros( $input['labels'][ $key ] );
			}
		}

		// Boolean fields.
		$booleans = array( 'home_trail', 'hide_post_title', 'add_prefix', 'disable_woo' );
		foreach ( $booleans as $key ) {
			$breadcrumb_options[ $key ] = isset( $input[ $key ] );
		}

		Settings::update_specific_options( 'wds_breadcrumb_options', $breadcrumb_options );
	}

	/**
	 * Gets site service instance.
	 *
	 * @return object
	 */
	private function get_site_service() {
		return Service::get( Service::SERVICE_SITE );
	}

	/**
	 * Static known public post types getter
	 *
	 * @return array A list of known post type *objects* keyed by name
	 */
	public static function get_post_types() {
		static $post_types;

		if ( empty( $post_types ) ) {
			$exclusions = array(
				'revision',
				'nav_menu_item',
				'attachment',
			);
			$raw        = get_post_types(
				array( 'public' => true ),
				'objects'
			);
			foreach ( $raw as $pt => $pto ) {
				if ( in_array( $pt, $exclusions, true ) ) {
					continue;
				}
				$post_types[ $pt ] = $pto;
			}
		}

		return is_array( $post_types )
			? $post_types
			: array();
	}

	/**
	 * Initializes the admin pane.
	 */
	public function init() {
		$this->option_name = 'wds_autolinks_options';
		$this->name        = Settings::COMP_AUTOLINKS;
		$this->slug        = Settings::TAB_AUTOLINKS;
		$this->action_url  = admin_url( 'options.php' );
		$this->page_title  = __( 'SmartCrawl Wizard: Advanced Tools', 'wds' );

		add_action( 'wp_ajax_wds-load_exclusion-post_data', array( $this, 'json_load_post' ) );
		add_action( 'wp_ajax_wds-load_exclusion_posts-posts_data-specific', array( $this, 'json_load_posts_specific' ) );
		add_action( 'wp_ajax_wds-load_exclusion_posts-posts_data-paged', array( $this, 'json_load_posts_paged' ) );
		add_action( 'admin_init', array( $this, 'reset_moz_api_credentials' ) );
		add_action( 'admin_init', array( $this, 'deactivate_components' ) );

		parent::init();
	}

	/**
	 * Get the title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Advanced Tools', 'wds' );
	}

	/**
	 * Resets Moz API creds.
	 *
	 * TODO: probably need to move this to the same location as save_moz_api_credentials
	 */
	public function reset_moz_api_credentials() {
		$post_data = $this->get_request_data();
		if ( isset( $post_data['reset-moz-credentials'] ) ) { // Just a presence flag.
			$options               = self::get_specific_options( 'wds_settings_options' );
			$options['access-id']  = '';
			$options['secret-key'] = '';
			self::update_specific_options( 'wds_settings_options', $options );

			wp_safe_redirect( esc_url_raw( add_query_arg( array() ) ) );
			die;
		}
	}

	/**
	 * Deactivate components.
	 *
	 * @return void
	 */
	public function deactivate_components() {
		$data = isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], $this->option_name . '-options' ) // phpcs:ignore
			? stripslashes_deep( $_POST )
			: array();

		$redirect_url = wp_get_referer();
		if ( isset( $data['deactivate-autolinks-component'] ) ) {
			Settings::deactivate_component( 'autolinks' );
			wp_safe_redirect( $redirect_url );
			die();
		}

		if ( isset( $data['deactivate-robots-component'] ) ) {
			Settings::deactivate_component( 'robots-txt' );
			wp_safe_redirect( $redirect_url );
			die();
		}

		if ( isset( $data['deactivate-breadcrumb-component'] ) ) {
			Settings::deactivate_component( 'breadcrumb' );
			wp_safe_redirect( $redirect_url );
			die();
		}
	}

	/**
	 * Loads Individual post data
	 *
	 * Outputs AJAX response
	 */
	public function json_load_post() {
		$post_data = $this->get_request_data();
		$result    = array(
			'id'    => 0,
			'title' => '',
			'type'  => '',
		);
		if ( ! current_user_can( 'edit_others_posts' ) || empty( $post_data ) ) {
			wp_send_json( $result );
		}

		$post_id = ! empty( $post_data['id'] ) && is_numeric( $post_data['id'] )
			? (int) $post_data['id']
			: false;
		if ( empty( $post_id ) ) {
			wp_send_json( $result );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json( $result );
		}

		wp_send_json( $this->post_to_response_data( $post ) );
	}

	/**
	 * Makes the post response format uniform
	 *
	 * @param object $post WP_Post instance.
	 *
	 * @return array Post response hash
	 */
	private function post_to_response_data( $post ) {
		$result = array(
			'id'    => 0,
			'title' => '',
			'type'  => '',
			'date'  => '',
		);
		if ( empty( $post ) || empty( $post->ID ) ) {
			return $result;
		}
		static $date_format;

		if ( empty( $date_format ) ) {
			$date_format = get_option( 'date_format' );
		}

		$post_id         = $post->ID;
		$result['id']    = $post_id;
		$result['title'] = get_the_title( $post_id );
		$result['type']  = get_post_type( $post_id );
		$result['date']  = get_post_time( $date_format, false, $post_id );

		return $result;
	}

	/**
	 * Loads posts by specific IDs
	 *
	 * Outputs AJAX response
	 */
	public function json_load_posts_specific() {
		$post_data = $this->get_request_data();
		$result    = array(
			'meta'  => array(),
			'posts' => array(),
		);
		if ( ! current_user_can( 'edit_others_posts' ) || empty( $post_data ) ) {
			wp_send_json( $result );
		}

		$post_ids = ! empty( $post_data['posts'] ) && is_array( $post_data['posts'] )
			? array_values( array_filter( array_map( 'intval', $post_data['posts'] ) ) )
			: array();
		if ( empty( $post_ids ) ) {
			wp_send_json_success( $result );
		}

		$args = array(
			'post_status'         => 'publish',
			'posts_per_page'      => - 1,
			'post__in'            => $post_ids,
			'orderby'             => 'post__in',
			'ignore_sticky_posts' => true,
			'post_type'           => 'any',
		);

		$query = new \WP_Query( $args );

		$result['meta'] = array(
			'total' => $query->max_num_pages,
			'page'  => 1,
		);

		foreach ( $query->posts as $post ) {
			if ( ! empty( $post->ID ) ) {
				$result['posts'][ $post->ID ] = $this->post_to_response_data( $post );
			}
		}

		wp_send_json_success( $result );
	}

	/**
	 * Loads paged posts of certain type
	 *
	 * Outputs AJAX response
	 */
	public function json_load_posts_paged() {
		$request_data = isset( $_GET['_wds_nonce'] ) && wp_verify_nonce( $_GET['_wds_nonce'], 'wds-autolinks-nonce' ) ? $_GET : array(); // phpcs:ignore
		$result       = array(
			'meta'  => array(),
			'posts' => array(),
		);
		if ( ! current_user_can( 'edit_others_posts' ) || empty( $request_data ) ) {
			wp_send_json( $result );
		}
		$args = array(
			'post_status'         => 'publish',
			'posts_per_page'      => 10,
			'ignore_sticky_posts' => true,
		);
		$page = 1;
		if ( ! empty( $request_data['type'] ) && in_array( $request_data['type'], array_keys( self::get_post_types() ), true ) ) {
			$args['post_type'] = sanitize_key( $request_data['type'] );
		}
		if ( ! empty( $request_data['term'] ) ) {
			$args['s'] = sanitize_text_field( $request_data['term'] );
		}
		if ( ! empty( $request_data['page'] ) && is_numeric( $request_data['page'] ) ) {
			$args['paged'] = (int) $request_data['page'];
			$page          = $args['paged'];
		}

		$query = new \WP_Query( $args );

		$result['meta'] = array(
			'total' => $query->max_num_pages,
			'page'  => $page,
		);

		foreach ( $query->posts as $post ) {
			$result['posts'][] = $this->post_to_response_data( $post );
		}

		wp_send_json( $result );
	}

	/**
	 * Add admin settings page
	 */
	public function options_page() {
		parent::options_page();

		$arguments = array(
			'active_tab'      => $this->get_active_tab( 'tab_automatic_linking' ),
			'already_exists'  => Robots::get()->file_exists(),
			'rootdir_install' => Robots::get()->is_rootdir_install(),
		);

		wp_enqueue_script( Assets::AUTOLINKS_PAGE_JS );

		$options = Settings::get_component_options( self::COMP_AUTOLINKS );

		$post_types = array(
			'url' => __( 'URL', 'wds' ),
		);

		foreach ( self::get_post_types() as $type ) {
			$key                = strtolower( $type->name );
			$post_types[ $key ] = $type->labels->name;
		}

		$image_url = sprintf( '%s/assets/images/empty-box.svg', SMARTCRAWL_PLUGIN_URL );
		$image_url = White_Label::get()->get_wpmudev_hero_image( $image_url );

		$args = array(
			'option_name'     => $this->option_name,
			'insert_options'  => $this->get_insert_keys(),
			'link_to_options' => $this->get_linkto_keys(),
			'nonce'           => wp_create_nonce( 'wds-autolinks-nonce' ),
			'post_types'      => $post_types,
			'enabled'         => Settings::get_setting( 'autolinks' ) && $this->get_site_service()->is_member(),
			'image'           => $image_url,
		);

		foreach (
			array(
				'insert',
				'link_to',
				'customkey',
				'ignore',
				'customkey',
				'ignore',
				'ignorepost',
				'cpt_char_limit',
				'tax_char_limit',
				'link_limit',
				'single_link_limit',
			) as $value
		) {
			$args[ $value ] = \smartcrawl_get_array_value(
				$options,
				$value
			);
		}

		$additional = array(
			'allow_empty_tax'                => array(
				'label'       => esc_html__( 'Allow autolinks to empty taxonomies', 'wds' ),
				'description' => esc_html__( 'Allows autolinking to taxonomies that have no posts assigned to them.', 'wds' ),
			),
			'excludeheading'                 => array(
				'label'       => esc_html__( 'Prevent linking in heading tags', 'wds' ),
				'description' => esc_html__( 'Excludes headings from autolinking.', 'wds' ),
			),
			'onlysingle'                     => array(
				'label'       => esc_html__( 'Process only single posts and pages', 'wds' ),
				'description' => esc_html__( 'Process only single posts and pages', 'wds' ),
			),
			'allowfeed'                      => array(
				'label'       => esc_html__( 'Process RSS feeds', 'wds' ),
				'description' => esc_html__( 'Autolinking will also occur in RSS feeds.', 'wds' ),
			),
			'casesens'                       => array(
				'label'       => esc_html__( 'Case sensitive matching', 'wds' ),
				'description' => esc_html__( 'Only autolink the exact string match.', 'wds' ),
			),
			'customkey_preventduplicatelink' => array(
				'label'       => esc_html__( 'Prevent duplicate links', 'wds' ),
				'description' => esc_html__( 'Only link to a specific URL once per page/post.', 'wds' ),
			),
			'target_blank'                   => array(
				'label'       => esc_html__( 'Open links in new tab', 'wds' ),
				'description' => esc_html__( 'Adds the target=“_blank” tag to links to open a new tab when clicked.', 'wds' ),
			),
			'rel_nofollow'                   => array(
				'label'       => esc_html__( 'Nofollow autolinks', 'wds' ),
				'description' => esc_html__( 'Adds the nofollow meta tag to autolinks to prevent search engines following those URLs when crawling your website.', 'wds' ),
			),
			'exclude_no_index'               => array(
				'label'       => esc_html__( 'Prevent linking on no-index pages', 'wds' ),
				'description' => esc_html__( 'Prevent autolinking on no-index pages.', 'wds' ),
			),
			'exclude_image_captions'         => array(
				'label'       => esc_html__( 'Prevent linking on image captions', 'wds' ),
				'description' => esc_html__( 'Prevent links from being added to image captions.', 'wds' ),
			),
			'disable_content_cache'          => array(
				'label'       => esc_html__( 'Prevent caching for autolinked content', 'wds' ),
				'description' => esc_html__( 'Some page builder plugins and themes conflict with object cache when automatic linking is enabled. Enable this option to disable object cache for autolinked content.', 'wds' ),
			),
		);

		foreach ( $additional as $key => $value ) {
			if ( isset( $options[ $key ] ) ) {
				$additional[ $key ]['value'] = $options[ $key ];
			}
		}

		$args['additional']            = $additional;
		$args['default_redirect_type'] = \SmartCrawl\Redirects\Utils::get()->get_default_type();

		wp_localize_script(
			Assets::AUTOLINKS_PAGE_JS,
			'_wds_autolinks',
			$args
		);

		$breadcrumb_options = Settings::get_component_options( 'breadcrumb' );
		if ( ! is_array( $breadcrumb_options ) ) {
			$breadcrumb_options = array();
		}

		$args = array(
			'nonce'          => wp_create_nonce( 'wds-breadcrumb-nonce' ),
			'enabled'        => ! ! Settings::get_setting( 'breadcrumb' ),
			'image'          => $image_url,
			'settings_nonce' => wp_create_nonce( 'wds-settings-nonce' ),
			'referer'        => add_query_arg( 'tab', 'tab_breadcrumb', remove_query_arg( '_wp_http_referer' ) ),
			'option_name'    => $this->option_name,
			'separator'      => isset( $breadcrumb_options['separator'] ) ? $breadcrumb_options['separator'] : 'greater-than',
			'separators'     => \smartcrawl_get_separators(),
			'options'        => $this->get_breadcrumb_options(),
			'home_label'     => isset( $breadcrumb_options['home_label'] ) ? $breadcrumb_options['home_label'] : '',
			'home_page'      => array(
				'label' => __( 'Home', 'wds' ),
				'url'   => get_home_url(),
			),
		);

		$breadcrumb_configs = array(
			'add_prefix'      => array(
				'label'       => esc_html__( 'Add Prefix to Breadcrumbs', 'wds' ),
				'description' => esc_html__( 'Enable this option to include a prefix at the beginning of the breadcrumbs.', 'wds' ),
			),
			'home_trail'      => array(
				'label'       => esc_html__( 'Add homepage to the breadcrumbs trail', 'wds' ),
				'description' => esc_html__( 'Enable this option to add the homepage to the breadcrumbs.', 'wds' ),
			),
			'hide_post_title' => array(
				'label'       => esc_html__( 'Hide Post Title', 'wds' ),
				'description' => esc_html__( 'Enable this option to hide the post title from the breadcrumbs trail.', 'wds' ),
			),
			'disable_woo'     => array(
				'label'       => esc_html__( 'Disable WooCommerce Breadcrumbs', 'wds' ),
				'description' => esc_html__( 'Enable this option to hide the default WooCommerce product breadcrumbs from your site.', 'wds' ),
			),

		);

		foreach ( $breadcrumb_configs as $key => $value ) {
			if ( isset( $breadcrumb_options[ $key ] ) ) {
				$breadcrumb_configs[ $key ]['value'] = $breadcrumb_options[ $key ];
			}
		}

		$args['configs']    = $breadcrumb_configs;
		$args['prefix']     = isset( $breadcrumb_options['prefix'] ) ? $breadcrumb_options['prefix'] : '';
		$args['custom_sep'] = isset( $breadcrumb_options['custom_sep'] ) ? $breadcrumb_options['custom_sep'] : '';

		wp_localize_script(
			Assets::AUTOLINKS_PAGE_JS,
			'_wds_breadcrumb',
			$args
		);

		$this->render_page( 'advanced-tools/advanced-tools-settings', $arguments );
	}

	/**
	 * Default settings
	 */
	public function defaults() {
		$this->options = get_option( $this->option_name );

		if ( ! is_array( $this->options ) ) {
			$this->options = array();
		}

		if ( empty( $this->options['ignorepost'] ) ) {
			$this->options['ignorepost'] = '';
		}

		if ( empty( $this->options['ignore'] ) ) {
			$this->options['ignore'] = '';
		}

		if ( empty( $this->options['customkey'] ) ) {
			$this->options['customkey'] = '';
		}

		if ( empty( $this->options['cpt_char_limit'] ) ) {
			$this->options['cpt_char_limit'] = '';
		}

		if ( empty( $this->options['tax_char_limit'] ) ) {
			$this->options['tax_char_limit'] = '';
		}

		if ( ! isset( $this->options['link_limit'] ) ) {
			$this->options['link_limit'] = '';
		}

		if ( ! isset( $this->options['single_link_limit'] ) ) {
			$this->options['single_link_limit'] = '';
		}

		update_option( $this->option_name, $this->options );
	}

	/**
	 * Get the request data.
	 *
	 * @return array
	 */
	private function get_request_data() {
		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wds_nonce'] ) ), 'wds-autolinks-nonce' )
			? $_POST :
			array();
	}

	/**
	 * Get the insert options.
	 *
	 * @return array
	 */
	public function get_insert_options() {
		$options = Settings::get_component_options( self::COMP_AUTOLINKS );
		$result  = array();
		foreach ( $this->get_insert_keys() as $key => $label ) {
			$result[ $key ] = array(
				'label' => $label,
				'value' => ! empty( $options[ $key ] ),
			);
		}

		return $result;
	}

	/**
	 * Get the insert keys.
	 *
	 * @return array
	 */
	private function get_insert_keys() {
		// Add post types.
		foreach ( self::get_post_types() as $post_type => $pt ) {
			$key = strtolower( $pt->name );

			$insert[ $key ] = $pt->labels->name;
		}
		// Add comments.
		$insert['comment'] = __( 'Comments', 'wds' );

		// Add Woo Product category.
		if ( taxonomy_exists( 'product_cat' ) ) {
			$taxonomy = get_taxonomy( 'product_cat' );
			// Add product category.
			$insert['product_cat'] = empty( $taxonomy->label ) ? __( 'Product Categories', 'wds' ) : $taxonomy->label;
		}

		return $insert;
	}

	/**
	 * Get link to options.
	 *
	 * @return array
	 */
	public function get_linkto_options() {
		$options = Settings::get_component_options( self::COMP_AUTOLINKS );
		$result  = array();

		foreach ( $this->get_linkto_keys() as $key => $label ) {
			$result[ $key ] = array(
				'label' => $label,
				'value' => ! empty( $options[ $key ] ),
			);
		}

		return $result;
	}

	/**
	 * Get link to keys.
	 *
	 * @return array
	 */
	private function get_linkto_keys() {
		$post_types = array();
		foreach ( self::get_post_types() as $post_type => $pt ) {
			$key                      = strtolower( $pt->name );
			$post_types[ 'l' . $key ] = $pt->labels->name;
		}

		$taxonomies = array();
		foreach ( get_taxonomies( array( 'public' => true ) ) as $taxonomy ) {
			if ( ! in_array( $taxonomy, array( 'nav_menu', 'link_category', 'post_format' ), true ) ) {
				$tax = get_taxonomy( $taxonomy );
				$key = strtolower( $tax->labels->name );

				$taxonomies[ 'l' . $key ] = $tax->labels->name;
			}
		}

		return array_merge( $post_types, $taxonomies );
	}

	/**
	 * Get all available breadcrumb types.
	 *
	 * @return array
	 */
	private function get_breadcrumb_options() {
		$options = Settings::get_component_options( 'breadcrumb' );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return array(
			array(
				'type'        => 'post',
				'label'       => __( 'Post', 'wds' ),
				'snippets'    => array( 'Category', 'Subcategory' ),
				'value'       => isset( $options['labels']['post'] ) ? $options['labels']['post'] : '%%title%%',
				'placeholder' => __( '%%title%%', 'wds' ),
				'variables'   => array_merge(
					$this->get_macros( 'post' ),
					$this->get_general_macros()
				),
			),
			array(
				'type'        => 'page',
				'label'       => __( 'Page', 'wds' ),
				'snippets'    => array( 'Parent' ),
				'value'       => isset( $options['labels']['page'] ) ? $options['labels']['page'] : '%%title%%',
				'placeholder' => __( '%%title%%', 'wds' ),
				'variables'   => array_merge(
					$this->get_macros( 'page' ),
					$this->get_general_macros()
				),
			),
			array(
				'type'        => 'archive',
				'label'       => __( 'Archive', 'wds' ),
				'title'       => __( 'Archive Page', 'wds' ),
				'snippets'    => array(),
				'value'       => isset( $options['labels']['archive'] ) ? $options['labels']['archive'] : __( 'Archives for %%original-title%%', 'wds' ),
				'placeholder' => __( 'Archives for %%original-title%%', 'wds' ),
				'variables'   => array_merge(
					$this->get_macros( 'archive' ),
					$this->get_general_macros(),
					$this->get_pagination_macros()
				),
			),
			array(
				'type'        => 'search',
				'label'       => __( 'Search', 'wds' ),
				'title'       => __( 'Search Results Page', 'wds' ),
				'snippets'    => array(),
				'value'       => isset( $options['labels']['search'] ) ? $options['labels']['search'] : __( "Search for '%%searchphrase%%'", 'wds' ),
				'placeholder' => __( 'Search for "%%searchphrase%%"', 'wds' ),
				'variables'   => array_merge(
					$this->get_macros( 'search' ),
					$this->get_general_macros(),
					$this->get_pagination_macros()
				),
			),
			array(
				'type'        => '404',
				'label'       => __( '404', 'wds' ),
				'title'       => __( '404 Error Page', 'wds' ),
				'snippets'    => array(),
				'value'       => isset( $options['labels']['404'] ) ? $options['labels']['404'] : __( '404 Error: page not found', 'wds' ),
				'placeholder' => __( '404 Error: page not found', 'wds' ),
				'variables'   => array_merge(
					$this->get_macros( '404' ),
					$this->get_general_macros()
				),
			),
		);
	}

	/**
	 * Get breadcrumb macros.
	 *
	 * @param string $type Breadcrumb type.
	 *
	 * @return array
	 */
	public function get_macros( $type = '' ) {
		$post_type_macros = array(
			'%%id%%'       => __( 'ID', 'wds' ),
			'%%title%%'    => __( 'Title', 'wds' ),
			'%%modified%%' => __( 'Modified Time', 'wds' ),
			'%%date%%'     => __( 'Date', 'wds' ),
			'%%name%%'     => __( 'Author Nicename', 'wds' ),
			'%%userid%%'   => __( 'Author Userid', 'wds' ),
		);

		switch ( $type ) {
			case 'post':
				$post_type_macros['%%category%%'] = __( 'Categories (comma separated)', 'wds' );
				$post_type_macros['%%tag%%']      = __( 'Tags', 'wds' );

				foreach ( $post_type_macros as $macro => $label ) {
					$post_type_macros[ $macro ] = sprintf( 'Post %s', $label );
				}

				return array_merge( $post_type_macros, $this->get_general_macros() );
			case 'page':
				foreach ( $post_type_macros as $macro => $label ) {
					$post_type_macros[ $macro ] = sprintf( 'Page %s', $label );
				}

				return array_merge( $post_type_macros, $this->get_general_macros() );
			case 'archive':
				return array_merge(
					array(
						'%%original-title%%' => __( 'Archive Title ( no prefix )', 'wds' ),
						'%%archive-title%%'  => __( 'Archive Title', 'wds' ),
					),
					$this->get_general_macros()
				);
			case 'search':
				return array_merge(
					array(
						'%%searchphrase%%' => __( 'Search Keyword', 'wds' ),
					),
					$this->get_general_macros()
				);
			default:
				return $this->get_general_macros();
		}
	}

	/**
	 * Get general macros.
	 *
	 * @return array
	 */
	private function get_general_macros() {
		return array(
			'%%sep%%'          => __( 'Separator', 'wds' ),
			'%%sitename%%'     => __( "Site's name", 'wds' ),
			'%%sitedesc%%'     => __( "Site's tagline / description", 'wds' ),
			'%%currenttime%%'  => __( 'Current time', 'wds' ),
			'%%currentdate%%'  => __( 'Current date', 'wds' ),
			'%%currentmonth%%' => __( 'Current month', 'wds' ),
			'%%currentyear%%'  => __( 'Current year', 'wds' ),
		);
	}

	/**
	 * Get pagination macros.
	 *
	 * @since 3.5.0
	 *
	 * @return array
	 */
	private function get_pagination_macros() {
		return array(
			'%%page%%'             => __( 'Current page number (i.e. page 2 of 4)', 'wds' ),
			'%%pagetotal%%'        => __( 'Current page total', 'wds' ),
			'%%pagenumber%%'       => __( 'Current page number', 'wds' ),
			'%%spell_pagenumber%%' => __( 'Current page number, spelled out as numeral in English', 'wds' ),
			'%%spell_pagetotal%%'  => __( 'Current page total, spelled out as numeral in English', 'wds' ),
			'%%spell_page%%'       => __( 'Current page number, spelled out as numeral in English', 'wds' ),
		);
	}
}
