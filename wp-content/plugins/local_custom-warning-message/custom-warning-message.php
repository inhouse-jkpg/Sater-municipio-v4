<?php

/**
 * Plugin Name:       Custom Warning Message
 * Plugin URI:
 * Description:       Simple warning message with options.
 * Version:           1.0.0
 * Author:            Jonas Hultenius/Annelie Viklund
 * Author URI:        annelie.viklund@sogeti.se jonas.hultenius@sogeti.se
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       gavle-custom-types
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('CUSTOMWARNINGMESSAGE_PATH', plugin_dir_path(__FILE__));
define('CUSTOMWARNINGMESSAGE_URL', plugins_url('', __FILE__));
define('CUSTOMWARNINGMESSAGE_TEMPLATE_PATH', CUSTOMWARNINGMESSAGE_PATH . 'templates/');

load_plugin_textdomain('custom-warning-message', false, plugin_basename(dirname(__FILE__)) . '/languages');

require_once CUSTOMWARNINGMESSAGE_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once CUSTOMWARNINGMESSAGE_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new CustomWarningMessage\Vendor\Psr4ClassLoader();
$loader->addPrefix('CustomWarningMessage', CUSTOMWARNINGMESSAGE_PATH);
$loader->addPrefix('CustomWarningMessage', CUSTOMWARNINGMESSAGE_PATH . 'source/php/');
$loader->register();

// Start application
new CustomWarningMessage\App();
