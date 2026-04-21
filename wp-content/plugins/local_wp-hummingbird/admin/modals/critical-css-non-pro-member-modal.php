<?php
/**
 * Critical CSS modal for non PRO member.
 *
 * @since 3.6.0
 * @package Hummingbird
 */

use Hummingbird\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="sui-modal sui-modal-lg wp-hb-upsell-modals">
	<div
			role="dialog"
			id="critical-css-non-pro-member-modal"
			class="sui-modal-content"
			aria-modal="true"
			aria-labelledby="critical-css-non-pro-member-modal-title"
			data-modal-size="lg"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<?php if ( ! apply_filters( 'wpmudev_branding_hide_branding', false ) ) : ?>
					<figure class="sui-box-banner" aria-hidden="true">
						<img src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/upgrade-css-modal-bg.png' ); ?>" alt=""
							srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/upgrade-css-modal-bg.png' ); ?> 1x, <?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/upgrade-css-modal-bg@2x.png' ); ?> 2x">
					</figure>
				<?php endif; ?>

				<button class="sui-button-icon sui-button-float--right" id="critical-css-non-pro-member-dismiss-button" data-action="closed" data-location="dash_widget" onclick="WPHB_Admin.minification.hbTrackCriticalMPEvent( this )">
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_attr_e( 'Close this modal', 'wphb' ); ?></span>
				</button>

				<h3 id="critical-css-non-pro-member-modal-title" class="sui-box-title sui-lg" style="white-space: inherit">
					<?php esc_html_e( 'Generate Critical CSS', 'wphb' ); ?>
				</h3>
			</div>

			<div class="sui-box-body sui-content-center sui-spacing-top--10 sui-spacing-bottom--10">
				<p class="sui-description" style="text-align: center">
					<?php esc_html_e( 'A performance game-changer, Generate Critical CSS automatically prioritizes above-the-fold content, substantially boosting page speed and user experience. Upgrade today to unlock this feature + a host of other powerful (and free) WordPress tools.', 'wphb' ); ?>
				</p>
				<p style="margin-bottom: 10px; margin-top: 25px;">
					<a
						style="background: #0059ff;"
						id="critical-css-non-pro-member-try-pro"
						data-action="cta_clicked"
						data-location="dash_widget"
						target="_blank"
						href="<?php echo esc_url( Utils::get_link( 'plugin', 'hummingbird_delay_js_ao_summary' ) ); ?>"
						class="sui-button margin-top-10"
						onclick="WPHB_Admin.minification.hbTrackCriticalMPEvent( this )"
					>
						<?php esc_html_e( 'Find out more', 'wphb' ); ?>
					</a>
				</p>
				<?php if ( ! Utils::has_access_to_hub() ) { ?>
					<p class="sui-description">
						<?php esc_html_e( 'Already a member?', 'wphb' ); ?>
						<a
							style="color: #0059ff;"
							class="wphb-already-member-connect-site"
							id="critical-css-non-pro-member-connect"
							href="<?php echo esc_url( Utils::get_link( 'connect-url', 'hummingbird_criticalcss_existing' ) ); ?>"
							data-action="<?php echo Utils::is_dash_plugin_active_and_disconnected() ? esc_attr( 'connect_dash' ) : esc_attr( 'connect_site' ); ?>"
							data-location="dash_widget"
							onclick="WPHB_Admin.minification.hbTrackCriticalMPEvent( this )"
						>
							<?php esc_html_e( 'Connect site', 'wphb' ); ?>
						</a>
					</p>
				<?php } ?>
			</div>
			<div class="sui-box-footer sui-flatten sui-spacing-bottom--40">
				<h4 class="sui-box-title"><?php esc_html_e( 'You get these pro optimization features:', 'wphb' ); ?></h4>
				<div class="wp-hb-pro-features">
					<ul>
						<li>
							<div class="wp-hb-pro-features_item">
								<div class="sui-icon-box">
									<span class="sui-icon-web-globe-world" aria-hidden="true"></span>
								</div>
								<div class="sui-content-box">
									<h5><?php esc_html_e( 'Delay JavaScript execution', 'wphb' ); ?></h5>
										<p><?php esc_html_e( 'Delay the loading of JS files and 3rd-party scripts until user interaction for more speed.', 'wphb' ); ?></p>
								</div>
							</div>
							<div class="wp-hb-pro-features_item">
								<div class="sui-icon-box">
									<span class="sui-icon-loader" aria-hidden="true"></span>
								</div>
								<div class="sui-content-box">
									<h5><?php esc_html_e( 'Enhanced file minification with CDN', 'wphb' ); ?></h5>
									<p><?php esc_html_e( 'Minify file sizes and serve them faster than ever with our 114-point global CDN.', 'wphb' ); ?></p>
								</div>
							</div>
						</li>
						<li>
							<div class="wp-hb-pro-features_item">
								<div class="sui-icon-box">
									<span class="sui-icon-web-globe-world" aria-hidden="true"></span>
								</div>
								<div class="sui-content-box">
									<h5><?php esc_html_e( 'Instant site health alerts & notifications', 'wphb' ); ?></h5>
									<p><?php esc_html_e( 'Stay on top of site health and resolve issues quickly with instant alerts and notifications.', 'wphb' ); ?></p>
								</div>
							</div>
							<div class="wp-hb-pro-features_item">
								<div class="sui-icon-box">
									<span class="sui-icon-wand-magic" aria-hidden="true"></span>
								</div>
								<div class="sui-content-box">
									<h5><?php esc_html_e( 'Automated white label reports', 'wphb' ); ?></h5>
									<p><?php esc_html_e( 'Create automated white label reports for your clients that detail site performance.', 'wphb' ); ?></p>
								</div>
							</div>
						</li>
						<li>
							<div class="wp-hb-pro-features_item">
								<div class="sui-icon-box">
									<span class="sui-icon-smush" aria-hidden="true"></span>
								</div>
								<div class="sui-content-box">
									<h5><?php esc_html_e( 'Serve images faster with Smush Pro', 'wphb' ); ?></h5>
									<p><?php esc_html_e( 'Trusted by 1 million+ users, you won’t find a better WP image optimization plugin.', 'wphb' ); ?></p>
								</div>
							</div>
							<div class="wp-hb-pro-features_item">
								<div class="sui-icon-box">
									<span class="sui-icon-help-support" aria-hidden="true"></span>
								</div>
								<div class="sui-content-box">
									<h5><?php esc_html_e( '24/7 live WordPress support', 'wphb' ); ?></h5>
									<p><?php esc_html_e( 'Get help with any WordPress issue from the best support team in the business.', 'wphb' ); ?></p>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>