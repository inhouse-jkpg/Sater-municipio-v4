<?php

namespace WP_Defender\Helper\Analytics;

use WP_Defender\Model\Setting\Scan as Scan_Setting;
use WP_Defender\Behavior\WPMUDEV;

/**
 * Gather analytics data required for scan feature.
 */
class Scan {

	/**
	 * Analytics data for scan started event.
	 *
	 * @param Scan_Setting $scan_setting Scan settings object.
	 *
	 * @return array[
	 *   'event' => string,
	 *   'data' => array
	 * ]
	 */
	public function scan_started( Scan_Setting $scan_setting ): array {
		$analytics_data = [
			'Core File change' => 'Disabled',
			'Plugin file change' => 'Disabled',
			'Known Vulnerabilities' => 'Disabled',
			'Suspicious Code' => 'Disabled',
		];

		if ( $scan_setting->integrity_check && $scan_setting->check_core ) {
			$analytics_data['Core File change'] = 'Enabled';
		}

		if ( $scan_setting->integrity_check && $scan_setting->check_plugins ) {
			$analytics_data['Plugin file change'] = 'Enabled';
		}

		$is_pro = wd_di()->get( WPMUDEV::class )->is_pro();

		if ( $is_pro && $scan_setting->check_known_vuln ) {
			$analytics_data['Known Vulnerabilities'] = 'Enabled';
		}

		if ( $is_pro && $scan_setting->scan_malware ) {
			$analytics_data['Suspicious Code'] = 'Enabled';
		}

		return [
			'event' => 'def_scan_started',
			'data' => $analytics_data,
		];
	}
}