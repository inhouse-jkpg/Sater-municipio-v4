<?php
/**
 * Plugin Name: Säter Tillgänglighet (A11y)
 * Description: Accessibility fixes for WCAG compliance: sticky header in landscape.
 * Version: 1.0.0
 * Author: Säter kommun
 * License: MIT
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', 'sater_a11y_enqueue_assets', 100);

function sater_a11y_enqueue_assets(): void
{
    $cssPath = __DIR__ . '/assets/css/landscape.css';
    $jsPath  = __DIR__ . '/assets/js/header-scroll.js';

    if (file_exists($cssPath)) {
        wp_enqueue_style(
            'sater-a11y-landscape',
            plugin_dir_url(__FILE__) . 'assets/css/landscape.css',
            ['styleguide-css', 'municipio-css'],
            (string) filemtime($cssPath)
        );
    }

    // Only enqueue the scroll script when the header is configured as sticky.
    if (get_theme_mod('header_sticky') === 'sticky' && file_exists($jsPath)) {
        wp_enqueue_script(
            'sater-a11y-header-scroll',
            plugin_dir_url(__FILE__) . 'assets/js/header-scroll.js',
            [],
            (string) filemtime($jsPath),
            true
        );
    }
}
