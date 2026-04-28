<?php

/**
 * Plugin Name:       Säter Custom Post Types
 * Plugin URI:
 * Description:       Just some simple post types for news and events.
 * Version:           1.0.0
 * Author:            Jonas Hultenius
 * Author URI:        jonas.hultenius@sogeti.se
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       sater-custom-types
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('SATERCUSTOMTYPES_PATH', plugin_dir_path(__FILE__));
define('SATERCUSTOMTYPES_URL', plugins_url('', __FILE__));
define('SATERCUSTOMTYPES_TEMPLATE_PATH', SATERCUSTOMTYPES_PATH . 'templates/');

load_plugin_textdomain('sater-custom-types', false, plugin_basename(dirname(__FILE__)) . '/languages');

require_once SATERCUSTOMTYPES_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once SATERCUSTOMTYPES_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new SaterCustomTypes\Vendor\Psr4ClassLoader();
$loader->addPrefix('SaterCustomTypes', SATERCUSTOMTYPES_PATH);
$loader->addPrefix('SaterCustomTypes', SATERCUSTOMTYPES_PATH . 'source/php/');
$loader->register();

// Start application
new SaterCustomTypes\App();
