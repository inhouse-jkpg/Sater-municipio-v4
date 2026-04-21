<?php

/**
 * Plugin Name:       Easy reading
 * Plugin URI:
 * Description:       Adds easy to read alternative content version
 * Version: 3.0.5
 * Author:            Jonatan Hanson
 * Author URI:
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       easy-reading
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('EASYREADING_PATH', plugin_dir_path(__FILE__));
define('EASYREADING_URL', plugins_url('', __FILE__));
define('EASYREADING_TEMPLATE_PATH', EASYREADING_PATH . 'templates/');

add_action('init', function() {
    load_plugin_textdomain('easy-reading', false, plugin_basename(dirname(__FILE__)) . '/languages');
});

// Autoload from plugin
if (file_exists(EASYREADING_PATH . 'vendor/autoload.php')) {
    require_once EASYREADING_PATH . 'vendor/autoload.php';
}

require_once EASYREADING_PATH . 'Public.php';

// Acf auto import and export
add_action('plugins_loaded', function () {
    $acfExportManager = new AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('easy-reading');
    $acfExportManager->setExportFolder(EASYREADING_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'easy-reading' 			=> 'group_58eb4fce51bb7',
        'easy-reading-options' 	=> 'group_58eb9450b0a9f',
    ));
    $acfExportManager->import();
});

// Start application
new EasyReading\App();
