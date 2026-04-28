<?php // phpcs:ignore
/**
 * "New: Amazon S3 Integration" modal.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Assets;

$assets = new Assets();
wp_nonce_field( 'snapshot_whats_new_seen', '_wpnonce-whats_new_seen' );
?>
<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="snapshot-whats-new-modal"
		class="sui-modal-content"
		aria-modal="true"
	>
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<figure class="sui-box-banner" aria-hidden="true">
					<img
						src="<?php echo esc_attr( $assets->get_asset( 'img/modal-whats-new-file-explorer.png' ) ); ?>"
						srcset="<?php echo esc_attr( $assets->get_asset( 'img/modal-whats-new-file-explorer.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/modal-whats-new-file-explorer@2x.png' ) ); ?> 2x"
						alt="<?php esc_attr_e( 'File exclusion UI with directory explorer.', 'snapshot' ); ?>"
					/>
				</figure>

				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>

				<div class="sui-box-title sui-lg" style="padding: 0 10px; white-space: normal;">
					<?php esc_html_e( 'New: Improved File Exclusion!', 'snapshot' ); ?>
				</div>
			</div>

			<div class="sui-box-body sui-content-center" style="padding-bottom: 30px; padding-top: 15px;">
				<p class="sui-description"><?php esc_html_e( 'Guess What\'s New? With a few clicks, you can now easily choose the files and folders to exclude from backups. Check out out this improved feature on the Settings tab of Snapshot\'s Backup screen.', 'snapshot' ); ?></p>
			</div>
			<div class="sui-box-footer sui-content-center sui-flatten" style="padding-bottom: 40px;">
				<button class="sui-button" id="snapshot-whats-new-modal-button-ok" data-modal-close><?php esc_html_e( 'GOT IT', 'snapshot' ); ?></button>
			</div>

		</div>
	</div>
</div>