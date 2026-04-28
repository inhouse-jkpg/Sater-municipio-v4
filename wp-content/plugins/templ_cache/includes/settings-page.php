
<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">

	<h1><?php _e( 'Templ Cache', 'templio-cache' ); ?></h1>

	<?php settings_errors(); ?>

	<p> <?php _e( 'The Templ Cache plugin is activated automatically when needed by the server. If enabled, you may see the the need to manually purge the cache at times. If there are any questions, please contact Templ support.', 'templio-cache' ); ?> </p>
	<form method="post" action="options.php">

		<?php settings_fields( 'templio-cache' ); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Purge Automatically', 'templio-cache' ); ?></th>
				<td>
					<label for="templio_auto_purge">
						<input name="templio_auto_purge" type="checkbox" id="templio_auto_purge" value="1" <?php checked( get_option( 'templio_auto_purge' ) ); ?> />
						<?php _e( 'Automatically remove all cached pages when content changes', 'templio-cache' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<p class="submit">
			<?php echo get_submit_button( null, 'primary large', 'submit', false ); ?>
			&nbsp;
			<a href="<?php echo wp_nonce_url( admin_url( add_query_arg( 'action', 'purge-cache', $this->admin_page ) ), 'purge-cache' ); ?>" class="button button-secondary button-large delete"><?php _e( 'Purge Cache', 'templio-cache' ); ?></a>
		</p>

	</form>

	<?php do_action('templ_after_cache_settings'); ?>

</div>
