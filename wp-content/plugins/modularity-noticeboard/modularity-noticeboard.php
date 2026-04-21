<?php

/**
 * Plugin Name:       Modularity Digital Noticeboard
 * Plugin URI:        https://github.com/horby/modularity-noticeboard
 * Description:       Adds Noticeboard modules to Modularity
 * Version:           1.1.0
 * Author:            Anders Fajerson, Michael Claesson
 * Author URI:        https://github.com/horby
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-noticeboard
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITYNOTICEBOARD_PATH', plugin_dir_path(__FILE__));
define('MODULARITYNOTICEBOARD_URL', plugins_url('', __FILE__));
define('MODULARITYNOTICEBOARD_TEMPLATE_PATH', MODULARITYNOTICEBOARD_PATH . 'templates/');

define('MODULARITYNOTICEBOARD_POST_TYPE', 'announcement');
define('MODULARITYNOTICEBOARD_TAXONOMY', 'announcement_type');

define('MODULARITYNOTICEBOARD_MEETINGDATE_ACF_FIELD', 'field_57db7f7cb2a54');
define('MODULARITYNOTICEBOARD_ANNOUNCEMENT_TYPE_ACF_FIELD', 'field_58bfb86c760e3');
define('MODULARITYNOTICEBOARD_CITY_COUNCIL_SLUG', 'kommunfullmaktiges-sammantrade');

load_plugin_textdomain('modularity-noticeboard', false, plugin_basename(dirname(__FILE__)) . '/languages');

require_once MODULARITYNOTICEBOARD_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once MODULARITYNOTICEBOARD_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new ModularityNoticeboard\Vendor\Psr4ClassLoader();
$loader->addPrefix('ModularityNoticeboard', MODULARITYNOTICEBOARD_PATH);
$loader->addPrefix('ModularityNoticeboard', MODULARITYNOTICEBOARD_PATH . 'source/php/');
$loader->register();

// Start application
$modularityNoticeboardApp = new ModularityNoticeboard\App();

// Acf auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-noticeboard');
    $acfExportManager->setExportFolder(MODULARITYNOTICEBOARD_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'module' => 'group_57da9a06e21a8',
        'post-type' => 'group_57db7edf10454',
    ));
    $acfExportManager->import();
});

register_activation_hook(__FILE__, array($modularityNoticeboardApp, 'install'));
register_deactivation_hook(__FILE__, array($modularityNoticeboardApp, 'uninstall'));
