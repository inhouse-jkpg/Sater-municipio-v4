<?php
/**
 * Class Metabox
 *
 * @package    SmartCrawl
 * @subpackage Seomoz
 */

namespace SmartCrawl\SEOMoz;

use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Controllers;
use SmartCrawl\Admin\Settings\Admin_Settings;

/**
 * Init meta box.
 */
class Metabox extends Controllers\Controller {

	use Singleton;

	/**
	 * Can we add meta box.
	 *
	 * @return bool
	 */
	public function should_run() {
		$adv_tools_options = Settings::get_component_options( Settings::COMP_AUTOLINKS, array() );

		return ( ! isset( $adv_tools_options['disable-adv-tools'] ) || ! $adv_tools_options['disable-adv-tools'] ) &&
			Admin_Settings::is_tab_allowed( Settings::TAB_AUTOLINKS )
			&& Settings::get_setting( 'access-id' )
			&& Settings::get_setting( 'secret-key' );
	}

	/**
	 * Init the class.
	 *
	 * @return  void
	 */
	protected function init() {
		add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
	}

	/**
	 * Adds a box to the main column on the Post and Page edit screens.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		$show = \user_can_see_urlmetrics_metabox();

		foreach ( get_post_types() as $post_type ) {
			if ( $show ) {
				add_meta_box(
					'wds_seomoz_urlmetrics',
					__( 'Moz URL Metrics - SmartCrawl', 'wds' ),
					array( &$this, 'urlmetrics_box' ),
					$post_type,
					'normal',
					'high'
				);
			}
		}
	}

	/**
	 * Prints the box content.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return void
	 */
	public function urlmetrics_box( $post ) {
		$renderer = Renderer::get();
		?>
		<div class="<?php echo esc_attr( \smartcrawl_sui_class() ); ?>">
			<div class="<?php \smartcrawl_wrap_class( 'wds-metabox' ); ?>">
				<div class="wds-metabox-section">
					<?php
					$renderer->render(
						get_permalink( $post->ID ),
						'urlmetrics-metabox'
					);
					?>
				</div>
			</div>
		</div>
		<?php
	}
}