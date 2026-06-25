<?php
/**
 * Plugin Name: Säter Tillgänglighet (A11y)
 * Description: Accessibility fixes for WCAG compliance: sticky header and archive date picker.
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

function sater_a11y_should_enqueue_archive_datepicker(): bool
{
    if (is_admin()) {
        return false;
    }

    return is_archive() || is_author();
}

function sater_a11y_enqueue_archive_datepicker(): void
{
    if (!sater_a11y_should_enqueue_archive_datepicker()) {
        return;
    }

    wp_enqueue_script('jquery-ui-datepicker');

    $cssPath = __DIR__ . '/assets/css/archive-datepicker.css';
    if (is_readable($cssPath)) {
        wp_enqueue_style(
            'sater-a11y-archive-datepicker',
            plugin_dir_url(__FILE__) . 'assets/css/archive-datepicker.css',
            ['styleguide-css', 'municipio-css'],
            (string) filemtime($cssPath)
        );
    }

    $jsPath = __DIR__ . '/assets/js/archive-datepicker.js';
    if (is_readable($jsPath)) {
        wp_enqueue_script(
            'sater-a11y-archive-datepicker',
            plugin_dir_url(__FILE__) . 'assets/js/archive-datepicker.js',
            ['jquery', 'jquery-ui-datepicker'],
            (string) filemtime($jsPath),
            true
        );
    }
}

function sater_a11y_enqueue_assets(): void
{
    sater_a11y_enqueue_archive_datepicker();
    $cssPath = __DIR__ . '/assets/css/mobile-header.css';
    $jsPath  = __DIR__ . '/assets/js/header-scroll.js';

    if (file_exists($cssPath)) {
        wp_enqueue_style(
            'sater-a11y-mobile-header',
            plugin_dir_url(__FILE__) . 'assets/css/mobile-header.css',
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
