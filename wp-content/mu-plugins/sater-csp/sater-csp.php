<?php
/**
 * Plugin Name: Säter CSP Extensions
 * Description: Extends Cookies and Content Security Policy with connect-src and style-src directives required by services such as Vizzit.
 * Version: 1.0.0
 * Author: Säter kommun
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/ExtendedContentSecurityPolicy.php';

add_action('plugins_loaded', 'sater_csp_replace_cacsp_header_handler', 20);
add_action('send_headers', 'sater_csp_send_headers', 10);
add_action('login_enqueue_scripts', 'sater_csp_send_headers', 10);

/**
 * Replace CACSP header output so we can append connect-src and style-src.
 */
function sater_csp_replace_cacsp_header_handler(): void
{
    if (!function_exists('cacsp_option_actived')) {
        return;
    }

    remove_action('send_headers', 'cacsp_init', 10);
    remove_action('login_enqueue_scripts', 'cacsp_init', 10);
}

/**
 * Send the extended Content Security Policy header on the front end.
 */
function sater_csp_send_headers(): void
{
    if (is_admin() || !function_exists('cacsp_option_actived') || !cacsp_option_actived()) {
        return;
    }

    SaterCsp\ExtendedContentSecurityPolicy::send();
}
