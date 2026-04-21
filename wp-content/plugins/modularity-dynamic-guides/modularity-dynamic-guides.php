<?php

/**
 * Plugin Name:       Modularity Dynamic Guides
 * Plugin URI:        https://github.com/NiclasNorin/modularity-dynamic-guides
 * Description:       A plugin to create dynamic guides
 * Version: 1.4.9
 * Author:            Niclas Norin
 * Author URI:        https://github.com/NiclasNorin
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-dynamic-guides
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITYDYNAMICGUIDES_PATH', plugin_dir_path(__FILE__));
define('MODULARITYDYNAMICGUIDES_URL', plugins_url('', __FILE__));
define('MODULARITYDYNAMICGUIDES_TEMPLATE_PATH', MODULARITYDYNAMICGUIDES_PATH . 'templates/');
define('MODULARITYDYNAMICGUIDES_TEXT_DOMAIN', 'modularity-dynamic-guides');
define('MODULARITYDYNAMICGUIDES_VIEW_PATH', MODULARITYDYNAMICGUIDES_PATH . 'views/');
define('MODULARITYDYNAMICGUIDES_MODULE_VIEW_PATH', MODULARITYDYNAMICGUIDES_PATH . 'source/php/Module/views');

require_once MODULARITYDYNAMICGUIDES_PATH . 'Public.php';

// Register the autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

load_plugin_textdomain('modularity-dynamic-guides', false, plugin_basename(dirname(__FILE__)) . '/languages');

add_filter('/Modularity/externalViewPath', function ($arr) {
    $arr['mod-dynamic-guide'] = MODULARITYDYNAMICGUIDES_MODULE_VIEW_PATH;
    return $arr;
}, 10, 3);

// Acf auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-dynamic-guides');
    $acfExportManager->setExportFolder(MODULARITYDYNAMICGUIDES_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'modularity-dynamic-guides-settings' => 'group_65b3a530b28a9' //Update with acf id here, settings view
    ));
    $acfExportManager->import();
});


// Start application
new ModularityDynamicGuides\App();
