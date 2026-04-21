<?php

/**
 * Plugin Name:       Säter Modularity Modules
 * Plugin URI:        
 * Description:       Custom modules for Säter.se
 * Version:           1.0.0
 * Author:            Jonas Hultenius
 * Author URI:        jonas.hultenius@sogeti.se
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       SaterModularity
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('SATERMODULARITY_PATH', plugin_dir_path(__FILE__));
define('SATERMODULARITY_URL', plugins_url('', __FILE__));
define('SATERMODULARITY_TEMPLATE_PATH', SATERMODULARITY_PATH . 'templates/');

load_plugin_textdomain('SaterModularity', false, plugin_basename(dirname(__FILE__)) . '/languages');

require_once SATERMODULARITY_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once SATERMODULARITY_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new SaterModularity\Vendor\Psr4ClassLoader();
$loader->addPrefix('SaterModularity', SATERMODULARITY_PATH);
$loader->addPrefix('SaterModularity', SATERMODULARITY_PATH . 'source/php/');
$loader->register();

// Start application
new SaterModularity\App();
