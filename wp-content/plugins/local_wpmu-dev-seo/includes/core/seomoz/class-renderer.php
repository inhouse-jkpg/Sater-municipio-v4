<?php
/**
 * Class Renderer
 *
 * @package    SmartCrawl
 * @subpackage Seomoz
 */

namespace SmartCrawl\SEOMoz;

use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Renderable;

/**
 * Class Renderer
 */
class Renderer extends Renderable {

	use Singleton;

	/**
	 * Render the content.
	 *
	 * @param string $target_url Target URL.
	 * @param string $view       View name.
	 *
	 * @return void
	 */
	public function render( $target_url, $view ) {
		$access_id  = Settings::get_setting( 'access-id' );
		$secret_key = Settings::get_setting( 'secret-key' );

		if ( empty( $access_id ) || empty( $secret_key ) ) {
			return;
		}

		$target_url = preg_replace( '!http(s)?:\/\/!', '', $target_url );
		$api        = new API( $access_id, $secret_key );
		$urlmetrics = $api->urlmetrics( $target_url );

		$attribution = str_replace( '/', '%252F', untrailingslashit( $target_url ) );
		$attribution = "https://moz.com/researchtools/ose/links?site={$attribution}";

		if ( is_object( $urlmetrics ) && $api->is_response_valid( $urlmetrics ) ) {
			$this->render_view(
				$view,
				array(
					'attribution' => $attribution,
					'urlmetrics'  => $urlmetrics,
				)
			);
		} else {
			$error   = $this->get_specific_error( $urlmetrics );
			$message = sprintf(
				'%s %s',
				esc_html__( 'We were unable to retrieve data from the Moz API.', 'wds' ),
				$error
			);

			$this->render_view(
				'notice',
				array(
					'class'   => 'sui-notice-error',
					'message' => $message,
				)
			);
		}
	}

	/**
	 * Get the error.
	 *
	 * @param object $response Response.
	 *
	 * @return string
	 */
	private function get_specific_error( $response ) {
		switch ( API::get_error_type( $response ) ) {
			case 400:
				return esc_html__( "If you've recently created an account, allow 24 hours for your first data to arrive. If you are an existing user, please reset your Moz API credentials to fix this issue.", 'wds' );

			default:
				return isset( $response->error_message ) ? $response->error_message : '';
		}
	}

	/**
	 * Get view defaults.
	 *
	 * @return array
	 */
	protected function get_view_defaults() {
		return array();
	}
}