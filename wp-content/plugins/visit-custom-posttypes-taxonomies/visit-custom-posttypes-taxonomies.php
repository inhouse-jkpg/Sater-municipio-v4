<?php

/*
 * Plugin Name: Visit Helsingborg: Custom Post Types, Taxonomies and ACF Fields
 * Plugin URI: -
 * Description:
 * Version: 2.0.6
 * Author: Anna Johansson
 * Author URI: -
 * Text domain: visit
 */
/**
 * Composer autoloader from plugin
 */

if (file_exists(plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
}

/**
 * Instantiate main plugin class
 */
Visit\App::instance();

load_plugin_textdomain('visit', false, dirname(plugin_basename(__FILE__)) . '/languages');
