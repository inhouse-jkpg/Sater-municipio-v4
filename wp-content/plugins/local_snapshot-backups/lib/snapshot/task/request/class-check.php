<?php
/**
 * Checks IP.
 *
 * @package Snapshot
 * @since   4.4.0
 */

namespace WPMUDEV\Snapshot4\Task\Request;

use WPMUDEV\Snapshot4\Task;

class Check extends Task {

	/**
	 * Applies the IP check.
	 *
	 * @param array $args
	 * @return
	 */
	public function apply( $args = array() ) {
		/**
		 * @var \WPMUDEV\Snapshot4\Model\Request\Check
		 */
		$model = $args['model'];

		$model->set( 'site_name', esc_url( get_site_url() ) );
		return $model->check_ips();
	}
}