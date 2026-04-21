<?php
/**
 * Common licensing settings methods.
 *
 * @since 1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */

namespace CosmicGiant\Plugin_Skeleton\Traits\Licensing;

use CosmicGiant\Plugin_Skeleton\Utils\Date;

/**
 * Common licensing settings methods.
 *
 * @since 1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */
trait Settings {

	/**
	 * Prepare license settings fields.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function get_license_settings_fields() {

		return [
			[
				'name'                => 'license_key',
				'label'               => esc_html__( 'License Key', 'cosmicgiant' ),
				'type'                => 'text',
				'class'               => 'medium',
				'default_value'       => '',
				'input_type'          => $this->license_feedback() ? 'password' : 'text',
				'error_message'       => esc_html__( 'Invalid License', 'cosmicgiant' ),
				'feedback_callback'   => [ $this, 'license_feedback' ],
				'validation_callback' => [ $this, 'license_validation' ],
				'description'         => $this->license_key_description(),
				'disabled'            => defined( $this->license_key() ) || ( is_multisite() && ! is_main_site() ),
			],
			[
				'name'          => 'background_updates',
				'label'         => esc_html__( 'Background Updates', 'cosmicgiant' ),
				'type'          => 'radio',
				'horizontal'    => true,
				'default_value' => false,
				'tooltip'       => sprintf(
					esc_html__( 'Set this to ON to allow %1$s to download and install bug fixes and security updates automatically in the background. Requires a valid license key.', 'cosmicgiant' ),
					$this->get_short_title()
				),
				'choices'       => [
					[
						'label' => esc_html__( 'On', 'cosmicgiant' ),
						'value' => true,
					],
					[
						'label' => esc_html__( 'Off', 'cosmicgiant' ),
						'value' => false,
					],
				],
			],
		];

	}

	/**
	 * Get license validity for plugin settings field.
	 *
	 * @since  1.0
	 *
	 * @param string $value Plugin setting value.
	 * @param array  $field Plugin setting field.
	 *
	 * @return null|bool
	 */
	public function license_feedback( $value = '', $field = [] ) {

		// If no license key is provided, check the setting.
		if ( empty( $value ) ) {
			$value = $this->get_setting( 'license_key' );
		}

		// If no license key is provided, return.
		if ( empty( $value ) ) {
			return null;
		}

		// Get license data.
		$license_data = $this->check_license( $value );

		// If no license data was returned or license is invalid, return false.
		if ( empty( $license_data ) || 'invalid' === $license_data->license ) {
			return false;
		} elseif ( 'valid' === $license_data->license ) {
			return true;
		}

		return false;

	}

	/**
	 * Activate license on plugin settings save.
	 *
	 * @since  1.0
	 *
	 * @param array  $field         Plugin setting field.
	 * @param string $field_setting Plugin setting value.
	 */
	public function license_validation( $field, $field_setting ) {

		// Get old license.
		$old_license = $this->get_plugin_setting( 'license_key' );

		// If an old license key exists and a new license is being saved, deactivate old license.
		if ( $old_license && $field_setting != $old_license ) {

			// Deactivate license.
			$deactivate_license = $this->process_license_request( 'deactivate_license', $old_license );

			// Log response.
			$this->log_debug( __METHOD__ . '(): Deactivate license: ' . print_r( $deactivate_license, true ) );

		}

		// If field setting is empty, return.
		if ( empty( $field_setting ) ) {
			return;
		}

		// Activate license.
		$this->activate_license( $field_setting );

	}

	/**
	 * Prepare description for License Key plugin settings field.
	 *
	 * @since  1.0
	 *
	 * @return string
	 */
	public function license_key_description() {

		// Get license key.
		$license_key = $this->get_license_key();

		// If no license key is entered, display warning.
		if ( rgblank( $license_key ) ) {
			return esc_html__( 'The license key is used for access to extensions, automatic upgrades and support.', 'cosmicgiant' );
		}

		// Get license data.
		$license_data = $this->check_license( $license_key );

		// If no expiration date is provided, return.
		if ( ! rgobj( $license_data, 'expires' ) ) {
			return '';
		}

		if ( 'lifetime' === $license_data->expires ) {

			return sprintf(
				'<em>%s</em>',
				esc_html__( 'Your license is valid forever.', 'cosmicgiant' )
			);

		} else {

			return sprintf(
				'<em>%s</em>',
				sprintf(
					esc_html__( 'Your license is valid through %s.', 'cosmicgiant' ),
					date( Date::FORMAT_DATE, strtotime( $license_data->expires ) )  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				)
			);

		}

	}

	/**
	 * Updates the plugin settings with the provided settings.
	 *
	 * @since 1.0
	 *
	 * @param array $settings The settings to be saved.
	 */
	public function update_plugin_settings( $settings ) {

		if ( $this->is_save_postback() ) {

			$previous_settings = $this->get_previous_settings();

			if ( $settings['background_updates'] != $previous_settings['background_updates'] ) {
				$this->update_wp_auto_updates( $settings['background_updates'] );
			}

		}

		parent::update_plugin_settings( $settings );

	}

	/**
	 * Updates the WordPress auto_update_plugins option to enable or disable automatic updates so the correct state is displayed on the plugins page.
	 *
	 * @since 1.0
	 *
	 * @param bool $is_enabled Indicates if background updates are enabled for Advanced Permissions in the plugin settings.
	 */
	public function update_wp_auto_updates( $is_enabled ) {

		$option       = 'auto_update_plugins';
		$auto_updates = (array) get_site_option( $option, [] );

		if ( $is_enabled ) {
			$auto_updates[] = $this->_full_path;
			$auto_updates   = array_unique( $auto_updates );
		} else {
			$auto_updates = array_diff( $auto_updates, [ $this->_full_path ] );
		}

		$callback = [ $this, 'action_update_site_option_auto_update_plugins' ];
		remove_action( 'update_site_option_auto_update_plugins', $callback );
		update_site_option( $option, $auto_updates );
		add_action( 'update_site_option_auto_update_plugins', $callback, 10, 3 );

	}

	/**
	 * Updates the background updates app setting when the WordPress auto_update_plugins option is changed.
	 *
	 * @since 1.0
	 *
	 * @param string $option    The name of the option.
	 * @param array  $value     The current value of the option.
	 * @param array  $old_value The previous value of the option.
	 */
	public function action_update_site_option_auto_update_plugins( $option, $value, $old_value ) {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_POST['asset'] ) && ! empty( $_POST['state'] ) ) { // phpcs:ignore
			// Option is being updated by the ajax request performed when using the enable/disable auto-updates links on the plugins page.
			$asset = sanitize_text_field( urldecode( $_POST['asset'] ) ); // phpcs:ignore

			if ( $asset !== $this->_full_path ) {
				return;
			}

			$is_enabled = $_POST['state'] === 'enable'; // phpcs:ignore
		} else {
			// Option is being updated by some other means.
			$is_enabled  = in_array( $this->_full_path, $value );
			$was_enabled = in_array( $this->_full_path, $old_value );

			if ( $is_enabled === $was_enabled ) {
				return;
			}
		}

		$settings = $this->get_plugin_settings();

		if ( $settings['background_updates'] != $is_enabled ) {
			$settings['background_updates'] = $is_enabled;
			$this->update_plugin_settings( $settings );
		}

	}

}
