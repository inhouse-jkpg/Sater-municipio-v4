<?php
// Network

// $cookies_and_cacsp_dir = plugin_dir_path( __FILE__ );

add_action( "network_admin_menu", "cacsp_new_menu_item_network" );
function cacsp_new_menu_item_network() {

	add_submenu_page(
		'settings.php', 
		__( 'Cookies and Content Security Policy', 'cookies-and-content-security-policy' ), 
		__( 'Cookies and Content Security Policy', 'cookies-and-content-security-policy' ), 
		'manage_options', 
		'cacsp_network_settings', 
		'cacsp_network_settings' 
	);

}

function cacsp_network_settings() {
	include_once( 'functions.php' );
	include_once( 'settings-cacsp-update-options.php' );
	?>
	<div class="wrap">
		<form method="post">
			<?php
			$cacsp_option_use = get_cacsp_options( 'cacsp_option_use', false, '', true );
			?>
			<h1><?php esc_html_e( 'Cookies and Content Security Policy', 'cookies-and-content-security-policy' ); ?></h1>
			<h2><?php esc_html_e( 'Network settings', 'cookies-and-content-security-policy' ); ?></h2>
			<p><?php esc_html_e( 'Choose how to handle domains, texts, settings and colors in you network.', 'cookies-and-content-security-policy' ); ?></p>
			<table class="form-table">
				<tr>
					<td>
						<?php
						$cacsp_option_use = get_cacsp_options( 'cacsp_option_use' );
						?>
						<fieldset>
							<label for="cacsp_option_use_local">
								<?php if ( $cacsp_option_use == 'default' || $cacsp_option_use == null ) {
									$checked = ' checked';
								} else {
									$checked = '';
								} ?>
								<input type="radio" name="cacsp_option_use" id="cacsp_option_use_local" value="default"<?php echo $checked; ?>> 
									<strong><?php esc_html_e( 'Default', 'cookies-and-content-security-policy' ); ?></strong>
									<small><?php esc_html_e( 'Everything is individual on all sites', 'cookies-and-content-security-policy' ); ?></small>
							</label>
							<br>
							<label for="cacsp_option_use_main_site_some">
								<?php if ( $cacsp_option_use == 'some' ) {
									$checked = ' checked';
								} else {
									$checked = '';
								} ?>
								<input type="radio" name="cacsp_option_use" id="cacsp_option_use_main_site_some" value="some"<?php echo $checked; ?>> 
									<strong><?php esc_html_e( 'Only texts', 'cookies-and-content-security-policy' ); ?></strong>
									<small><?php esc_html_e( 'Everyting but texts is shared from the main site *.', 'cookies-and-content-security-policy' ); ?></small>
							</label>
							<br>
							<label for="cacsp_option_use_main_site_everythings">
								<?php if ( $cacsp_option_use == 'everything' ) {
									$checked = ' checked';
								} else {
									$checked = '';
								} ?>
								<input type="radio" name="cacsp_option_use" id="cacsp_option_use_main_site_everythings" value="everything"<?php echo $checked; ?>> 
									<strong><?php esc_html_e( 'Everything', 'cookies-and-content-security-policy' ); ?></strong>
									<small><?php esc_html_e( 'Everything is shared from your main site *.', 'cookies-and-content-security-policy' ); ?></small>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>
			<?php
			echo '* <a href="' . get_admin_url( get_main_site_id() ) . 'options-general.php?page=cacsp_settings">' . __( 'Your main site', 'cookies-and-content-security-policy' ) . '</a>';
			?>
			<div class="submit">
				<input type="submit" name="save_cacsp_settings_network" value="<?php esc_html_e( 'Save Settings' , 'cookies-and-content-security-policy' ); ?>" class="button-primary" />
			</div>
			<?php
			wp_nonce_field( 'cacsp_nonce_action', 'cacsp_nonce_name', false );
			?>
		</form>
	</div>
	<?php
}


