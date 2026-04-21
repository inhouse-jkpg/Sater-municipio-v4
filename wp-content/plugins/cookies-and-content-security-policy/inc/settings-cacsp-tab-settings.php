<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>

<div class="cookies-and-content-security-policy-help">
	<?php
	echo '<p>';
		_e( 'You don\'t have to fill out this form, the plugin comes with default settings. But if you want to change settings, this is it.', 'cookies-and-content-security-policy' );
	echo '</p>';
	?>
</div>
<?php
$cacsp_option_only_csp = get_cacsp_options( 'cacsp_option_only_csp', false, '', true );
$cacsp_option_own_style = get_cacsp_options( 'cacsp_option_own_style', false, '', true );
$cacsp_option_own_js = get_cacsp_options( 'cacsp_option_own_js', false, '', true );
$cacsp_option_banner = get_cacsp_options( 'cacsp_option_banner', false, '', true );
$cacsp_option_allow_use_site = get_cacsp_options( 'cacsp_option_allow_use_site', false, '', true );
$cacsp_option_hide_unused_settings_row = get_cacsp_options( 'cacsp_option_hide_unused_settings_row', false, '', true );
$cacsp_option_grandma = get_cacsp_options( 'cacsp_option_grandma', false, '', true );
$cacsp_option_show_refuse_button = get_cacsp_options( 'cacsp_option_show_refuse_button', false, '', true );
$cacsp_option_settings_close_button = get_cacsp_options( 'cacsp_option_settings_close_button', false, '', true );
$cacsp_option_settings_save_consent = get_cacsp_options( 'cacsp_option_settings_save_consent', false, '', true );
$cacsp_option_forms = get_cacsp_options( 'cacsp_option_forms', false, '', true );
$cacsp_option_worker = get_cacsp_options( 'cacsp_option_worker', false, '', true );
$cacsp_option_blob = get_cacsp_options( 'cacsp_option_blob', false, '', true );
$cacsp_option_disable_unsafe_inline = get_cacsp_options( 'cacsp_option_disable_unsafe_inline', false, '', true );
$cacsp_option_disable_unsafe_eval = get_cacsp_options( 'cacsp_option_disable_unsafe_eval', false, '', true );
$cacsp_option_disable_content_not_allowed_message = get_cacsp_options( 'cacsp_option_disable_content_not_allowed_message', false, '', true );
$cacsp_option_meta = get_cacsp_options( 'cacsp_option_meta', false, '', true );
$cacsp_option_debug = get_cacsp_options( 'cacsp_option_debug', false, '', true );
$cacsp_option_no_x_csp = get_cacsp_options( 'cacsp_option_no_x_csp', false, '', true );
$cacsp_option_settings_policy_link = get_cacsp_options( 'cacsp_option_settings_policy_link', false, '', true );
$cacsp_option_settings_policy_link_url = get_cacsp_options( 'cacsp_option_settings_policy_link_url', false, '', true );
$cacsp_option_settings_policy_link_target = get_cacsp_options( 'cacsp_option_settings_policy_link_target', false, '', true );
$cacsp_option_settings_expire = get_cacsp_options( 'cacsp_option_settings_expire', true, '365', true );
$cacsp_option_settings_timeout = get_cacsp_options( 'cacsp_option_settings_timeout', true, '1000', true );
$cacsp_option_settings_admin_email = get_cacsp_options( 'cacsp_option_settings_admin_email', false, '', true );
$cacsp_option_wpengine_compatibility_mode = get_cacsp_options( 'cacsp_option_wpengine_compatibility_mode', false, '', true );
$cacsp_option_google_consent_mode = get_cacsp_options( 'cacsp_option_google_consent_mode', false, '', true );
$cacsp_option_bypass_ip = get_cacsp_options( 'cacsp_option_bypass_ip', false, '', true );
$cacsp_option_bypass_ips = get_cacsp_options( 'cacsp_option_bypass_ips', false, '', true );
?>
<h2><?php esc_html_e( 'Settings', 'cookies-and-content-security-policy' ); ?></h2>
<table class="form-table">
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Basic settings', 'cookies-and-content-security-policy' ); ?>
		</th>
		<td>
			<fieldset>
				<label for="cacsp_option_only_csp">
					<?php if ( $cacsp_option_only_csp ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_only_csp" id="cacsp_option_only_csp" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Only use CSP, no modal for me.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'This will only make use of domains in Always allowed.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_banner">
					<?php if ( $cacsp_option_banner ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_banner" id="cacsp_option_banner" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Do not use a modal, I want a banner.', 'cookies-and-content-security-policy' ); ?>
				</label>
				<br>
				<label for="cacsp_option_allow_use_site">
					<?php if ( $cacsp_option_allow_use_site ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_allow_use_site" id="cacsp_option_allow_use_site" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Allow user to access site without saving settings.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'Only works with banner.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_hide_unused_settings_row">
					<?php if ( $cacsp_option_hide_unused_settings_row ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_hide_unused_settings_row" id="cacsp_option_hide_unused_settings_row" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Hide unused sections in Settings.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'Example: If you don\'t have any domains specified for Marketing, that setting won\'t show for the visitor.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_grandma">
					<?php if ( $cacsp_option_grandma ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_grandma" id="cacsp_option_grandma" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Grandma mode.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'Add grandma with milk and cookies.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_show_refuse_button">
					<?php if ( $cacsp_option_show_refuse_button ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_show_refuse_button" id="cacsp_option_show_refuse_button" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Show refuse cookies button.', 'cookies-and-content-security-policy' ); ?>
				</label>
				<br>
				<label for="cacsp_option_settings_close_button">
					<?php if ( $cacsp_option_settings_close_button ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_settings_close_button" id="cacsp_option_settings_close_button" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Show close button (&times;).', 'cookies-and-content-security-policy' ); ?>
				</label>
				<br>
				<label for="cacsp_option_google_consent_mode">
					<?php if ( $cacsp_option_google_consent_mode ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_google_consent_mode" id="cacsp_option_google_consent_mode" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Enable Google Consent Mode v2', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php _e( 'If you enable Google Consent Mode v2, consent will be denied until the visitor accepts marketing cookies. If you do not enable Google Consent Mode v2, consent will always be denied.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
			</fieldset>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Advanced settings', 'cookies-and-content-security-policy' ); ?>
		</th>
		<td>
			<fieldset>
				<label for="cacsp_option_own_style">
					<?php if ( $cacsp_option_own_style ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_own_style" id="cacsp_option_own_style" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Do not include css, I\'ve got my own style.', 'cookies-and-content-security-policy' ); ?>
				</label>
				<br>
				<label for="cacsp_option_own_js">
					<?php if ( $cacsp_option_own_js ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_own_js" id="cacsp_option_own_js" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Do not include js, I\'ve got my own script.', 'cookies-and-content-security-policy' ); ?>
				</label>
				<br>
				<label for="cacsp_option_forms">
					<?php if ( $cacsp_option_forms ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_forms" id="cacsp_option_forms" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Use Content Security Policy for forms for added security.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'Recommended for better security.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_worker">
					<?php if ( $cacsp_option_worker ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_worker" id="cacsp_option_worker" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Use Content Security Policy for worker-src.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'Recommended for better security.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_blob">
					<?php if ( $cacsp_option_blob ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_blob" id="cacsp_option_blob" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Allow blob.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'Example: Some javascript libraries uses blob for images. Divi uses this for scripts in edit mode.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_disable_unsafe_inline">
					<?php if ( $cacsp_option_disable_unsafe_inline ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_disable_unsafe_inline" id="cacsp_option_disable_unsafe_inline" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Disable unsafe-inline.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'Disables the use of inline resources, such as inline &lt;script&gt; elements, javascript: URLs, and inline event handlers.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_disable_unsafe_eval">
					<?php if ( $cacsp_option_disable_unsafe_eval ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_disable_unsafe_eval" id="cacsp_option_disable_unsafe_eval" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Disable unsafe-eval.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'Disables the use of eval() and similar methods for creating code from strings.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_disable_content_not_allowed_message">
					<?php if ( $cacsp_option_disable_content_not_allowed_message ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_disable_content_not_allowed_message" id="cacsp_option_disable_content_not_allowed_message" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Disable content not allowed message.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'Disables the error message that appears when an iframe is blocked.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_meta">
					<?php if ( $cacsp_option_meta ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_meta" id="cacsp_option_meta" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Use meta.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'If your host does not accept setting php header(), or if you\'re using static page cache that doesn\'t go through php, check this to add CSP as a meta tag in the header instead.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_debug">
					<?php if ( $cacsp_option_debug ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_debug" id="cacsp_option_debug" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Debug.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'This will print helpful comments at the beginning of your page inside the &gt;head&lt; tag.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_no_x_csp">
					<?php if ( $cacsp_option_no_x_csp ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_no_x_csp" id="cacsp_option_no_x_csp" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Disable X-Content-Security-Policy.', 'cookies-and-content-security-policy' ); ?>
				</label>
				<br>
				<label for="cacsp_option_settings_admin_email">
					<?php if ( $cacsp_option_settings_admin_email ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_settings_admin_email" id="cacsp_option_settings_admin_email" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Hide admin email in error messages.', 'cookies-and-content-security-policy' ); ?>
				</label>
				<br>
				<label for="cacsp_option_wpengine_compatibility_mode">
					<?php if ( $cacsp_option_wpengine_compatibility_mode ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_wpengine_compatibility_mode" id="cacsp_option_wpengine_compatibility_mode" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'WP Engine compatibility mode.', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php _e( 'Enabling this will use the <a href="https://wpengine.com/support/personalization-user-segmentation-page-cache/" target="_blank">User Cache Segmentation</a> to set the cookie, which will allow this plugin to work with WP Engine caching. <strong>Do not enable this setting if you are not on WP Engine.</strong>.', 'cookies-and-content-security-policy' ); ?></small>
				</label>
				<br>
				<label for="cacsp_option_settings_save_consent">
					<?php if ( $cacsp_option_settings_save_consent ) {
						$checked = ' checked';
					} else {
						$checked = '';
					} ?>
					<input type="checkbox" name="cacsp_option_settings_save_consent" id="cacsp_option_settings_save_consent" value="1"<?php echo $checked; ?>> 
						<?php esc_html_e( 'Save proof of consent', 'cookies-and-content-security-policy' ); ?>
						<br>
						<small><?php esc_html_e( 'The consent data is saved in the database table "your_prefix" + "cacsp_consent". By default, the prefix is "wp_", so in most cases the table is named "wp_cacsp_consent".', 'cookies-and-content-security-policy' ); ?></small>
				</label>
			</fieldset>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Cookie policy page', 'cookies-and-content-security-policy' ); ?>
		</th>
		<td>
			<?php
			echo wp_dropdown_pages(
					array(
						'name'              => 'cacsp_option_settings_policy_link',
						'echo'              => 0,
						'show_option_none'  => __( '&mdash; Select &mdash;' ),
						'option_none_value' => '0',
						'selected'          => $cacsp_option_settings_policy_link,
					)
				)
			?>
			<small><?php esc_html_e( 'Will be linked from the cookie modal. Does not show the the cookie modal for the user to be able to read the policy before accepting.', 'cookies-and-content-security-policy' ); ?></small>
		</td>
	</tr>
	<?php
	echo cacsp_settings_input_row( __('Cookie policy URL', 'cookies-and-content-security-policy', false ), 'cacsp_option_settings_policy_link_url', $cacsp_option_settings_policy_link_url, false, false, false, __( 'If you use a PDF, or have your cookie policy externally, simply add the URL here. Don\'t forget https://', 'cookies-and-content-security-policy' ) ); 
	?>
	<tr>
		<th>
			<?php esc_html_e( 'Cookie policy target', 'cookies-and-content-security-policy' ); ?>
		</th>
		<td>
			<label for="cacsp_option_settings_policy_link_target">
				<?php if ( $cacsp_option_settings_policy_link_target ) {
					$checked = ' checked';
				} else {
					$checked = '';
				} ?>
				<input type="checkbox" name="cacsp_option_settings_policy_link_target" id="cacsp_option_settings_policy_link_target" value="1"<?php echo $checked; ?>> 
					<?php esc_html_e( 'Open cookie policy in a new tab.', 'cookies-and-content-security-policy' ); ?>
			</label>
		</td>
	</tr>
	<?php
	echo cacsp_settings_input_row( __('Cookie Expire', 'cookies-and-content-security-policy', false ), 'cacsp_option_settings_expire', $cacsp_option_settings_expire, false, false, __( 'Default: 365', 'cookies-and-content-security-policy' ), __( 'After how many days the accept cookie should expire.', 'cookies-and-content-security-policy'), 'number' ); 
	?>
	<?php
	echo cacsp_settings_input_row( __('Timeout', 'cookies-and-content-security-policy', false ), 'cacsp_option_settings_timeout', $cacsp_option_settings_timeout, false, false, __( 'Default: 1000', 'cookies-and-content-security-policy' ), __( 'How many ms (milliseconds) until the cookie modal should appear.', 'cookies-and-content-security-policy'), 'number' ); 
	?>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'How to link back to settings', 'cookies-and-content-security-policy' ); ?>
		</th>
		<td>
			<?php esc_html_e( 'If you want to link back to the settings modal simply add a link with href = "#cookiesAndContentPolicySettings"', 'cookies-and-content-security-policy' ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Bypassing', 'cookies-and-content-security-policy' ); ?>
		</th>
		<td>
			<?php esc_html_e( 'Just add ?cacsp_bypass=true to your url. All cookies will be accepted for the session. Great for when you run speedtest and so forth.', 'cookies-and-content-security-policy' ); ?>
		</td>
	</tr>
	<tr>
		<th>
			<?php esc_html_e( 'Bypass by IP', 'cookies-and-content-security-policy' ); ?>
		</th>
		<td>
			<label for="cacsp_option_bypass_ip">
				<?php if ( $cacsp_option_bypass_ip ) {
					$checked = ' checked';
				} else {
					$checked = '';
				} ?>
				<input type="checkbox" name="cacsp_option_bypass_ip" id="cacsp_option_bypass_ip" value="1"<?php echo $checked; ?>> 
					<?php esc_html_e( 'Bypass by IP', 'cookies-and-content-security-policy' ); ?>
					<br>
					<small><?php esc_html_e( 'This will bypass the cookie modal and accept all cookies for the specified IPs.', 'cookies-and-content-security-policy' ); ?></small>
			</label>
		</td>
	</tr>
	<?php echo cacsp_settings_textarea_row( __('IPs to bypass', 'cookies-and-content-security-policy' ), 'cacsp_option_bypass_ips', $cacsp_option_bypass_ips, true, __('127.0.0.1<br>13.37.841.01', 'cookies-and-content-security-policy' ) ); ?>
</table>

<div class="submit">
	<input type="submit" name="save_cacsp_settings_settings" value="<?php esc_html_e( 'Save Settings' , 'cookies-and-content-security-policy' ); ?>" class="button-primary" />
</div>

<script>
	// Show/hide "Allow user to access site without saving settings"
	if ( !jQuery('label[for="cacsp_option_banner"] input').is(":checked") ) {
		jQuery('label[for="cacsp_option_allow_use_site"]').hide();
		jQuery('label[for="cacsp_option_allow_use_site"]').next('br').hide();
	}
	jQuery('label[for="cacsp_option_banner"] input').change(function() {
		if ( jQuery('label[for="cacsp_option_banner"] input').is(":checked") ) {
			jQuery('label[for="cacsp_option_allow_use_site"]').show();
			jQuery('label[for="cacsp_option_allow_use_site"]').next('br').show();
		} else {
			jQuery('label[for="cacsp_option_allow_use_site"]').hide();
			jQuery('label[for="cacsp_option_allow_use_site"]').next('br').hide();
		}
	});
	// Show/hide "IPs to bypass"
	if ( !jQuery('label[for="cacsp_option_bypass_ip"] input').is(":checked") ) {
		jQuery('label[for="cacsp_option_bypass_ip"] input').parent().parent().parent().next().hide();
	}
	jQuery('label[for="cacsp_option_bypass_ip"] input').change(function() {
		if ( jQuery('label[for="cacsp_option_bypass_ip"] input').is(":checked") ) {
			jQuery(this).parent().parent().parent().next().show();
		} else {
			jQuery(this).parent().parent().parent().next().hide();
		}
	});
</script>