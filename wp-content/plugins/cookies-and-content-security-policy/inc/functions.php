<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function get_cacsp_options( $option, $new_line_to_space = false, $fallback = '', $esc = false ) {

	$cacsp_option_use = false;
	if ( is_multisite() ) {
		$cacsp_site_id = get_main_site_id();
		$cacsp_option_use = get_blog_option( $cacsp_site_id, 'cacsp_option_use' );
		if ( $cacsp_option_use == 'some' && ( strpos( $option, 'cacsp_option_text_' ) > -1 || strpos( $option, '_button' ) > -1 || strpos( $option, '_description' ) > -1 ) ) {
			$cacsp_site_id = get_current_blog_id();
		}
	}
	if ( !$cacsp_option_use ) {
		$cacsp_option_use = 'default';
	}
	
	if ( is_multisite() && $cacsp_option_use != 'default' ) {
		$option = get_blog_option( $cacsp_site_id, $option );
	} else {
		$option = get_option( $option );
	}

	if ( !$option && strlen( $fallback ) > 0 ) {
		$option = $fallback;
	}
	if ( $new_line_to_space ) {
		$remove = array( "\n", "\r\n", "\r" );
		$option = str_replace( $remove, ' ', $option );
	}
	if ( $esc ) {
		$option = esc_attr( $option );
	}
	return stripslashes( $option );
}

function cacsp_sanitize_text_field_with_html( $str ) {
	$allowed_html = array(
	    'i' => array(
	        'class' => array(),
	        'aria-hidden' => array()
	    ),
	    'br' => array(),
	    'em' => array(),
	    'strong' => array(),
	    'p' => array(),
	);
	$str_with_html = wp_kses( $str, $allowed_html );
	return $str_with_html;
}

function cacsp_sanitize_domains( $domains, $new_row = true ) {
	$domains_arr = explode( "\n", $domains );
	$clean_domains = '';
	foreach ( $domains_arr as &$domain ) {
		$clean_domain = esc_url_raw( $domain );
		if ( !empty( $clean_domain ) ) {
	    	$clean_domains .= esc_url_raw( $clean_domain );
	    }
	    if ( $new_row && $clean_domain ) {
	    	$clean_domains .= "\n";
	    }
	}
	return $clean_domains;
}

function cacsp_sanitize_ip( $str ) {
	$cacsp_sanitize_ip = sanitize_textarea_field( $str );
	return $cacsp_sanitize_ip;
}

function cacsp_option_actived() {
	$cacsp_option_actived = false;
	$cacsp_main_site_id = null;
	$cacsp_option_use = 'default';
	if ( is_multisite() ) {
		$cacsp_main_site_id = get_main_site_id();
		$cacsp_option_use = get_blog_option( $cacsp_main_site_id, 'cacsp_option_use' );
	}
	if ( current_user_can( 'manage_options' ) && get_cacsp_options( 'cacsp_option_actived' ) == 'admin' || get_cacsp_options( 'cacsp_option_actived' ) == 'true' || is_multisite() && $cacsp_option_use == 'some' && get_cacsp_options( 'cacsp_option_actived' ) ) {
		$cacsp_option_actived = true;
	}
	return $cacsp_option_actived;
}

function cacsp_get_plugin_version() {
	$plugin_version = get_file_data( plugin_dir_path( __DIR__ ) . 'cookies-and-content-security-policy.php', array( 'Version' => 'Version' ), false )['Version'];
	return esc_attr( $plugin_version );
}

function cacsp_get_protocol() {
	$protocol = ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) ? "https://" : "http://";
	return $protocol;
}

function cacsp_option_use() {
	return get_cacsp_options( 'cacsp_option_use' );
}

