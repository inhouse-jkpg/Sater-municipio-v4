<?php

/**
 * Plugin Name:       Modularity Flowbox Module
 * Plugin URI:
 * Version:           1.0.0
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-flowbox
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('FLOWBOXMODULARITY_PATH', plugin_dir_path(__FILE__));
define('FLOWBOXMODULARITY_URL', plugins_url('', __FILE__));
define('FLOWBOXMODULARITY_TEMPLATE_PATH', FLOWBOXMODULARITY_PATH . 'templates/');

load_plugin_textdomain('FlowboxModularity', false, plugin_basename(dirname(__FILE__)) . '/languages');

require_once FLOWBOXMODULARITY_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once FLOWBOXMODULARITY_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new FlowboxModularity\Vendor\Psr4ClassLoader();
$loader->addPrefix('FlowboxModularity', FLOWBOXMODULARITY_PATH);
$loader->addPrefix('FlowboxModularity', FLOWBOXMODULARITY_PATH . 'source/php/');
$loader->register();

// Start application
new FlowboxModularity\App();
