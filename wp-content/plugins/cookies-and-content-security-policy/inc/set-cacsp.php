<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Set Content Security Policy
function set_content_security_policy() {
	$contentSecurityPolicyScript = '';
	$contentSecurityPolicyImg = '';
	$contentSecurityPolicyFrame = '';
	$contentSecurityPolicyForm = '';
	$contentSecurityPolicyWorker = '';
	$contentSecurityPolicy = '';
	$cacsp_option_debug = get_cacsp_options( 'cacsp_option_debug' );
	$cacsp_option_debug_text = '';
	$cacsp_option_forms = get_cacsp_options( 'cacsp_option_forms' );
	$cacsp_option_worker = get_cacsp_options( 'cacsp_option_worker' );
	$cacsp_option_blob = get_cacsp_options( 'cacsp_option_blob' );
	$cacsp_option_disable_unsafe_inline = get_cacsp_options( 'cacsp_option_disable_unsafe_inline' );
	$cacsp_option_disable_unsafe_eval = get_cacsp_options( 'cacsp_option_disable_unsafe_eval' );
	$cacsp_option_wpengine_compatibility_mode = get_cacsp_options( 'cacsp_option_wpengine_compatibility_mode' );
	$cacsp_option_bypass_ip = get_cacsp_options( 'cacsp_option_bypass_ip' );
	
	if ( $cacsp_option_debug ) {
		$cacsp_option_debug_text .= '<!-- Setting Content Security Policy -->' . "\n";
		$cacsp_option_debug_text .= '<!-- If you have saved your cookie settings and don\'t get a comment after this one saying that there is a cookie set your host is probably using cookie cache. Ask them to uncache cookies_and_content_security_policy. WP Engine is one of those hosts. -->' . "\n";	
	}
	$cacsp_bypass = 'false';
	// start ip check for bypass
	$cacsp_bypass_ip = 'false';
	if ( $cacsp_option_bypass_ip ) {
		$cacsp_option_bypass_ips = rtrim( get_cacsp_options( 'cacsp_option_bypass_ips' ) );
		$cacsp_option_bypass_ips_arr = array_map( 'trim', explode( "\n", $cacsp_option_bypass_ips ) );
		if ( in_array( $_SERVER['REMOTE_ADDR'], $cacsp_option_bypass_ips_arr ) ) {
			$cacsp_bypass_ip = 'true';
		}
	}
	// end ip check for bypass
	if ( isset( $_GET["cacsp_bypass"] ) || $cacsp_bypass_ip === 'true' ) {
		$cacsp_bypass = 'true';
		$cacsp_bypass_cookie_value = '["statistics","experience","markerting"]';
		$cacsp_option_debug_text .= '<!-- We are bypassing and accepting all -->' . "\n";
		setcookie( 'cookies_and_content_security_policy', $cacsp_bypass_cookie_value );
	}
	if ( isset( $_COOKIE["cookies_and_content_security_policy"] ) || isset($_SERVER["HTTP_X_WPENGINE_SEGMENT"]) || $cacsp_bypass == 'true' ) {
		if ( $cacsp_option_debug ) {
			$cacsp_option_debug_text .= '<!-- We have Content Security Policy Cookie set -->' . "\n";
		}
		if ( $cacsp_bypass == 'true' ) {
			$cookie_filter = $cacsp_bypass_cookie_value;
		} else {
			if ( $cacsp_option_wpengine_compatibility_mode === '1' ) {
				if ( $cacsp_option_debug ) {
					$cacsp_option_debug_text .= '<!-- Using WP Engine compatibility mode -->' . "\n";
				}
				if ( isset( $_SERVER["HTTP_X_WPENGINE_SEGMENT"] ) ) { 
					$cookie_filter = urldecode(str_replace( '\\', '', $_SERVER["HTTP_X_WPENGINE_SEGMENT"]));
				}
			} else {
				$cookie_filter = str_replace( '\\', '', $_COOKIE['cookies_and_content_security_policy'] );
			}
		}
		if ( $cookie_filter ) {
			if ( $cacsp_option_debug ) {
				$cacsp_option_debug_text .= '<!-- We have a Content Security Policy Cookie settings saved -->' . "\n";
				$cacsp_option_debug_text .= '<!-- Content Security Policy Cookie value: ' . $cookie_filter . ' -->' . "\n";
			}
			$cookie_filter_json = json_decode( $cookie_filter );
			if ( $cookie_filter_json ) {
				if ( $cacsp_option_debug ) {
					$cacsp_option_debug_text .= '<!-- We have a json in the Content Security Policy Cookie -->' . "\n";
				}
				foreach( $cookie_filter_json as $value ) {
					if ( $value == 'statistics' ) {
						if ( $cacsp_option_debug ) {
							$cacsp_option_debug_text .= '<!-- We have statistics in the Content Security Policy Cookie -->' . "\n";
						}
						$contentSecurityPolicyScript .= ' ' . get_cacsp_options( 'cacsp_option_statistics_scripts', true );
						$contentSecurityPolicyImg .= ' ' . get_cacsp_options( 'cacsp_option_statistics_images', true );
						$contentSecurityPolicyFrame .= ' ' . get_cacsp_options( 'cacsp_option_statistics_frames', true );
						if ( $cacsp_option_forms ) {
							$contentSecurityPolicyForm .= ' ' . get_cacsp_options( 'cacsp_option_statistics_forms', true );
						}
						if ( $cacsp_option_worker ) {
							$contentSecurityPolicyWorker .= ' ' . get_cacsp_options( 'cacsp_option_statistics_worker', true );
						}
					} else if ( $value == 'experience' ) {
						if ( $cacsp_option_debug ) {
							$cacsp_option_debug_text .= '<!-- We have experience in the Content Security Policy Cookie -->' . "\n";
						}
						$contentSecurityPolicyScript .= ' ' . get_cacsp_options( 'cacsp_option_experience_scripts', true );
						$contentSecurityPolicyImg .= ' ' . get_cacsp_options( 'cacsp_option_experience_images', true );
						$contentSecurityPolicyFrame .= ' ' . get_cacsp_options( 'cacsp_option_experience_frames', true );
						if ( $cacsp_option_forms ) {
							$contentSecurityPolicyForm .= ' ' . get_cacsp_options( 'cacsp_option_experience_forms', true );
						}
						if ( $cacsp_option_worker ) {
							$contentSecurityPolicyWorker .= ' ' . get_cacsp_options( 'cacsp_option_experience_worker', true );
						}
					} else if ( $value == 'markerting' ) {
						if ( $cacsp_option_debug ) {
							$cacsp_option_debug_text .= '<!-- We have markerting in the Content Security Policy Cookie -->' . "\n";
						}
						$contentSecurityPolicyScript .= ' ' . get_cacsp_options( 'cacsp_option_markerting_scripts', true );
						$contentSecurityPolicyImg .= ' ' . get_cacsp_options( 'cacsp_option_markerting_images', true );
						$contentSecurityPolicyFrame .= ' ' . get_cacsp_options( 'cacsp_option_markerting_frames', true );
						if ( $cacsp_option_forms ) {
							$contentSecurityPolicyForm .= ' ' . get_cacsp_options( 'cacsp_option_markerting_forms', true );
						}
						if ( $cacsp_option_worker ) {
							$contentSecurityPolicyWorker .= ' ' . get_cacsp_options( 'cacsp_option_markerting_worker', true );
						}
					}
				}
			}
		}
	}
	$contentSecurityPolicyBlob = "";
	if ( $cacsp_option_blob ) {
		$contentSecurityPolicyBlob = "blob: ";
	}
	$contentSecurityPolicyUnsafe = "";
	if ( !$cacsp_option_disable_unsafe_inline ) {
		$contentSecurityPolicyUnsafe .= " 'unsafe-inline'";
	}
	if ( !$cacsp_option_disable_unsafe_eval ) {
		$contentSecurityPolicyUnsafe .= " 'unsafe-eval'";
	}
	$contentSecurityPolicyScript = "script-src 'self'" . $contentSecurityPolicyUnsafe . " " . $contentSecurityPolicyBlob . cacsp_single_space( get_cacsp_options( 'cacsp_option_always_scripts', true ) . $contentSecurityPolicyScript ) . '; ';
	$contentSecurityPolicyImg = "img-src 'self' data: " . $contentSecurityPolicyBlob . cacsp_single_space( get_cacsp_options( 'cacsp_option_always_images', true ) . $contentSecurityPolicyImg ) . '; ';
	$cacsp_option_frames_js = cacsp_single_space( get_cacsp_options( 'cacsp_option_always_frames', true ) . $contentSecurityPolicyFrame );
	$contentSecurityPolicyObject = "object-src 'self' data: " . $contentSecurityPolicyBlob . cacsp_single_space( get_cacsp_options( 'cacsp_option_always_frames', true ) . $contentSecurityPolicyFrame ) . '; ';
	$contentSecurityPolicyFrame = "frame-src 'self' data: " . $contentSecurityPolicyBlob . cacsp_single_space( get_cacsp_options( 'cacsp_option_always_frames', true ) . $contentSecurityPolicyFrame ) . '; ';
	if ( $cacsp_option_forms ) {
		$contentSecurityPolicyForm = "form-action 'self' data: " . $contentSecurityPolicyBlob . cacsp_single_space( get_cacsp_options( 'cacsp_option_always_forms', true ) . $contentSecurityPolicyForm ) . '; ';
	}
	if ( $cacsp_option_worker ) {
		$contentSecurityPolicyWorker = "worker-src 'self' data:" . $contentSecurityPolicyUnsafe . " " . $contentSecurityPolicyBlob . cacsp_single_space( get_cacsp_options( 'cacsp_option_always_worker', true ) . $contentSecurityPolicyWorker ) . '; ';
	}
	$contentSecurityPolicy = $contentSecurityPolicyScript . $contentSecurityPolicyImg . $contentSecurityPolicyObject . $contentSecurityPolicyFrame . $contentSecurityPolicyForm . $contentSecurityPolicyWorker;
	if ( get_cacsp_options( 'cacsp_option_settings_policy_link' ) ) {
		$cacsp_option_debug_text .= '<!-- Cookie policy page ID is: ' . get_cacsp_options( 'cacsp_option_settings_policy_link' ) . ' -->' . "\n";
	}
	if ( $cacsp_option_debug ) {
		$cacsp_option_debug_text .= '<!-- Content Security Policy Cookie settings: ' . "\n" . str_replace( '; ', ';' . "\n", $contentSecurityPolicy ) . ' -->' . "\n";
	}
	if ( $cacsp_option_debug ) {
		add_action( 'wp_head', function() use ( $cacsp_option_debug_text ) {
		    echo $cacsp_option_debug_text . "\n";
		}, 0 );
	}
	if ( get_cacsp_options( 'cacsp_option_google_consent_mode', false, '0' ) == 1 ) {
		add_action( 'wp_head', function() {
		    echo "<script id=\"cacsp-gtag-consent-default\">
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('consent', 'default', {
				'ad_storage': 'denied', 
				'ad_user_data': 'denied', 
				'ad_personalization': 'denied', 
				'analytics_storage': 'denied', 
				'functionality_storage': 'denied', 
				'personalization_storage': 'denied', 
				'security_storage': 'denied', 
				'wait_for_update': 500
			});
			</script>\n";
		}, 0 );
	}
	$cacsp_option_meta = get_cacsp_options( 'cacsp_option_meta' );
	$cacsp_option_no_x_csp = get_cacsp_options( 'cacsp_option_no_x_csp' );
	if ( $cacsp_option_meta ) {
		add_action( 'wp_head', function() use ( $contentSecurityPolicy, $cacsp_option_no_x_csp ) {
			if ( !$cacsp_option_no_x_csp ) {
		    	echo '<meta http-equiv="X-Content-Security-Policy" content="' . $contentSecurityPolicy . '">' . "\n";
		    }
		    echo '<meta http-equiv="Content-Security-Policy" content="' . $contentSecurityPolicy . '">' . "\n";
		}, 1 );
	} else {
		if ( !$cacsp_option_no_x_csp ) {
			header( "X-Content-Security-Policy: " . $contentSecurityPolicy );
		}
		header( "Content-Security-Policy: " . $contentSecurityPolicy );
	}
}

function cacsp_single_space( $domains ) {
	return preg_replace( '/\s+/', ' ', trim( $domains ));
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'cacsp/v1', '/texts/', array(
		'methods' => 'GET',
		'callback' => 'cacsp_texts',
		'permission_callback' => '__return_true',
	) );
} );

function cacsp_texts( $data ) {
	$hide_admin_email = get_cacsp_options( 'cacsp_option_settings_admin_email' );;
	if ( $hide_admin_email ) {
		$admin_email = '';
	} else {
		$admin_email = get_option( 'admin_email' );
	}
	return array(
		'warning_texts' => array(
			'admin_email' => $admin_email,
		)
	);
}

// add_action( 'plugins_loaded', 'cacsp_init' ); // OLD:
// add_filter( 'wp_headers', 'cacsp_init' ); // New alternative
add_action( 'send_headers', 'cacsp_init', 10, 0 ); // https://docs.wpvip.com/infrastructure/http-headers/
add_action( 'login_enqueue_scripts', 'cacsp_init', 10, 0 );
function cacsp_init() {
	if ( !is_admin() && cacsp_option_actived() ) {
		set_content_security_policy();
	}
}
