<?php
/**
 * Bootstrap methods in the GFAddon class.
 *
 * Including _construct(), bootstrap(), init() and admin_init().
 *
 * @since   1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */

namespace CosmicGiant\Plugin_Skeleton\GF_Addon\Traits;

/**
 * Bootstrap methods in the GFAddon class.
 *
 * Including _construct(), bootstrap(), init() and admin_init().
 *
 * @since   1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */
trait Bootstrap {

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// The boostrap() method is only available in Gravity Forms 2.5+. Set the minimum version to 2.5.
		$this->_min_gravityforms_version = '2.5';

		parent::__construct();

	}

	/**
	 * Attaches any filters or actions needed to bootstrap the addon.
	 *
	 * @since 1.0
	 */
	public function bootstrap() {

		parent::bootstrap();

		add_action( 'init', [ $this, 'updater' ], 0 );

	}

	/**
	 * Register needed hooks.
	 *
	 * @since  1.0
	 */
	public function init() {

		parent::init();

		add_filter( 'auto_update_plugin', [ $this, 'maybe_auto_update' ], 10, 2 );

	}

	/**
	 * Register needed hooks.
	 *
	 * @since 1.0
	 */
	public function init_admin() {

		parent::init_admin();

		remove_action( 'after_plugin_row_' . $this->get_path(), array( $this, 'plugin_row' ), 10, 2 );

		if ( isset( $this->_min_gravityforms_version ) && RG_CURRENT_PAGE == 'plugins.php' && false === $this->_enable_rg_autoupgrade ) {
			add_action( 'after_plugin_row_' . $this->_path, [ $this, 'action_after_plugin_row' ], 10 );
		}

		if ( function_exists( 'members_register_cap_group' ) ) {
			remove_filter( 'members_get_capabilities', [ $this, 'members_get_capabilities' ] );
			add_action( 'members_register_cap_groups', [ $this, 'members_register_cap_group' ] );
			add_action( 'members_register_caps', [ $this, 'members_register_caps' ] );
		}

	}

	/**
	 * Returns the physical path of the plugins root folder.
	 *
	 * @since 1.0
	 *
	 * @param string $full_path The full path.
	 *
	 * @return string
	 */
	public function get_base_path( $full_path = '' ) {

		return WP_PLUGIN_DIR . '/' . dirname( $this->get_base_name() );

	}

	/**
	 * Returns the url of the root folder of the current Add-On.
	 *
	 * @since 1.0
	 *
	 * @param string $full_path Optional. The full path the plugin file.
	 *
	 * @return string
	 */
	public function get_base_url( $full_path = '' ) {

		return plugins_url( '', $this->get_base_name() );

	}

	/**
	 * Get the EDD item ID.
	 *
	 * @since 1.0
	 *
	 * @return int
	 */
	protected function get_base_name() {

		return defined( $this->base_name() ) ? constant( $this->base_name() ) : '';

	}

	/**
	 * Get the plugin base name constant name.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	abstract protected function base_name();

}
