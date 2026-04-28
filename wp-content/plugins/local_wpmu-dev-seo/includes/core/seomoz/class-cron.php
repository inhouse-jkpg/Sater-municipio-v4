<?php
/**
 * Class Cron
 *
 * @package    SmartCrawl
 * @subpackage Seomoz
 */

namespace SmartCrawl\SEOMoz;

use SmartCrawl\Admin\Settings\Admin_Settings;
use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Controllers;

/**
 * Class Cron
 */
class Cron extends Controllers\Controller {

	use Singleton;

	const EVENT_HOOK = 'wds_daily_moz_data_hook';

	const OPTION_ID = 'wds-moz-data';

	/**
	 * Can we add meta box.
	 *
	 * @return bool
	 */
	public function should_run() {
		$adv_tools_options = Settings::get_component_options( Settings::COMP_AUTOLINKS, array() );

		return ( ! isset( $adv_tools_options['disable-adv-tools'] ) || ! $adv_tools_options['disable-adv-tools'] ) &&
			Admin_Settings::is_tab_allowed( Settings::TAB_AUTOLINKS );
	}

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'admin_init', array( $this, 'schedule_moz_data_event' ) );
		add_action( self::EVENT_HOOK, array( $this, 'save_moz_data' ) );
	}

	/**
	 * Schedule cron event.
	 *
	 * @return void
	 */
	public function schedule_moz_data_event() {
		if ( ! wp_next_scheduled( self::EVENT_HOOK ) ) {
			wp_schedule_event( time(), 'daily', self::EVENT_HOOK );
		}
	}

	/**
	 * Save the moz data.
	 *
	 * @return void
	 */
	public function save_moz_data() {
		$access_id  = Settings::get_setting( 'access-id' );
		$secret_key = Settings::get_setting( 'secret-key' );

		if ( empty( $access_id ) || empty( $secret_key ) ) {
			return;
		}

		$target_url = preg_replace( '!http(s)?:\/\/!', '', home_url() );
		$api        = new API( $access_id, $secret_key );
		$urlmetrics = $api->urlmetrics( $target_url );

		$data           = get_option( self::OPTION_ID, array() );
		$data           = empty( $data ) || ! is_array( $data )
			? array()
			: $data;
		$data[ time() ] = $urlmetrics;
		update_option( self::OPTION_ID, $data );
	}
}