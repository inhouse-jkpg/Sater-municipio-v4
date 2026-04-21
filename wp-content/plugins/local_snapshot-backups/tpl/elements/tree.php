<?php
/**
 * Template file for Directory Tree
 *
 * @var array $excluded List of excluded files.
 *
 * @since 4.1.4
 */

foreach ( $files as $file ) :
	$path       = $file['path'];
	$suspicious = is_suspicious_file_name( $path );

	if ( $suspicious ) {
		continue;
	}

	$checked = in_array( $path, $excluded );
	$class   = ( isset( $file['browsable'] ) && $file['browsable'] && 'dir' === $file['type'] ) ? 'is-browsable' : 'not-browsable';
	$class  .= ' node-type--' . $file['type'];
	$class  .= ( 'ajax' === $type ) ? ' node--appended' : '';
	$class  .= $checked ? ' node--enabled' : ' node--disabled';
	$size    = $file['size'];
	?>
	<li class="<?php echo esc_attr( $class ); ?>" data-path="<?php echo esc_attr( strip_tags( $file['path'] ) ); ?>" data-name="<?php echo esc_attr( $file['name'] ); ?>" data-type="<?php echo esc_attr( $file['type'] ); ?>" role="treeitem"
							<?php
							if ( 'dir' === $file['type'] ) :
								?>
		aria-expanded="false" <?php endif; ?> aria-selected="<?php echo $checked ? 'false' : 'true'; ?>">
		<span class="sui-tree-node">
			<?php if ( 'dir' === $file['type'] ) : ?>
				<span role="button" class="loading-icon" data-button="expander" aria-label="<?php esc_attr_e( 'Expand or compress item', 'snapshot' ); ?>"></span>
			<?php endif; ?>
			<span class="sui-node-checkbox" role="checkbox" aria-label="<?php esc_attr_e( 'Select this item', 'snapshot' ); ?>"></span>
			<span class="snapshot-icon" aria-hidden="true"></span>
			<span class="sui-node-text">
				<?php echo esc_html( $file['name'] ); ?>
			</span>

			<?php if ( 'file' === $file['type'] ) : ?>
				<span class="sui-node-text-right"><?php echo esc_html( $size ); ?></span>
			<?php endif; ?>
		</span>
	</li>
<?php endforeach; ?>