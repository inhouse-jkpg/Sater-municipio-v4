<?php
/**
 * Dashboard Welcome Modal.
 *
 * @package SmartCrawl
 */

use SmartCrawl\Settings;

$modal_id = 'wds-welcome-modal';

$options = Settings::get_specific_options( 'wds_settings_options' );
?>

<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="<?php echo esc_attr( $modal_id ); ?>"
		class="sui-modal-content <?php echo esc_attr( $modal_id ); ?>-dialog"
		aria-modal="true"
		aria-labelledby="<?php echo esc_attr( $modal_id ); ?>-dialog-title"
		aria-describedby="<?php echo esc_attr( $modal_id ); ?>-dialog-description">

		<div class="sui-box" role="document">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--40">
				<div class="sui-box-banner" role="banner" aria-hidden="true">
					<img src="<?php echo esc_attr( SMARTCRAWL_PLUGIN_URL ); ?>assets/images/upgrade-welcome-header.svg" alt="<?php esc_html_e( 'New! Geolocation-based Redirection Rules', 'wds' ); ?>"/>
				</div>
				<button
					class="sui-button-icon sui-button-float--right" data-modal-close
					id="<?php echo esc_attr( $modal_id ); ?>-close-button"
					type="button"
				>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog window', 'wds' ); ?></span>
				</button>
				<h3 class="sui-box-title sui-lg" id="<?php echo esc_attr( $modal_id ); ?>-dialog-title">
					<?php esc_html_e( 'New! Geolocation-based Redirection Rules', 'wds' ); ?>
				</h3>

				<div class="sui-box-body">
					<p class="sui-description" id="<?php echo esc_attr( $modal_id ); ?>-dialog-description">
						<?php
						esc_html_e(
							'Hey there, We\'re excited to let you know that you can now direct your website visitors to specific pages based on their location. With the new Geolocation-based redirection rules, you can ensure that your users see the most relevant content based on their locations. Give it a try and see the results for yourself!',
							'wds'
						);
						?>
					</p>

					<button
						id="<?php echo esc_attr( $modal_id ); ?>-get-started"
						type="button"
						class="sui-button sui-button-blue wds-disabled-during-request">
						<span class="sui-loading-text">
							<?php esc_html_e( 'Check it out', 'wds' ); ?>
						</span>
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>