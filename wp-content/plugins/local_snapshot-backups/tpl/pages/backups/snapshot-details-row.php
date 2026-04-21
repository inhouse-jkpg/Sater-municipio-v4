<?php // phpcs:ignore
/**
 * Expanded row with backup details.
 *
 * @package snapshot
 */

if ( 'null' !== $description ) {
	$desc = ( strlen( $description ) > 45 ) ? wp_trim_words( stripslashes( $description ), 9, '' ) : $description;
} else {
	$description = false;
}

$ex_files_count = 0;
if ( isset( $global_exclusions['files'] ) && is_countable( $global_exclusions['files'] ) ) {
	$ex_files_count = count( $global_exclusions['files'] );
}
?>
<tr class="sui-accordion-item-content snapshot-details-row <?php echo $last_snap ? ' snapshot-last-backup' : ''; ?>" data-backup_id="<?php echo esc_attr( $snapshot_id ); ?>">
	<td colspan="4">
		<div class="sui-box snapshot-non-restoration">
			<div class="sui-box-body">
				<?php if ( $add_export_notice ) { ?>
					<div class="sui-row snapshot-export-notice">
						<div class="sui-col"><span <?php /* translators: %1$s - Destination, %2$s - Link for Destination page  */ ?> class="tpd-description"><?php echo wp_kses_post( sprintf( __( 'The backup is stored on the %1$s Cloud and a full copy of the backup is exported to the connected and active <a href="%2$s">destinations</a>.', 'snapshot' ), $plugin_custom_name, esc_url( network_admin_url() . 'admin.php?page=snapshot-destinations' ) ) ); ?></span>
						</div>
					</div>
				<?php } ?>
				<div class="sui-row">
					<?php echo wp_kses_post( $export_details ); ?>
					<?php if ( 'automate' !== $backup_type ) : ?>
						<div class="sui-col-md-3 sui-col-xs-6">
							<span class="sui-settings-label"><?php esc_html_e( 'Current Schedule', 'snapshot' ); ?></span>
							<span class="sui-description row-current-schedule"><span class="sui-icon-loader sui-loading" aria-hidden="true"></span><a href="#" style="display: none;" class="open-edit-schedule" data-modal-data="{}"><span class="schedule"></span><span class="sui-icon-pencil sui-sm right" aria-hidden="true"></span></a></span>
						</div>
						<div class="sui-col-md-3 sui-col-xs-6">
							<span class="sui-settings-label"><?php esc_html_e( 'Excluded Files and Folders', 'snapshot' ); ?></span>
							<?php if ( 0 === $ex_files_count ) : ?>
								<span class="sui-description"><?php esc_html_e( 'No exclusion set', 'snapshot' ); ?></span>
							<?php endif; ?>

							<?php if ( isset( $global_exclusions['files'] ) ) { ?>
								<?php foreach ( $global_exclusions['files'] as $key => $value ) : ?>
									<div class="sui-description">
										<div class="sui-tooltip sui-tooltip-top-left" data-tooltip="<?php echo esc_attr( $value ); ?>">
											<div class="ellipsis">
												<span class="sui-icon-<?php echo esc_attr( strpos( $value, '.' ) > 0 ? 'page' : 'folder' ); ?> sui-sm left" aria-hidden="true"></span>
												<?php echo esc_html( $value ); ?>
											</div>
										</div>
									</div>
									<?php
									if ( $key === 1 && $ex_files_count > 2 ) {
										/* translators: %d - excluded tables count */
										$other_html  = sprintf( __( '+ %d others', 'snapshot' ), $ex_files_count - 2 );
										$other_files = implode( ", \n", array_slice( $global_exclusions['files'], 2, $ex_files_count - 2 ) );
										?>
										<div class="sui-description">
											<div class="sui-tooltip sui-tooltip-constrained" data-tooltip="<?php echo esc_attr( $other_files ); ?>">
												<div class="ellipsis others-txt">
													<?php echo esc_html( $other_html ); ?>
												</div>
											</div>
										</div>
										<?php
										break;
									}
									?>
								<?php endforeach; ?>
							<?php } ?>
						</div>
						<div class="sui-col-md-3 sui-col-xs-6">
							<span class="sui-settings-label"><?php esc_html_e( 'Excluded DB Tables', 'snapshot' ); ?></span>
							<?php
							if ( isset( $global_exclusions['tables'] ) ) {
								$ex_tables_count = count( $global_exclusions['tables'] );
								if ( 0 === $ex_tables_count ) {
									?>
									<span class="sui-description"><?php esc_html_e( 'No exclusion set', 'snapshot' ); ?></span>
									<?php
								}
								foreach ( $global_exclusions['tables'] as $key => $value ) {
									?>
									<div class="sui-description">
										<div class="sui-tooltip sui-tooltip-top-left" data-tooltip="<?php echo esc_attr( trim( $value, " \'" ) ); ?>">
											<div class="ellipsis">
												<span class="sui-icon-user-reputation-points sui-sm left" aria-hidden="true"></span>
												<?php echo esc_html( trim( $value, " \'" ) ); ?>
											</div>
										</div>
									</div>
									<?php
									if ( $key === 1 && $ex_tables_count > 2 ) {
										/* translators: %s - excluded tables count */
										$other_html   = sprintf( __( '+ %s others', 'snapshot' ), $ex_tables_count - 2 );
										$other_tables = array_slice( $global_exclusions['tables'], 2, $ex_tables_count - 2 );
										array_walk(
											$other_tables,
											function ( &$value, $key ) {
												$value = trim( $value, " \'" );
											}
										);
										$other_tables = implode( ', ', $other_tables );
										?>
										<div class="sui-description">
											<div class="sui-tooltip sui-tooltip-constrained" data-tooltip="<?php echo esc_attr( $other_tables ); ?>">
												<div class="ellipsis others-txt">
													<?php echo esc_html( $other_html ); ?>
												</div>
											</div>
										</div>
										<?php
										break;
									}
								}
							}
							?>
						</div>
					<?php endif; ?>
					<div class="sui-col-md-3 sui-col-xs-6">
						<span class="sui-settings-label"><?php esc_html_e( 'Date', 'snapshot' ); ?></span>
						<span class="sui-description"><?php echo esc_html( $date ); ?></span>
					</div>

					<div class="sui-col-md-3 sui-col-xs-6">
						<?php
						/* translators: %s - icon */
						?>
						<span class="sui-settings-label">
							<span class="snapshot-tooltip-size"><?php esc_html_e( 'Backup Size', 'snapshot' ); ?></span>
							<span class="snapshot-icon-tooltip sui-tooltip sui-tooltip-constrained" style="margin-left: 5px;" data-tooltip="<?php esc_attr_e( 'Sizes shown refer to the size of the backup. The export size can differ, as these are incremental backups, not full site ones.', 'snapshot' ); ?>"><span class="sui-icon-info sui-sm" aria-hidden="true"></span></span>
							<span class="snapshot-icon-tooltip2 sui-tooltip sui-tooltip-constrained" style="margin-left: 5px;display: none;" data-tooltip="<?php esc_attr_e( 'Sizes shown refer to the size of the backup once exported. The actual size that is counted against your storage usage should be smaller, as these are incremental backups, not full site ones.', 'snapshot' ); ?>"><span class="sui-icon-info sui-sm" aria-hidden="true"></span></span>
						</span>
						<span class="sui-description"><?php echo esc_html( $size ); ?></span>
					</div>
					<div class="sui-col-md-3 sui-col-xs-6">
						<span class="sui-settings-label"><?php esc_html_e( 'Comment', 'snapshot' ); ?></span>
						<span class="sui-description">

							<?php if ( $description ) : ?>
								<a href="#" class="open-edit-backup sui-tooltip sui-tooltip-constrained" data-tooltip="<?php echo esc_attr( stripslashes( $description ) ); ?>" data-type="edit" data-modal-data="{}" title="<?php esc_attr_e( 'Edit comment', 'snapshot' ); ?>">
									<span class="snapshot-backup--content">
										<?php echo wp_kses_post( stripslashes( $desc ) ); ?>
									</span>
									<span class="sui-icon-pencil sui-sm right" aria-hidden="true"></span>
									<span class="screen-reader-text"><?php esc_html_e( 'Edit comment', 'snapshot' ); ?></span>
								</a>
							<?php else : ?>
								<a href="#" class="open-edit-backup" data-type="add" data-modal-data="{}" title="<?php esc_attr_e( 'Add comment', 'snapshot' ); ?>">
									<?php esc_html_e( 'Add comment', 'snapshot' ); ?>
									<span class="sui-icon-pencil sui-sm right" aria-hidden="true"></span>
								</a>
							<?php endif; ?>

						</span>

					</div>
				</div>
			</div>
			<div class="sui-box-footer">
				<div class="sui-actions-right snapshot-delete-backup">
					<button class="sui-button-icon sui-button-red sui-button-outlined sui-tooltip" data-tooltip="<?php esc_attr_e( 'Delete', 'snapshot' ); ?>" onclick="jQuery(window).trigger('snapshot:delete_backup', ['<?php echo esc_attr( $snapshot_id ); ?>'])">
						<span class="sui-icon-trash" aria-hidden="true"></span>
						<span class="sui-screen-reader-text"><?php esc_html_e( 'Delete', 'snapshot' ); ?></span>
					</button>
				</div>
				<div class="sui-actions-right sui-tooltip sui-tooltip-constrained sui-tooltip-top-left-mobile snapshot-delete-backup-inactive" style="--tooltip-width: 128px;" data-tooltip="<?php esc_attr_e( 'You can only delete the last backup.', 'snapshot' ); ?>">
					<button class="sui-button-icon sui-button-outlined" disabled>
						<span class="sui-icon-trash" aria-hidden="true"></span>
						<span class="sui-screen-reader-text"><?php esc_html_e( 'Delete', 'snapshot' ); ?></span>
					</button>
				</div>

				<div class="sui-actions-left" style=" width: 150px; ">
					<button class="sui-button sui-button-ghost view-log" data-nonce="<?php echo esc_attr( wp_create_nonce( 'snapshot_get_backup_log' ) ); ?>" data-backup-id="<?php echo esc_attr( $snapshot_id ); ?>">
						<span class="sui-icon-eye" aria-hidden="true"></span>
						<?php esc_html_e( 'View logs', 'snapshot' ); ?>
					</button>
				</div>

				<div class="sui-content-right" style="width: 100%; display: flex; justify-content: flex-end;">
					<button role="button" class="sui-button sui-tooltip snapshot-restore-backup" data-tooltip="<?php esc_attr_e( 'Restore backup', 'snapshot' ); ?>" onclick="jQuery(window).trigger('snapshot:restore_backup', ['<?php echo esc_attr( $snapshot_id ); ?>'])">
						<span class="sui-icon-refresh" aria-hidden="true"></span>
						<?php esc_html_e( 'Restore', 'snapshot' ); ?>
						<span class="sui-screen-reader-text"><?php esc_html_e( 'Restore', 'snapshot' ); ?></span>
					</button>
					<button class="sui-button sui-button-blue sui-tooltip sui-tooltip-constrained snapshot-export-backup" data-snapshot-id="<?php echo esc_attr( $snapshot_id ); ?>" data-snapshot-name="<?php echo esc_attr( $snapshot_name ); ?>" data-tooltip="<?php esc_attr_e( 'The backup file will be sent to your email', 'snapshot' ); ?>">
						<span class="sui-loading-text">
							<span class="sui-icon-download" aria-hidden="true"></span>
							<?php esc_html_e( 'Export', 'snapshot' ); ?>
						</span>

						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</button>
				</div>
			</div>
		</div>

		<div class="sui-box snapshot-restoration step-0">
			<div class="sui-box-body sui-hidden-xs">
				<!-- Desktop -->

				<div class="progressbar-header">
					<p><?php esc_html_e( 'Restoration is in progress', 'snapshot' ); ?> <span class="downloaded-size--info"></span></p>
					<div class="restoration-step">
						<p class="only-step-0"><?php esc_html_e( 'Step 0/5 ', 'snapshot' ); ?></p>
						<p class="only-step-1"><?php esc_html_e( 'Step 1/5 ', 'snapshot' ); ?></p>
						<p class="only-step-2"><?php esc_html_e( 'Step 2/5 ', 'snapshot' ); ?></p>
						<p class="only-step-3"><?php esc_html_e( 'Step 3/5 ', 'snapshot' ); ?></p>
						<p class="only-step-4"><?php esc_html_e( 'Step 4/5 ', 'snapshot' ); ?></p>
						<p class="only-step-5"><?php esc_html_e( 'Step 5/5 ', 'snapshot' ); ?></p>
					</div>
				</div>

				<section>
					<div class="progressbar-container">
						<div class="progressbar-status">
							<div role="alert" class="sui-screen-reader-text" aria-live="assertive">
								<p><?php esc_html_e( 'Restoration progress at 0%', 'snapshot' ); ?></p>
							</div>
						</div>
						<ul class="progress-circles" aria-hidden="true">
							<li class="circle sui-tooltip ci-step-1" data-tooltip="<?php esc_attr_e( 'Backup exported', 'snapshot' ); ?>">
								<span class="sui-icon-check"></span>
							</li>
							<li class="circle sui-tooltip ci-step-2" data-tooltip="<?php esc_attr_e( 'Backup downloaded', 'snapshot' ); ?>">
								<span class="sui-icon-check"></span>
							</li>
							<li class="circle sui-tooltip ci-step-3" data-tooltip="<?php esc_attr_e( 'Files have been successfully restored', 'snapshot' ); ?>">
								<span class="sui-icon-check"></span>
							</li>
							<li class="circle sui-tooltip ci-step-4" data-tooltip="<?php esc_attr_e( 'Database has been successfully restored', 'snapshot' ); ?>">
								<span class="sui-icon-check"></span>
							</li>
						</ul>
					</div>
				</section>

				<div class="progress-title">
					<p><span class="lt-step-1"><?php esc_html_e( 'Exporting backup', 'snapshot' ); ?></span><span class="on-step-1"><?php esc_html_e( 'Backup exported', 'snapshot' ); ?></span></p>
					<p><span class="lt-step-2"><?php esc_html_e( 'Download backup', 'snapshot' ); ?></span><span class="on-step-2"><?php esc_html_e( 'Backup downloaded', 'snapshot' ); ?></span></p>
					<p><span class="lt-step-3"><?php esc_html_e( 'Restore files', 'snapshot' ); ?></span><span class="on-step-3"><?php esc_html_e( 'Files restored', 'snapshot' ); ?></span></p>
					<p><span class="lt-step-4"><?php esc_html_e( 'Restore database', 'snapshot' ); ?></span><span class="on-step-4"><?php esc_html_e( 'Database restored', 'snapshot' ); ?></span></p>
					<p><span class="lt-step-5"><?php esc_html_e( 'Finalize restoration', 'snapshot' ); ?></span><span class="on-step-5"><?php esc_html_e( 'Finalizing restoration', 'snapshot' ); ?></span></p>
				</div>

			</div>

			<div class="sui-box-body sui-hidden-sm sui-hidden-md sui-hidden-lg">
				<!-- Mobile -->
				<div class="sui-row">
					<div class="sui-col">
						<div class="sui-table-item-title"><?php esc_html_e( 'Destination', 'snapshot' ); ?></div>
						<div class="sui-table-item-title gray">
							<span class="sui-icon-wpmudev-logo" aria-hidden="true"></span>
							<?php echo esc_html( $destination_text ); ?>
						</div>
					</div>
					<div class="sui-col">
						<div class="sui-table-item-title"><?php esc_html_e( 'Frequency', 'snapshot' ); ?></div>
						<div class="sui-table-item-title gray"><?php echo esc_html( $frequency_human ); ?></div>
					</div>
				</div>

				<div class="progressbar-container mobile">
					<div class="progressbar mobile-0 lt-step-1"></div>
					<div class="progressbar mobile-100 on-step-1"></div>
					<div class="progress-circles mobile">
						<div class="circle active on-step-1"></div>
					</div>
				</div>
				<div class="progress-title mobile">
					<p><span class="lt-step-1"><?php esc_html_e( 'Exporting backup', 'snapshot' ); ?></span><span class="on-step-1"><?php esc_html_e( 'Backup exported', 'snapshot' ); ?></span></p>
				</div>

				<div class="progressbar-container mobile">
					<div class="progressbar mobile-0 lt-step-2"></div>
					<div class="progressbar mobile-100 on-step-2"></div>
					<div class="progress-circles mobile">
						<div class="circle active on-step-2"></div>
					</div>
				</div>
				<div class="progress-title mobile">
					<p><span class="lt-step-2"><?php esc_html_e( 'Download backup', 'snapshot' ); ?></span><span class="on-step-2"><?php esc_html_e( 'Backup downloaded', 'snapshot' ); ?></span></p>
				</div>

				<div class="progressbar-container mobile">
					<div class="progressbar mobile-0 lt-step-3"></div>
					<div class="progressbar mobile-100 on-step-3"></div>
					<div class="progress-circles mobile">
						<div class="circle active on-step-3"></div>
					</div>
				</div>
				<div class="progress-title mobile">
					<p><span class="lt-step-3"><?php esc_html_e( 'Restore files', 'snapshot' ); ?></span><span class="on-step-3"><?php esc_html_e( 'Files restored', 'snapshot' ); ?></span></p>
				</div>

				<div class="progressbar-container mobile">
					<div class="progressbar mobile-0 lt-step-4"></div>
					<div class="progressbar mobile-100 on-step-4"></div>
					<div class="progress-circles mobile">
						<div class="circle active on-step-4"></div>
					</div>
				</div>
				<div class="progress-title mobile">
					<p><span class="lt-step-4"><?php esc_html_e( 'Restore database', 'snapshot' ); ?></span><span class="on-step-4"><?php esc_html_e( 'Database restored', 'snapshot' ); ?></span></p>
				</div>

				<div class="progressbar-container mobile">
					<div class="progressbar mobile-0 lt-step-5"></div>
					<div class="progressbar mobile-100 on-step-5"></div>
					<div class="progress-circles mobile">
						<div class="circle active on-step-5"></div>
					</div>
				</div>
				<div class="progress-title mobile">
					<p><span class="lt-step-5"><?php esc_html_e( 'Finalize restoration', 'snapshot' ); ?></span><span class="on-step-5"><?php esc_html_e( 'Finalizing restoration', 'snapshot' ); ?></span></p>
				</div>
			</div>

			<div class="sui-box-footer" style="display: flex; justify-content: space-between;">
				<button class="sui-button sui-button-ghost cancel-restore" data-backup-id="<?php echo esc_attr( $snapshot_id ); ?>" onclick="jQuery(window).trigger('snapshot:cancel_restore', ['<?php echo esc_attr( $snapshot_id ); ?>'])">
					<?php esc_html_e( 'Cancel', 'snapshot' ); ?>
				</button>

				<button class="sui-button sui-button-ghost view-log-in-modal" data-nonce="<?php echo esc_attr( wp_create_nonce( 'snapshot_get_backup_log' ) ); ?>" data-backup-id="<?php echo esc_attr( $snapshot_id ); ?>">
					<span class="sui-icon-eye" aria-hidden="true"></span>
					<?php esc_html_e( 'View logs', 'snapshot' ); ?>
				</button>
			</div>
		</div>
	</td>
</tr>