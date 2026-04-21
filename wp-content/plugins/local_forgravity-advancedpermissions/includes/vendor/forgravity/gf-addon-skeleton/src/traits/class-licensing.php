<?php
/**
 * Common licensing methods.
 *
 * @since 1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */

namespace CosmicGiant\Plugin_Skeleton\Traits;

use CosmicGiant\Plugin_Skeleton\EDD_SL_Plugin_Updater;
use CosmicGiant\Plugin_Skeleton\Traits\Licensing\Settings as Settings_Trait;

use GFCache;

/**
 * Common licensing methods.
 *
 * @since 1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */
trait Licensing {

	use Settings_Trait;

	/**
	 * Initialize plugin updater.
	 *
	 * @since 1.0
	 */
	public function updater() {

		// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
			return;
		}

		new EDD_SL_Plugin_Updater(
			$this->get_store_url(),
			$this->get_plugin_file(),
			array(
				'version' => $this->get_addon_version(),
				'license' => $this->get_license_key(),
				'item_id' => $this->get_edd_item_id(),
				'author'  => 'CosmicGiant',
			)
		);

	}





	// # PLUGINS LIST --------------------------------------------------------------------------------------------------

	/**
	 * Display activate license message on Plugins list page.
	 *
	 * @since 1.0
	 *
	 * @param string $plugin_name The plugin filename.
	 */
	public function action_after_plugin_row( $plugin_name ) {

		// Get license key.
		$license_key = $this->get_license_key();

		// If no license key is installed, display message.
		if ( rgblank( $license_key ) ) {

			// Prepare message.
			$message = sprintf(
				esc_html__( '%2$sRegister your copy%4$s of %1$s to receive access to automatic upgrades and support. Need a license key? %3$sPurchase one now.%4$s', 'cosmicgiant' ),
				$this->get_short_title(),
				'<a href="' . admin_url( 'admin.php?page=gf_settings&subview=' . $this->_slug ) . '">',
				'<a href="' . esc_url( $this->_url ) . '" target="_blank">',
				'</a>'
			);

		} else {

			// Get license data.
			$license_data = $this->check_license( $license_key );

			// If license key is invalid, display message.
			if ( empty( $license_data ) || 'valid' !== $license_data->license ) {

				// Prepare message.
				$message = sprintf(
					esc_html__( 'Your license is invalid or expired. %1$sEnter a valid license key%2$s or %3$spurchase a new one.%4$s', 'cosmicgiant' ),
					'<a href="' . admin_url( 'admin.php?page=gf_settings&subview=' . $this->_slug ) . '">',
					'</a>',
					'<a href="' . esc_url( $this->_url ) . '" target="_blank">',
					'</a>'
				);

			}

		}

		// If there is no message to display, exit.
		if ( ! isset( $message ) ) {
			return;
		}

		// Get active class.
		$active_class = ( is_network_admin() && is_plugin_active_for_network( $plugin_name ) ) || ( ! is_network_admin() && is_plugin_active( $plugin_name ) ) ? ' active' : '';

		// Display plugin message.
		printf(
			'<tr class="plugin-update-tr%3$s" id="%2$s-update" data-slug="%2$s" data-plugin="%1$s">
				<td colspan="99" class="plugin-update colspanchange">
					<div class="update-message notice inline notice-warning notice-alt">
						<p>%4$s</p>
					</div>
				</td>
			</tr>',
			esc_attr( $plugin_name ),
			esc_attr( $this->get_slug() ),
			esc_attr( $active_class ),
			$message // phpcs:ignore
		);

		// Hide border for plugin row.
		printf( '<script type="text/javascript">document.querySelector( \'tr[data-plugin="%s"]\' ).classList.add( \'update\' );</script>', esc_attr( $plugin_name ) );

	}





	// # LICENSE REQUESTS ----------------------------------------------------------------------------------------------

	/**
	 * Activate a license key.
	 *
	 * @since  1.0
	 *
	 * @param string $license_key The license key.
	 *
	 * @return object
	 */
	public function activate_license( $license_key ) {

		// Activate license.
		$license = $this->process_license_request( 'activate_license', $license_key );

		// Clear update plugins transient.
		set_site_transient( 'update_plugins', null );

		// Delete plugin version info cache.
		$cache_key = md5( 'edd_plugin_' . sanitize_key( $this->_path ) . '_version_info' );
		delete_transient( $cache_key );

		return json_decode( wp_remote_retrieve_body( $license ) );

	}

	/**
	 * Check the status of a license key.
	 *
	 * @since  1.0
	 *
	 * @param string $license_key The license key.
	 *
	 * @return object
	 */
	public function check_license( $license_key = '' ) {

		// If license key is empty, get the plugin setting.
		if ( empty( $license_key ) ) {
			$license_key = $this->get_license_key();
		}

		$cache_key = sprintf( '%1$s_license_data_%2$s', $this->_slug, $license_key );

		// Get cached license.
		if ( $cached_license = GFCache::get( $cache_key ) ) {
			return $cached_license;
		}

		// Perform a license check request.
		$response = $this->process_license_request( 'check_license', $license_key );
		$license  = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license && in_array( $license->license, [ 'valid', 'inactive', 'site_inactive' ] ) ) {
			if ( $license->license === 'site_inactive' ) {
				$new_license_status = rgobj( $this->activate_license( $license_key ), 'license' );

				if ( $new_license_status === 'valid' ) {
					$license->license = 'valid';
				}
			}

			GFCache::set( $cache_key, $license, true, 10 * MINUTE_IN_SECONDS );
		}

		return $license;

	}

	/**
	 * Process a request to the CosmicGiant store.
	 *
	 * @since  1.0
	 *
	 * @param string $action  The action to process.
	 * @param string $license The license key.
	 *
	 * @return array|\WP_Error
	 */
	protected function process_license_request( $action, $license ) {

		// Prepare the request arguments.
		$args = [
			'method'    => 'POST',
			'timeout'   => 10,
			'sslverify' => false,
			'body'      => [
				'edd_action' => $action,
				'license'    => trim( $license ),
				'item_id'    => urlencode( $this->get_edd_item_id() ),
				'url'        => home_url(),
			],
		];

		return wp_remote_request( $this->get_store_url(), $args );

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Get the store URL.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function get_store_url() {

		return defined( $this->store_url() ) ? constant( $this->store_url() ) : 'https://cosmicgiant.com';

	}

	/**
	 * Get the plugin version.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function get_addon_version() {

		return defined( $this->addon_version() ) ? constant( $this->addon_version() ) : '1.0';

	}

	/**
	 * Get license key.
	 *
	 * @since  1.0
	 *
	 * @return string
	 */
	public function get_license_key() {

		return defined( $this->license_key() ) ? constant( $this->license_key() ) : $this->get_plugin_setting( 'license_key' );

	}

	/**
	 * Get the EDD item ID.
	 *
	 * @since 1.0
	 *
	 * @return int
	 */
	protected function get_edd_item_id() {

		return defined( $this->edd_item_id() ) ? constant( $this->edd_item_id() ) : 0;

	}





	// # ABSTRACT METHODS ---------------------------------------------------------------------------------------------

	/**
	 * Return the store url constant name.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	abstract protected function store_url();

	/**
	 * Get the full path and filename of the plugin bootstrap file.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	abstract protected function get_plugin_file();

	/**
	 * Return the license key constant name.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	abstract protected function license_key();

	/**
	 * Return the addon version constant name.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	abstract protected function addon_version();

	/**
	 * Return the EDD item id constant name.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	abstract protected function edd_item_id();

}
