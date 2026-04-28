<?php
/**
 * Class Dashboard Widget
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
 * Init WDS SEOMoz Dashboard Widget
 *
 * TODO: get rid of this widget and move the information it contains to an SC dashboard widget
 */
class Dashboard_Widget extends Controllers\Controller {

	use Singleton;

	/**
	 * Check if we can run the dashboard widget.
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
	 * Add a widget to WP Dashboard.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_dashboard_setup', array( &$this, 'dashboard_widget' ) );
	}

	/**
	 * Dashboard Widget callback.
	 *
	 * @return void
	 */
	public function dashboard_widget() {
		// Continue only if edit post capability is found.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'wds_seomoz_dashboard_widget',
			__( 'Moz - SmartCrawl', 'wds' ),
			array(
				&$this,
				'widget',
			)
		);
	}

	/**
	 * Render widget content.
	 *
	 * @return void
	 */
	public static function widget() {
		$renderer = Renderer::get();
		?>
		<div class="<?php echo esc_attr( \smartcrawl_sui_class() ); ?>">
			<div class="sui-wrap">
				<?php
				$renderer->render(
					get_bloginfo( 'url' ),
					'seomoz-dashboard-widget'
				);
				?>
			</div>
		</div>
		<?php
	}
}