function cacsp_hide_admin_for_sub_sites() {
	if ( is_multisite() ) {
		$cacsp_main_site_id = get_main_site_id();
		$cacsp_current_site_id = get_current_blog_id();
		$cacsp_option_use = get_blog_option( $cacsp_main_site_id, 'cacsp_option_use' );
		if ( $cacsp_option_use == 'everything' && $cacsp_main_site_id !== $cacsp_current_site_id ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function cacsp_save_error_message_js( $blog_id = null, $check_exists = false ) {
	//header( 'Content-Type: application/javascript' );
	if ( $blog_id ) {
		switch_to_blog( $blog_id );
	}
	$js = 'var CACSP_COOKIE_NAME = \'cookies_and_content_security_policy\';
	if (cacspMessages.cacspWpEngineCompatibilityMode === \'1\') {
		CACSP_COOKIE_NAME = \'wpe-us\';
	}';
	$js .= "\n";
	// Always
	$js .= 'var cacspalways = "' . get_cacsp_options( 'cacsp_option_always_frames', true ) . '";';
	$js .= "\n";
	// Statistics
	$js .= 'var cacspstatistics = "' . get_cacsp_options( 'cacsp_option_statistics_frames', true ) . '";';
	$js .= "\n";
	// Experience
	$js .= 'var cacspexperience = "' . get_cacsp_options( 'cacsp_option_experience_frames', true ) . '";';
	$js .= "\n";
	// Marketing
	$js .= 'var cacspmarkerting = "' . get_cacsp_options( 'cacsp_option_markerting_frames', true ) . '";';
	$js .= "\n";

	$js .= "if (Cookies.get(CACSP_COOKIE_NAME)) {
		cookie_filter = JSON.parse(Cookies.get(CACSP_COOKIE_NAME));
		if (cookie_filter) {
			var cacspAllowedDomains = cacspalways;
			jQuery.each(cookie_filter, function( index, value ) {
				cacspAllowedDomains += window['cacsp' + value];
			});
			jQuery(window).on('load', function() {
				cookiesAndContentPolicyErrorMessage(cacspAllowedDomains, '" . home_url() . "');
			});
		}
	} else {
		jQuery(window).on('load', function() {
			var cacspAllowedDomains = cacspalways;
			cookiesAndContentPolicyErrorMessage(cacspAllowedDomains, '" . home_url() . "');
		});
	}";

	$js .= "\n";
	$js .= "if (window.MutationObserver) {
		observer = new MutationObserver(function (mutations) {
			mutations.forEach(function (mutation) {
				mutation.addedNodes.forEach(function (node) {
					if (typeof node.getElementsByTagName !== 'function') {
						return;
					}
					var elements = node.querySelectorAll('iframe, object');
					var elementsArr = Array.from(elements);
					elementsArr.forEach(function () {
						cookiesAndContentPolicyErrorMessage(cacspAllowedDomains, '" . home_url() . "');
					});
				});
			});
		});
		observer.observe(document, {
			attributes: true,
			childList: true,
			attributeOldValue: true,
			subtree: true,
		});
	}";
	$js = str_replace(array("\n", "\r", "\t"), '', $js);
	$blog_id_js = '';
	if ( is_multisite() ) {
		$cacsp_site_id = get_main_site_id();
		$cacsp_option_use = get_blog_option( $cacsp_site_id, 'cacsp_option_use' );
		if ( $cacsp_option_use == 'default' ) {
			$blog_id_js = '-blog-id-' . get_current_blog_id();
		} 
		if ( $cacsp_option_use == 'default' && !$blog_id ) {
			//switch_to_blog( $cacsp_site_id );
			$sites = get_sites();
			foreach( $sites as $site ) {
				$site_id = get_object_vars($site)["blog_id"];
				cacsp_save_error_message_js( $site_id, $check_exists );
			}
			restore_current_blog();
		} 
	}
    $file = cacsp_get_error_message_js_dir() . '/cookies-and-content-security-policy-error-message' . $blog_id_js . '.js'; 
	if ( $check_exists && !file_exists( $file ) || !$check_exists ) {
		$open = fopen( $file, "w" ); 
		fwrite( $open, $js ); 
		fclose( $open );
	}
}

function cacsp_get_asset_last_modified_time( $path ) {
	return gmdate( 'YmdHis', filemtime( $path ) );
}

function cacsp_get_error_message_js_dir() {
	return wp_get_upload_dir()['basedir'];
}

function cacsp_insert_consent_data() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'cacsp_consent';
	$accepted_cookies = str_replace( 'markerting', 'marketing', $_POST['accepted_cookies'] );
	$expires = $_POST['expires'];
	if ( is_multisite() ) {
		$blog_id = get_current_blog_id();
	} else {
		$blog_id = 1;
	}
	$wpdb->insert( 
		$table_name, 
		array( 
			'time' => sanitize_text_field( current_time( 'mysql' ) ), 
			'ip' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ), 
			'accepted_cookies' => sanitize_text_field( $accepted_cookies ) . ' ' . $inloggad, 
			'expires' => sanitize_text_field( $expires ), 
			'site' => sanitize_text_field( $blog_id ), 
		) 
	);
}
add_action( 'wp_ajax_cacsp_insert_consent_data', 'cacsp_insert_consent_data' );
add_action( 'wp_ajax_nopriv_cacsp_insert_consent_data', 'cacsp_insert_consent_data' );
