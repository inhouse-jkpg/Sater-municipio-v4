<?php
/**
 * General plugin initialization
 *
 * @package SmartCrawl
 */

namespace SmartCrawl;

/**
 * Class Init.
 */
class Init {

	/**
	 * Inits plugin
	 *
	 * @return  void
	 */
	public function __construct() {
		$this->textdomain();
		$this->functions();
		$this->init();
	}

	/**
	 * Inits the class.
	 *
	 * @return void
	 */
	private function init() {
		$this->common();

		// Woocommerce integration.
		add_action( 'init', array( $this, 'woocommerce' ) );
		add_action( 'wpml_loaded', array( $this, 'wpml' ) );

		Redirects\Controller::get()->run();

		if ( is_admin() ) {
			$this->admin();
		} else {
			$this->front();
		}

		// Boot up the hub controller.
		Controllers\Hub::serve();
		Third_Party_Import\Controller::serve();
	}

	/**
	 * Inits common functionality.
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	private function common() {
		Cache\Manager::get()->run();
		Sitemaps\Front::get()->run();
		Sitemaps\Native::get()->run();
		Sitemaps\Controller::get()->run();
		Sitemaps\Troubleshooting::get()->run();
		Sitemaps\Dashboard_Widget::get()->run();
		SEOMoz\Metabox::get()->run();
		SEOMoz\Dashboard_Widget::get()->run();
		SEOMoz\Cron::get()->run();
		MaxMind\Cron::get()->run();
		Controllers\Cron::get()->run();
		Controllers\Compatibility::get()->run();
		Controllers\Analysis_Content::get()->run();
		Controllers\Robots::get()->run();
		Controllers\Data::get()->run();
		Controllers\Plugin_Links::get()->run();
		Admin\Admin::get()->run();
		Schema\Media::get()->run();
		Admin\Pages\Upgrade::get()->run();
		Schema\Types::get()->run();
		Schema\Printer::run();
		Lighthouse\Controller::get()->run();
		Configs\Controller::get()->run();
		Crawler\Controller::get()->run();
		Multisite\Network_Configs::get()->run();
		Multisite\Sitewide_Deprecation::get()->run();
		Controllers\Ajax_Search::get()->run();
		Autolinks\Autolinks::get()->run();
		Breadcrumbs\Controller::get()->run();
		Controllers\Primary_Terms::get()->run();
		Mixpanel\Sitemap::get()->run();
	}

	/**
	 * Inits admin side functionality.
	 *
	 * @since 3.5.2
	 *
	 * @return void
	 */
	private function admin() {
		Controllers\Recommended_Plugins::get()->run();
		Controllers\Dash_Notices::get()->run();
		Controllers\New_Feature::get()->run();
		Controllers\Onboard::get()->run();
		Controllers\Analysis::get()->run();
		Controllers\Assets::get()->run();
		Controllers\White_Label::get()->run();
		Controllers\Pointers::get()->run();
		Admin\Metabox::get()->run();
		Admin\Taxonomy::get()->run();
		Admin\Pages\Network_Settings::get()->run();
		Mixpanel\Dashboard::get()->run();
		Mixpanel\General::get()->run();
		Mixpanel\Schema::get()->run();
		Mixpanel\Tools::get()->run();
		Mixpanel\Schema::get()->run();
		Mixpanel\Modules::get()->run();
		MaxMind\MaxMind::get()->run();
		Controllers\Welcome::get()->run();
	}

	/**
	 * Inits public facing side functionality.
	 *
	 * @since 3.5.2
	 *
	 * @return void
	 */
	private function front() {
		Controllers\OnPage::get()->run();
		Social\Controller::get()->run();
		Front::get()->run();
		Controllers\Report_Permalinks::get()->run();
	}

	/**
	 * Inits Woocommerce integration.
	 *
	 * WooCommerce is not available before init when smartcrawl is activated
	 * on a sub-site (not network active).
	 *
	 * @since 3.5.2
	 *
	 * @return void
	 */
	public function woocommerce() {
		Woocommerce\Controller::get()->run();
	}

	/**
	 * Inits WPML integration.
	 *
	 * @since 3.7.2
	 *
	 * @return void
	 */
	public function wpml() {
		WPML\Controller::get()->run();
	}

	/**
	 * Inits public functions.
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	private function functions() {
		require_once SMARTCRAWL_PLUGIN_DIR . 'core/core.php';
	}

	/**
	 * Loads the plugin text domain.
	 *
	 * @since 3.5.2
	 *
	 * @return void
	 */
	private function textdomain() {
		if ( defined( '\WPMU_PLUGIN_DIR' ) && file_exists( \WPMU_PLUGIN_DIR . '/wpmu-dev-seo.php' ) ) {
			load_muplugin_textdomain( 'wds', dirname( \SMARTCRAWL_PLUGIN_BASENAME ) . '/languages' );
		} else {
			load_plugin_textdomain( 'wds', false, dirname( \SMARTCRAWL_PLUGIN_BASENAME ) . '/languages' );
		}
	}
}