<?php
/**
 * Class to handle mixpanel advanced tools events functionality.
 *
 * @since   3.7.0
 * @package SmartCrawl
 */

namespace SmartCrawl\Mixpanel;

use SmartCrawl\Singleton;
use SmartCrawl\Redirects;

/**
 * Mixpanel Tools Event class
 */
class Tools extends Events {

	use Singleton;

	/**
	 * Initialize class.
	 *
	 * @since 3.7.0
	 */
	protected function init() {
		add_action( 'smartcrawl_after_save_redirects', array( $this, 'intercept_redirects_update' ) );
		add_action( 'smartcrawl_before_save_tools', array( $this, 'intercept_tools_update' ), 10, 2 );
		add_action( 'update_option_wds_settings_options', array( $this, 'intercept_settings_update' ), 10, 2 );
		add_action( 'update_option_wds_woocommerce_options', array( $this, 'intercept_woo_seo_update' ), 10, 2 );
	}

	/**
	 * Handle redirects update.
	 *
	 * @return void
	 *
	 * @since 3.7.0
	 */
	public function intercept_redirects_update() {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		$this->tracker()->track(
			'SMA - Redirection',
			array( 'number_redirects' => Redirects\Database_Table::get()->get_redirect_count() )
		);
	}

	/**
	 * Handle Advanced Tools update.
	 *
	 * @param array $old_value The old option value.
	 * @param array $new_value The new option value.
	 *
	 * @return void
	 *
	 * @since 3.7.0
	 */
	public function intercept_tools_update( $old_value, $new_value ) {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		$old_status = $this->get_value( 'disable-adv-tools', $old_value );
		$new_status = $this->get_value( 'disable-adv-tools', $new_value );

		if ( $old_status !== $new_status ) {
			$event = $new_status ? 'SMA - Advanced Tool Deactivated' : 'SMA - Advanced Tool Activated';

			if ( \smartcrawl_is_build_type_full() ) {
				$this->tracker()->track(
					$event,
					array(
						'advanced_tool'  => 'Automatic Links',
						'triggered_from' => 'General Settings',
					)
				);
			}

			$this->tracker()->track(
				$event,
				array(
					'advanced_tool'  => 'URL Redirection',
					'triggered_from' => 'General Settings',
				)
			);

			$this->tracker()->track(
				$event,
				array(
					'advanced_tool'  => 'Woocommerce SEO',
					'triggered_from' => 'General Settings',
				)
			);

			$this->tracker()->track(
				$event,
				array(
					'advanced_tool'  => 'Moz',
					'triggered_from' => 'General Settings',
				)
			);

			$this->tracker()->track(
				$event,
				array(
					'advanced_tool'  => 'Robots.txt Editor',
					'triggered_from' => 'General Settings',
				)
			);

			$this->tracker()->track(
				$event,
				array(
					'advanced_tool'  => 'Breadcrumbs',
					'triggered_from' => 'General Settings',
				)
			);
		}

		$old_fields = array();
		$new_fields = array();

		foreach (
			array(
				'insert',
				'link_to',
				'customkey',
			)
			as $field
		) {
			$old_fields[ $field ] = $this->get_value( $field, $old_value );
			$new_fields[ $field ] = $this->get_value( $field, $new_value );
		}

		if ( $old_fields === $new_fields ) {
			return;
		}

		$this->tracker()->track(
			'SMA - Automatic Links',
			array(
				'insert_links_count' => count( $new_fields['insert'] ),
				'link_to_count'      => count( $new_fields['link_to'] ),
				'custom_links_count' => count( array_filter( explode( "\n", $new_fields['customkey'] ) ) ),
			)
		);
	}

	/**
	 * Handle Advanced Tools related settings update.
	 *
	 * @param array $old_value The old option value.
	 * @param array $new_value The new option value.
	 *
	 * @return void
	 *
	 * @since 3.7.0
	 */
	public function intercept_settings_update( $old_value, $new_value ) {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		$this->intercept_moz_api_update();
		$this->intercept_autolinks_update( $old_value, $new_value );
		$this->intercept_robots_txt_update( $old_value, $new_value );
		$this->intercept_breadcrumbs_update( $old_value, $new_value );
	}

