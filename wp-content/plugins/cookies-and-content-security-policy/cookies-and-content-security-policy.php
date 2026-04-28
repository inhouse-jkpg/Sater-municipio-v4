<?php
/**
* Plugin Name: Cookies and Content Security Policy
* Plugin URI: https://plugins.followmedarling.se/cookies-and-content-security-policy/
* Description: Block cookies and unwanted external content by setting Content Security Policy. A modal will be shown on the front end to let the visitor choose what kind of resources to accept.
* Short Description: Be fully GDPR and CCPA compliant through Content Security Policy. Blocks cookies and unwanted external content.
* Version: 2.29
* Author: Jonk @ Follow me Darling
* Author URI: https://plugins.followmedarling.se/
* Domain Path: /languages
* Text Domain: cookies-and-content-security-policy
**/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$cookies_and_cacsp_dir = plugin_dir_path( __FILE__ );

include_once( $cookies_and_cacsp_dir . 'inc/functions.php' );

add_action( 'init', 'cacsp_load_textdomain' );
function cacsp_load_textdomain() {
    load_plugin_textdomain( 'cookies-and-content-security-policy', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

if ( !cacsp_hide_admin_for_sub_sites() ) {
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'cacsp_options_settings_link' );
	function cacsp_options_settings_link( $links ) {
		$links[] = '<a href="' . admin_url( 'options-general.php?page=cacsp_settings' ) . '">' . __( 'Settings' ) . '</a>';
		return $links;
	}
}
add_action( 'init', 'cacsp_check_activated' );
function cacsp_check_activated() {
    if ( !get_option( 'cacsp_option_actived' ) && get_option( 'cacsp_option_active' ) ) {
        update_option( 'cacsp_option_actived', 'true' );
        delete_option( 'cacsp_option_active' );
    } elseif ( !get_option( 'cacsp_option_actived' ) ) {
    	update_option( 'cacsp_option_actived', 'false' );
    }
}

if ( !is_admin() && !get_cacsp_options( 'cacsp_option_only_csp' ) ) {
	add_action( 'wp_enqueue_scripts', 'enqueue_cacsp_front', 10 );
	add_action( 'login_enqueue_scripts', 'enqueue_cacsp_front', 10 );
	function enqueue_cacsp_front() {
		if ( cacsp_option_actived() ) {
			if ( !get_cacsp_options( 'cacsp_option_own_style' ) ) {
				wp_register_style( 
					'cookies-and-content-security-policy', 
					plugins_url( 'css/cookies-and-content-security-policy.min.css', __FILE__ ),
					array(),
					cacsp_get_plugin_version()
				);
				wp_enqueue_style( 'cookies-and-content-security-policy' );
			}
			if ( !get_cacsp_options( 'cacsp_option_own_js' ) ) {
				wp_enqueue_script( 
					'cookies-and-content-security-policy-cookie', 
					plugins_url( 'js/js.cookie.min.js', __FILE__ ),
					array(), 
					cacsp_get_plugin_version(), 
					true 
				);
				$cookies_and_cacsp_minified_js = true;
				if ( $cookies_and_cacsp_minified_js ) {
					$cookies_and_cacsp_js = 'js/cookies-and-content-security-policy.min.js';
				} else {
					$cookies_and_cacsp_js = 'js/cookies-and-content-security-policy.js';
				}
				wp_enqueue_script( 
					'cookies-and-content-security-policy', 
					plugins_url( $cookies_and_cacsp_js, __FILE__ ),
					array( 'jquery' ), 
					cacsp_get_plugin_version(), 
					true 
				);
				wp_localize_script( 'cookies-and-content-security-policy', 'cacsp_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
				if ( !get_cacsp_options( 'cacsp_option_only_csp' ) && get_cacsp_options( 'cacsp_option_disable_content_not_allowed_message', false, '0' ) == "0" ) {
					$blog_id = '';
					if ( is_multisite() ) {
						$cacsp_site_id = get_main_site_id();
						$cacsp_option_use = get_blog_option( $cacsp_site_id, 'cacsp_option_use' );
						if ( $cacsp_option_use == 'default' ) {
							$blog_id = '-blog-id-' . get_current_blog_id();
						}
					}
					wp_enqueue_script( 
						'cookies-and-content-security-policy-error-message', 
						wp_get_upload_dir()['baseurl'] . '/cookies-and-content-security-policy-error-message' . $blog_id . '.js',
						array( 'jquery', 'cookies-and-content-security-policy' ), 
						cacsp_get_plugin_version() . '&mod=' . cacsp_get_asset_last_modified_time( cacsp_get_error_message_js_dir() . '/cookies-and-content-security-policy-error-message' . $blog_id . '.js' ), 
						true 
					);
				}
				$array = array(
					'cacspReviewSettingsDescription' => __( get_cacsp_options( 'cacsp_review_settings_description', true, __( 'Your settings may be preventing you from seeing this content. Most likely you have Experience turned off.', 'cookies-and-content-security-policy' ), true ), 'cacspMessages'),
					'cacspReviewSettingsButton' => __( get_cacsp_options( 'cacsp_review_settings_button', true, __( 'Review your settings', 'cookies-and-content-security-policy' ), true ), 'cacspMessages'),
					'cacspNotAllowedDescription' => __( get_cacsp_options( 'cacsp_not_allowed_description', true, __( 'The content can\'t be loaded, since it is not allowed on the site.', 'cookies-and-content-security-policy' ), true ), 'cacspMessages'),
					'cacspNotAllowedButton' => __( get_cacsp_options( 'cacsp_not_allowed_button', true, __( 'Contact the administrator', 'cookies-and-content-security-policy' ), true ), 'cacspMessages'),
					'cacspExpires' => __( get_cacsp_options( 'cacsp_option_settings_expire', true, '365', true ), 'cacspMessages'),
					'cacspWpEngineCompatibilityMode' => get_cacsp_options( 'cacsp_option_wpengine_compatibility_mode', false, '' ),
					'cacspTimeout' => get_cacsp_options( 'cacsp_option_settings_timeout', false, '1000' ),
					'cacspOptionDisableContentNotAllowedMessage' => get_cacsp_options( 'cacsp_option_disable_content_not_allowed_message', false, '0' ),
					'cacspOptionGoogleConsentMode' => get_cacsp_options( 'cacsp_option_google_consent_mode', false, '0' ),
					'cacspOptionSaveConsent' => get_cacsp_options( 'cacsp_option_settings_save_consent', false, '0' ),
				);
				wp_localize_script('cookies-and-content-security-policy', 'cacspMessages', $array);
			}
		}
	}
	add_action('wp_head', 'cacsp_options_settings_colors', 12);
	add_action('login_head', 'cacsp_options_settings_colors', 12);
	function cacsp_options_settings_colors() {
		if ( cacsp_option_actived() ) {
			$cacsp_option_color_backdrop = get_cacsp_options( 'cacsp_option_color_backdrop' );
			$cacsp_option_color_modal_bg = get_cacsp_options( 'cacsp_option_color_modal_bg' );
			$cacsp_option_color_modal_header_bg = get_cacsp_options( 'cacsp_option_color_modal_header_bg' );
			$cacsp_option_color_modal_list_border = get_cacsp_options( 'cacsp_option_color_modal_list_border' );
			$cacsp_option_color_modal_text_color = get_cacsp_options( 'cacsp_option_color_modal_text_color' );
			$cacsp_option_color_modal_header_text_color = get_cacsp_options( 'cacsp_option_color_modal_header_text_color' );
			$cacsp_option_color_text_on = get_cacsp_options( 'cacsp_option_color_text_on' );
			$cacsp_option_color_disabled = get_cacsp_options( 'cacsp_option_color_disabled' );
			$cacsp_option_color_off = get_cacsp_options( 'cacsp_option_color_off' );
			$cacsp_option_color_on = get_cacsp_options( 'cacsp_option_color_on' );
			$cacsp_option_color_settings_button = get_cacsp_options( 'cacsp_option_color_settings_button' );
			$cacsp_option_color_settings_button_border = get_cacsp_options( 'cacsp_option_color_settings_button_border' );
			$cacsp_option_color_settings_button_text = get_cacsp_options( 'cacsp_option_color_settings_button_text' );
			$cacsp_option_color_refuse_button = get_cacsp_options( 'cacsp_option_color_refuse_button' );
			$cacsp_option_color_refuse_button_border = get_cacsp_options( 'cacsp_option_color_refuse_button_border' );
			$cacsp_option_color_refuse_button_text = get_cacsp_options( 'cacsp_option_color_refuse_button_text' );
			$cacsp_option_color_save_button = get_cacsp_options( 'cacsp_option_color_save_button' );
			$cacsp_option_color_save_button_border = get_cacsp_options( 'cacsp_option_color_save_button_border' );
			$cacsp_option_color_save_button_text = get_cacsp_options( 'cacsp_option_color_save_button_text' );
			$cacsp_option_color_accept_button = get_cacsp_options( 'cacsp_option_color_accept_button' );
			$cacsp_option_color_accept_button_border = get_cacsp_options( 'cacsp_option_color_accept_button_border' );
			$cacsp_option_color_accept_button_text = get_cacsp_options( 'cacsp_option_color_accept_button_text' );
			$cacsp_options_settings_colors = '<style type="text/css" id="cookies-and-content-security-policy-css-custom">'; 
				if ( $cacsp_option_color_backdrop ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-backdrop {
						background-color: ' . $cacsp_option_color_backdrop . ';
					}';
				}
				if ( $cacsp_option_color_modal_bg ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box > *,
					.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-show.modal-cacsp-box-bottom {
						background-color: ' . $cacsp_option_color_modal_bg . ';
					}';
				}
				if ( $cacsp_option_color_modal_header_bg ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-box-header {
						background-color: ' . $cacsp_option_color_modal_header_bg . ';
					}';
				}
				if ( $cacsp_option_color_modal_list_border ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-box-settings-list ul li:first-child,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns,
					.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-box-settings-list ul li,
					.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-show.modal-cacsp-box-bottom {
						border-color: ' . $cacsp_option_color_modal_list_border . ';
					}';
				}
				if ( $cacsp_option_color_modal_text_color ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position, .modal-cacsp-position,
					.modal-cacsp-position, .modal-cacsp-position *,
					.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-box-settings-list ul li span.modal-cacsp-toggle-switch.modal-cacsp-toggle-switch-active.disabled span {
						color: ' . $cacsp_option_color_modal_text_color . ';
					}';
				}
				if ( $cacsp_option_color_modal_header_text_color ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-box-header,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-box-header * {
						color: ' . $cacsp_option_color_modal_header_text_color . ';
					}';
				}
				if ( $cacsp_option_color_text_on ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-box-settings-list ul li a.modal-cacsp-toggle-switch.modal-cacsp-toggle-switch-active span {
						color: ' . $cacsp_option_color_text_on . ';
					}';
				}
				if ( $cacsp_option_color_disabled ) {
					$cacsp_option_color_disabled_hex = $cacsp_option_color_disabled;
					list($r, $g, $b) = sscanf( $cacsp_option_color_disabled_hex, "#%02x%02x%02x" );
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-box-settings-list ul li span.modal-cacsp-toggle-switch.modal-cacsp-toggle-switch-active.disabled span.modal-cacsp-toggle {
						background-color: rgba(' . $r . ',' . $g . ',' . $b . ', .2);
					}';
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-box-settings-list ul li span.modal-cacsp-toggle-switch.modal-cacsp-toggle-switch-active.disabled span.modal-cacsp-toggle-switch-handle {
						background-color: ' . $cacsp_option_color_disabled . ';
					}';
				}
				if ( $cacsp_option_color_off ) {
					$cacsp_option_color_off_hex = $cacsp_option_color_off;
					list($r, $g, $b) = sscanf( $cacsp_option_color_off_hex, "#%02x%02x%02x" );
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-box-settings-list ul li a.modal-cacsp-toggle-switch span.modal-cacsp-toggle {
						background-color: rgba(' . $r . ',' . $g . ',' . $b . ', .2);
					}';
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-box-settings-list ul li a.modal-cacsp-toggle-switch span.modal-cacsp-toggle-switch-handle {
						background-color: ' . $cacsp_option_color_off . ';
					}';
				}
				if ( $cacsp_option_color_on ) {
					$cacsp_option_color_on_hex = $cacsp_option_color_on;
					list($r, $g, $b) = sscanf( $cacsp_option_color_on_hex, "#%02x%02x%02x" );
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-box-settings-list ul li a.modal-cacsp-toggle-switch.modal-cacsp-toggle-switch-active span.modal-cacsp-toggle {
						background-color: rgba(' . $r . ',' . $g . ',' . $b . ', .2);
					}';
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-box-settings-list ul li a.modal-cacsp-toggle-switch.modal-cacsp-toggle-switch-active span.modal-cacsp-toggle-switch-handle {
						background-color: ' . $cacsp_option_color_on . ';
					}
					.modal-cacsp-position .modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-box-settings-list ul li a.modal-cacsp-toggle-switch.modal-cacsp-toggle-switch-active span {
						color: ' . $cacsp_option_color_on . ';
					}';
				}
				if ( $cacsp_option_color_settings_button ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-settings {
						background-color: ' . $cacsp_option_color_settings_button . ';
					}';
				}
				if ( $cacsp_option_color_settings_button_border ) {
					//.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-settings {
						border-color: ' . $cacsp_option_color_settings_button_border . ';
					}';
				}
				if ( $cacsp_option_color_settings_button_text ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-settings,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-settings * {
						color: ' . $cacsp_option_color_settings_button_text . ';
					}';
				}
				if ( $cacsp_option_color_refuse_button ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-refuse,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-refuse-all {
						background-color: ' . $cacsp_option_color_refuse_button . ';
					}';
				}
				if ( $cacsp_option_color_refuse_button_border ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-refuse,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-refuse-all {
						border-color: ' . $cacsp_option_color_refuse_button_border . ';
					}';
				}
				if ( $cacsp_option_color_refuse_button_text ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-refuse,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-refuse-all,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-refuse *,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-refuse-all * {
						color: ' . $cacsp_option_color_refuse_button_text . ';
					}';
				}
				if ( $cacsp_option_color_save_button ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-save {
						background-color: ' . $cacsp_option_color_save_button . ';
					}';
				}
				if ( $cacsp_option_color_save_button_border ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-save {
						border-color: ' . $cacsp_option_color_save_button_border . ';
					}';
				}
				if ( $cacsp_option_color_save_button_text ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-save,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-save * {
						color: ' . $cacsp_option_color_save_button_text . ';
					}';
				}
				if ( $cacsp_option_color_accept_button ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-accept,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-accept-all {
						background-color: ' . $cacsp_option_color_accept_button . ';
					}';
				}
				if ( $cacsp_option_color_accept_button_border ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-accept,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-accept-all {
						border-color: ' . $cacsp_option_color_accept_button_border . ';
					}';
				}
				if ( $cacsp_option_color_accept_button_text ) {
					$cacsp_options_settings_colors .= '.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-accept,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-accept *,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-accept-all,
					.modal-cacsp-position .modal-cacsp-box .modal-cacsp-btns .modal-cacsp-btn.modal-cacsp-btn-accept-all * {
						color: ' . $cacsp_option_color_accept_button_text . ';
					}';
				}
			$cacsp_options_settings_colors .= '</style>';
			$cacsp_options_settings_colors = str_replace( array( "\n", "\r", "\t" ), '', $cacsp_options_settings_colors );
			echo $cacsp_options_settings_colors;
		}
	}
}

add_filter( 'body_class', 'body_class_cacsp_front' );
add_filter( 'login_body_class', 'body_class_cacsp_front' );
function body_class_cacsp_front( $classes ) {
	global $post;
	if ( isset( $post ) ) {
		if ( $post->ID == get_cacsp_options( 'cacsp_option_settings_policy_link' ) ) {
		    $classes[] = 'modal-cacsp-do-not-show-cookie-modal';
		}
	}
	if ( get_cacsp_options( 'cacsp_option_banner' ) && get_cacsp_options( 'cacsp_option_allow_use_site' ) ) {
		$classes[] = 'modal-cacsp-open-no-backdrop';
	}
	if ( get_cacsp_options( 'cacsp_option_grandma' ) ) {
		$classes[] = 'modal-cacsp-grandma';
	}
	return $classes;
}

add_action( 'send_headers', 'send_headers_cacsp' );
function send_headers_cacsp() {
	$wpEngineCompatibilityMode = get_cacsp_options( 'cacsp_option_wpengine_compatibility_mode', false, '' );
	if ( $wpEngineCompatibilityMode === '1' ) {
		header( 'Vary: X-WPENGINE-SEGMENT' );
	}
};

if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'enqueue_cacsp_back' );
	function enqueue_cacsp_back() {
		wp_register_style( 
			'cookies-and-content-security-policy-admin', 
			plugins_url( 'css/cookies-and-content-security-policy-admin.min.css', __FILE__ ), 
			array(), 
			cacsp_get_plugin_version()
		);
		wp_enqueue_style( 'cookies-and-content-security-policy-admin' );
		wp_enqueue_script( 'wp-color-picker' );
	}
}

include_once( $cookies_and_cacsp_dir . 'inc/set-cacsp.php' );

include_once( $cookies_and_cacsp_dir . 'inc/settings-cacsp.php' );

include_once( $cookies_and_cacsp_dir . 'inc/modal-cacsp.php' );

include_once( $cookies_and_cacsp_dir . 'inc/plugin-compability.php' );

include_once( $cookies_and_cacsp_dir . 'inc/network.php' );

include_once( $cookies_and_cacsp_dir . 'inc/update.php' );
