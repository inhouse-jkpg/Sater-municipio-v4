<?php
/**
 * Compose the plugin page.
 *
 * @since 1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */

namespace CosmicGiant\Plugin_Skeleton\GF_Addon\Traits;

use GFCommon;
use Gravity_Forms\Gravity_Forms\Settings\Settings as Settings_API;

/**
 * Compose the plugin page.
 *
 * @since 1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */
trait Plugin_Page {

	/**
	 * Initialize plugin page.
	 * Displays plugin settings.
	 *
	 * @since  1.0
	 */
	public function plugin_page() {

		// Display plugin page header.
		$this->plugin_page_header();

		// Get current subview.
		$subview = $this->get_current_subview( false );

		// Display current subview if exists.
		if ( is_callable( $subview['callback'] ) ) {
			call_user_func( $subview['callback'] );
		} else {
			die( esc_html__( 'This subview does not exist.', 'plugin_skeleton' ) );
		}

		// Display plugin page footer.
		$this->plugin_page_footer();

	}

	/**
	 * Remove plugin page container for Gravity Forms 2.5.
	 *
	 * @since 1.0
	 */
	public function plugin_page_container() {

		$this->plugin_page();

	}

	/**
	 * Prevent plugin settings page from appearing on Gravity Forms settings page.
	 *
	 * @since  1.0
	 */
	public function plugin_settings_init() {

		// If this is not the plugin settings page, exit.
		if ( rgget( 'page' ) !== $this->_slug && rgget( 'subview' ) !== 'settings' ) {
			return;
		}

		// Get fields.
		$sections = $this->plugin_settings_fields();
		$sections = $this->prepare_settings_sections( $sections, 'plugin_settings' );

		// Initialize new settings renderer.
		$renderer = new Settings_API(
			[
				'capability'     => $this->_capabilities_settings_page,
				'fields'         => $sections,
				'initial_values' => $this->get_plugin_settings(),
				'save_callback'  => [ $this, 'update_plugin_settings' ],
			]
		);

		// Save renderer to instance.
		$this->set_settings_renderer( $renderer );

		if ( $this->get_settings_renderer()->is_save_postback() && rgget( 'page' ) === $this->_slug && ( ! rgget( 'subview' ) || rgget( 'subview' ) === 'settings' ) ) {
			$this->get_settings_renderer()->process_postback();
		}

	}

	/**
	 * Prepare plugin settings fields.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {

		return [
			[
				'id'       => $this->get_slug() . '-license',
				'title'    => esc_html__( 'License', 'cosmicgiant' ),
				'sections' => [
					[
						'title'  => sprintf( esc_html__( '%s Settings', 'cosmicgiant' ), $this->get_short_title() ),
						'fields' => $this->get_license_settings_fields(),
					],
				],
			],
		];

	}

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_menu_icon() {

		return file_get_contents( $this->get_base_path() . '/dist/images/menu-icon.svg' );

	}

	/**
	 * Plugin page header.
	 *
	 * @since 1.0
	 *
	 * @param string $title Page title.
	 */
	protected function plugin_page_header( $title = '' ) {

		// Print admin styles.
		wp_print_styles( [ 'jquery-ui-styles', 'gform_admin', 'gform_settings' ] );

		// Get subviews.
		$subviews = $this->get_subviews();

		// Prepare needed variables.
		$browser_class = esc_attr( GFCommon::get_browser_class() );
		$logo_url      = esc_url( $this->get_base_url() . '/dist/images/logo.svg' );
		$product_name  = $this->_title;
		$subview       = sprintf( 'tab_%s', esc_attr( $this->get_current_subview() ) );

		$navigation = '';
		foreach ( $subviews as $view ) {

			// Initialize URL query params.
			$query = [ 'subview' => $view['name'] ];

			// Add subview query params, if set.
			if ( isset( $view['query'] ) ) {
				$query = array_merge( $query, $view['query'] );
			}

			// Prepare subview URL.
			$view_url = add_query_arg( $query, admin_url( 'admin.php?page=' . $this->get_slug() ) );

			// Get tab icon.
			$icon_markup = method_exists( 'GFCommon', 'get_icon_markup' ) ? GFCommon::get_icon_markup( $view, 'dashicons-admin-generic' ) : '';

			$navigation .= sprintf(
				'<a href="%s"%s><span class="icon">%s</span> <span class="label">%s</span></a>',
				esc_url( $view_url ),
				$this->get_current_subview() === $view['name'] ? ' class="active"' : '',
				$icon_markup, // phpcs:ignore
				esc_html( $view['label'] )
			);
		}

		// phpcs:disable
		echo <<<HTML
		<div class="wrap {$browser_class}">

			<header class="gform-settings-header gform-settings-header--{$this->_slug}">
				<div class="gform-settings__wrapper">
					<img src="{$logo_url}" alt="{$product_name}" height="60">
				</div>
			</header>

			<div class="gform-settings__wrapper">

			<nav class="gform-settings__navigation">
				{$navigation}
			</nav>

		<div class="gform-settings__content" id="{$subview}">
HTML;
		// phpcs:enable

	}

	/**
	 * Plugin page footer.
	 *
	 * @since  1.0
	 */
	protected function plugin_page_footer() {

		$this->app_tab_page_footer();

	}

	/**
	 * Get current plugin page subview.
	 *
	 * @since  1.0
	 *
	 * @param bool $return_name Return only subview name.
	 *
	 * @return string|array|bool
	 */
	protected function get_current_subview( $return_name = true ) {

		// Get subviews.
		$subviews = $this->get_subviews();

		// Get current subview.
		$current_subview = rgempty( 'subview', $_GET ) ? $subviews[0]['name'] : rgget( 'subview' );

		// If returning name, return.
		if ( $return_name ) {
			return $current_subview;
		}

		// Loop through subviews.
		foreach ( $subviews as $subview ) {

			// If this is the current subview, return.
			if ( $current_subview === $subview['name'] ) {
				return $subview;
			}

		}

		return false;

	}

	/**
	 * Get plugin page subviews.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	protected function get_subviews() {

		// Initialize subviews.
		$subviews = [
			[
				'name'     => 'settings',
				'icon'     => $this->get_base_url() . '/includes/vendor/forgravity/gf-addon-skeleton/dist/images/menu/settings.svg',
				'label'    => esc_html__( 'Settings', 'plugin_skeleton' ),
				'callback' => [ $this, 'plugin_settings_page' ],
			],
		];

		return $subviews;

	}

}