	/**
	 * Handle Moz api settings update.
	 *
	 * @return void
	 *
	 * @since 3.7.0
	 */
	public function intercept_moz_api_update() {
		if ( ! isset( $_POST['_wds_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['_wds_nonce'] ) );

		if ( wp_verify_nonce( $nonce, 'wds-settings-nonce' ) && isset( $_POST['wds-moz-access-id'] ) && isset( $_POST['wds-moz-secret-key'] ) ) {
			$this->tracker()->track(
				'SMA - Advanced Tool Activated',
				array(
					'advanced_tool'  => 'MOZ',
					'triggered_from' => 'MOZ',
				)
			);
		}

		if ( wp_verify_nonce( $nonce, 'wds-autolinks-nonce' ) && isset( $_POST['reset-moz-credentials'] ) ) {
			$this->tracker()->track(
				'SMA - Advanced Tool Deactivated',
				array(
					'advanced_tool'  => 'MOZ',
					'triggered_from' => 'MOZ',
				)
			);
		}
	}

	/**
	 * Handle Automatic Links settings update.
	 *
	 * @param array $old_value The old option value.
	 * @param array $new_value The new option value.
	 *
	 * @return void
	 *
	 * @since 3.7.0
	 */
	public function intercept_autolinks_update( $old_value, $new_value ) {
		$old_status = $this->get_value( 'autolinks', $old_value );
		$new_status = $this->get_value( 'autolinks', $new_value );

		if ( $old_status === $new_status ) {
			return;
		}

		if ( $new_status && ( ! isset( $_POST['_wds_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wds_nonce'] ) ), 'wds-settings-nonce' ) ) ) {
			return;
		}

		$this->tracker()->track(
			$new_status ? 'SMA - Advanced Tool Activated' : 'SMA - Advanced Tool Deactivated',
			array(
				'advanced_tool'  => 'Automatic Links',
				'triggered_from' => 'Automatic Links',
			)
		);
	}

	/**
	 * Handle Robots.txt settings update.
	 *
	 * @param array $old_value The old option value.
	 * @param array $new_value The new option value.
	 *
	 * @return void
	 *
	 * @since 3.7.0
	 */
	public function intercept_robots_txt_update( $old_value, $new_value ) {
		$old_status = $this->get_value( 'robots-txt', $old_value );
		$new_status = $this->get_value( 'robots-txt', $new_value );

		if ( $old_status === $new_status ) {
			return;
		}

		if ( $new_status && ( ! isset( $_POST['_wds_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wds_nonce'] ) ), 'wds-settings-nonce' ) ) ) {
			return;
		}

		$this->tracker()->track(
			$new_status ? 'SMA - Advanced Tool Activated' : 'SMA - Advanced Tool Deactivated',
			array(
				'advanced_tool'  => 'Robots.txt Editor',
				'triggered_from' => 'Robots.txt Editor',
			)
		);
	}

	/**
	 * Handle Breadcrumbs settings update.
	 *
	 * @param array $old_value The old option value.
	 * @param array $new_value The new option value.
	 *
	 * @return void
	 *
	 * @since 3.7.0
	 */
	public function intercept_breadcrumbs_update( $old_value, $new_value ) {
		$old_status = $this->get_value( 'breadcrumb', $old_value );
		$new_status = $this->get_value( 'breadcrumb', $new_value );

		if ( $old_status === $new_status ) {
			return;
		}

		if ( $new_status && ( ! isset( $_POST['_wds_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wds_nonce'] ) ), 'wds-settings-nonce' ) ) ) {
			return;
		}

		$this->tracker()->track(
			$new_status ? 'SMA - Advanced Tool Activated' : 'SMA - Advanced Tool Deactivated',
			array(
				'advanced_tool'  => 'Breadcrumbs',
				'triggered_from' => 'Breadcrumbs',
			)
		);
	}

	/**
	 * Handle Woocommerce SEO update.
	 *
	 * @param array $old_value The old option value.
	 * @param array $new_value The new option value.
	 *
	 * @return void
	 *
	 * @since 3.7.0
	 */
	public function intercept_woo_seo_update( $old_value, $new_value ) {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		$old_field = $this->get_value( 'woocommerce_enabled', $old_value );
		$new_field = $this->get_value( 'woocommerce_enabled', $new_value );

		if ( $old_field === $new_field ) {
			return;
		}

		$this->tracker()->track(
			$new_field ? 'SMA - Advanced Tool Activated' : 'SMA - Advanced Tool Deactivated',
			array(
				'advanced_tool'  => 'Woocommerce SEO',
				'triggered_from' => 'Woocommerce SEO',
			)
		);
	}
}