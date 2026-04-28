<?php

namespace SmartCrawl;

use SmartCrawl\Admin\Settings\Dashboard;
use SmartCrawl\Controllers\Robots;
use SmartCrawl\Redirects\Database_Table;
use SmartCrawl\Services\Service;
use SmartCrawl\Admin\Settings\Admin_Settings;

if ( ! Admin_Settings::is_tab_allowed( Settings::TAB_AUTOLINKS ) ) {
	return;
}

$page_url = Admin_Settings::admin_url( Settings::TAB_AUTOLINKS );

$redirection_table = Database_Table::get();
$redirection_count = $redirection_table->get_count();

$option_name       = Settings::TAB_SETTINGS . '_options';
$options           = $_view['options'];
$adv_tools_enabled = ! isset( $options['disable-adv-tools'] ) || ! $options['disable-adv-tools'];

$service      = Service::get( Service::SERVICE_SITE );
$is_member    = $service->is_member();
$footer_class = $is_member ? 'sui-box-footer' : 'sui-box-body'; // Because the mascot message needs to be inside box body.
?>

<section
	id="<?php echo esc_attr( Dashboard::BOX_ADVANCED_TOOLS ); ?>"
	class="sui-box wds-dashboard-widget"
>
	<div class="sui-box-header">
		<h2 class="sui-box-title">
			<span class="sui-icon-wand-magic" aria-hidden="true"></span> <?php esc_html_e( 'Advanced Tools', 'wds' ); ?>
		</h2>
	</div>

	<div class="sui-box-body">
		<p><?php esc_html_e( 'Advanced tools focus on the finer details of SEO including internal linking, redirections and Moz analysis.', 'wds' ); ?></p>

		<?php if ( $adv_tools_enabled ) : ?>

			<?php
			$autolinking_enabled = Settings::get_setting( Settings::COMP_AUTOLINKS );
			$breadcrumbs_enabled = Settings::get_setting( Settings::COMP_BREADCRUMBS );
			$moz_connected       = Settings::get_setting( 'access-id' ) && Settings::get_setting( 'secret-key' );

			$robots_file_exists = Robots::get()->file_exists();
			$is_rootdir_install = Robots::get()->is_rootdir_install();
			$robots_enabled     = (bool) Settings::get_setting( 'robots-txt' ) && ! $robots_file_exists && $is_rootdir_install;
			?>

			<div class="wds-separator-top wds-draw-left-padded">
				<small><strong><?php esc_html_e( 'URL Redirects', 'wds' ); ?></strong></small>
				<?php if ( empty( $redirection_count ) ) : ?>
					<p>
						<small><?php esc_html_e( 'Automatically redirect traffic from one URL to another.', 'wds' ); ?></small>
					</p>
					<a
						href="<?php echo esc_attr( $page_url ); ?>&tab=tab_url_redirection&add_redirect=1"
						class="sui-button sui-button-blue"
					>
						<?php esc_html_e( 'Add Redirect', 'wds' ); ?>
					</a>
				<?php else : ?>
					<span class="wds-right"><small><?php echo esc_html( $redirection_count ); ?></small></span>
				<?php endif; ?>
			</div>

			<div class="wds-separator-top wds-draw-left-padded <?php echo $moz_connected ? 'wds-space-between' : ''; ?>">
				<small><strong><?php esc_html_e( 'Moz Integration', 'wds' ); ?></strong></small>

				<?php if ( $moz_connected ) : ?>
					<a
						href="<?php echo esc_attr( $page_url ); ?>&tab=tab_moz"
						class="sui-button sui-button-ghost"
					>
						<span class="sui-icon-eye" aria-hidden="true"></span> <?php esc_html_e( 'View Report', 'wds' ); ?>
					</a>
				<?php else : ?>
					<p>
						<small><?php esc_html_e( 'Moz provides reports that tell you how your site stacks up against the competition with all of the important SEO measurement tools.', 'wds' ); ?></small>
					</p>
					<a
						href="<?php echo esc_attr( $page_url ); ?>&tab=tab_moz"
						aria-label="<?php esc_html_e( 'Connect your Moz account', 'wds' ); ?>"
						class="sui-button sui-button-blue"
					>
						<?php esc_html_e( 'Connect', 'wds' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="wds-separator-top wds-draw-left-padded <?php echo $robots_enabled ? 'wds-space-between' : ''; ?>">
				<small>
					<strong><?php esc_html_e( 'Robots.txt', 'wds' ); ?></strong>
				</small>

				<?php if ( $robots_enabled ) : ?>
					<span class="wds-right">
						<small><?php esc_html_e( 'Active robots.txt file', 'wds' ); ?></small>
					</span>
				<?php else : ?>
					<p>
						<small><?php esc_html_e( 'Add a robots.txt file to tell search engines what they can and can’t index, and where things are.', 'wds' ); ?></small>
					</p>
					<button
						type="button"
						data-option-id="<?php echo esc_attr( $option_name ); ?>"
						data-flag="<?php echo esc_attr( 'robots-txt' ); ?>"
						aria-label="<?php esc_html_e( 'Activate Robots.txt Editor', 'wds' ); ?>"
						class="wds-activate-component wds-disabled-during-request sui-button sui-button-blue">
						<span class="sui-loading-text"><?php esc_html_e( 'Activate', 'wds' ); ?></span>
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</button>
				<?php endif; ?>
			</div>

			<div class="wds-separator-top wds-draw-left-padded">
				<small><strong><?php esc_html_e( 'Breadcrumbs', 'wds' ); ?></strong></small>
				<?php if ( $breadcrumbs_enabled ) : ?>
					<div class="wds-right">
						<span class="sui-tag wds-right sui-tag-sm sui-tag-blue"><?php esc_html_e( 'Active', 'wds' ); ?></span>
					</div>
				<?php else : ?>
					<p>
						<small><?php esc_html_e( 'Enhance your site\'s user experience and crawlability by adding breadcrumbs to your posts, pages, archives, and products.', 'wds' ); ?></small>
					</p>
					<button
						type="button"
						data-option-id="<?php echo esc_attr( $option_name ); ?>"
						data-flag="<?php echo esc_attr( Settings::COMP_BREADCRUMBS ); ?>"
						aria-label="<?php esc_html_e( 'Activate Breadcrumbs', 'wds' ); ?>"
						class="wds-activate-component wds-disabled-during-request sui-button sui-button-blue">

						<span class="sui-loading-text"><?php esc_html_e( 'Activate', 'wds' ); ?></span>
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</button>
				<?php endif; ?>
			</div>

			<div class="wds-separator-top <?php echo ! $is_member ? 'wds-box-blocked-area wds-draw-down wds-draw-left' : 'wds-draw-left-padded'; ?>">
				<small><strong><?php esc_html_e( 'Automatic Linking', 'wds' ); ?></strong></small>
				<?php if ( ! $is_member ) : ?>
					<a
						href="https://wpmudev.com/project/smartcrawl-wordpress-seo/?utm_source=smartcrawl&utm_medium=plugin&utm_campaign=smartcrawl_dash_autolinking_pro_tag"
						target="_blank"
					>
						<span
							class="sui-tag sui-tag-pro sui-tooltip"
							data-tooltip="<?php esc_attr_e( 'Upgrade to SmartCrawl Pro', 'wds' ); ?>"
						>
							<?php esc_html_e( 'Pro', 'wds' ); ?>
						</span>
					</a>
				<?php endif; ?>
				<?php if ( $autolinking_enabled && $is_member ) : ?>
					<div class="wds-right">
						<span class="sui-tag wds-right sui-tag-sm sui-tag-blue"><?php esc_html_e( 'Active', 'wds' ); ?></span>
					</div>
				<?php else : ?>
					<p>
						<small><?php esc_html_e( 'Configure SmartCrawl to automatically link certain key words to a page on your blog or even a whole new site all together.', 'wds' ); ?></small>
					</p>
					<button
						type="button"
						data-option-id="<?php echo esc_attr( $option_name ); ?>"
						data-flag="<?php echo 'autolinks'; ?>"
						aria-label="<?php esc_html_e( 'Activate autolinks component', 'wds' ); ?>"
						class="wds-activate-component wds-disabled-during-request sui-button sui-button-blue">

						<span class="sui-loading-text"><?php esc_html_e( 'Activate', 'wds' ); ?></span>
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</button>
				<?php endif; ?>
			</div>

		<?php endif; ?>
	</div>

	<div class="<?php echo esc_attr( $footer_class ); ?>">
		<div>
			<?php if ( $adv_tools_enabled ) : ?>
				<a
					href="<?php echo esc_attr( $page_url ); ?>"
					aria-label="<?php esc_html_e( 'Configure advanced tools', 'wds' ); ?>"
					class="sui-button sui-button-ghost"
				>
					<span
						class="sui-icon-wrench-tool"
						aria-hidden="true"></span> <?php esc_html_e( 'Configure', 'wds' ); ?>
				</a>
			<?php else : ?>
				<button
					type="button"
					data-option-id="<?php echo esc_attr( $option_name ); ?>"
					data-flag="disable-adv-tools"
					data-value="0"
					aria-label="<?php esc_html_e( 'Activate Advanced Tools component', 'wds' ); ?>"
					class="wds-activate-component wds-disabled-during-request sui-button sui-button-blue">
					<span class="sui-loading-text"><?php esc_html_e( 'Activate', 'wds' ); ?></span>
					<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
				</button>
			<?php endif; ?>
		</div>

		<?php
		if ( ! $is_member ) {

			$this->render_view(
				'mascot-message',
				array(
					'key'         => 'seo-checkup-upsell',
					'dismissible' => false,
					'message'     => sprintf(
						'%s <a target="_blank" class="sui-button sui-button-purple" href="https://wpmudev.com/project/smartcrawl-wordpress-seo/?utm_source=smartcrawl&utm_medium=plugin&utm_campaign=smartcrawl_dash_reports_upsell_notice">%s</a>',
						esc_html__( 'Upgrade to Pro and automatically link your articles both internally and externally with automatic linking - a favourite among SEO pros.', 'wds' ),
						esc_html__( 'Unlock now with Pro', 'wds' )
					),
				)
			);
		}
		?>
	</div>
</section>