<?php
/**
 * Plugin Name: Modularity Latest Events (MU)
 * Description: Loads the Modularity Latest Events package from mu-plugins. WordPress only auto-loads PHP files in the mu-plugins root, not subfolders.
 *
 * @package Sater
 */

if (!defined('ABSPATH')) {
    exit;
}

// Same directory as this loader (wp-content/mu-plugins/). Avoid relying on WPMU_PLUGIN_DIR alone.
$bootstrap = __DIR__ . '/local_modularity-latest-news/modularity-latest-news.php';
if (!is_readable($bootstrap)) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[local-modularity-latest-news] Missing bootstrap: ' . $bootstrap);
    }
    return;
}

require_once $bootstrap;
