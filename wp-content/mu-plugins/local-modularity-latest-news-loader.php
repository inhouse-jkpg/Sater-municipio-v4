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

require_once WPMU_PLUGIN_DIR . '/local_modularity-latest-news/modularity-latest-news.php';
