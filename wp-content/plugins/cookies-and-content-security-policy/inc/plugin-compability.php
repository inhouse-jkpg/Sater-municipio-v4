<?php
// WP Super Cache
if ( in_array( 'wp-super-cache/wp-cache.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { 
    // Tell WP Super Cache to cache requests with the cookie “Cookies and Content Security Policy” separately from other visitors.
    function cacsp_add_wpsc_cookie_banner() {
        do_action( 'wpsc_add_cookie', 'cookies_and_content_security_policy' );
    }
    add_action( 'init', 'cacsp_add_wpsc_cookie_banner' );
}
