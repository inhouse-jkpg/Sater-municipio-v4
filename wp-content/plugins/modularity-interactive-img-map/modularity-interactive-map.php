<?php

/**
 * Plugin Name:       Modularity Interactive Image Map v3
 * Plugin URI:
 * Description:       Build a interactive image map in a Modularity Module
 * Version: 4.0.4
 * Author:            Guy Incognito
 * Author URI:
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-interactive-map
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITY_INTERACTIVE_MAP_PATH', plugin_dir_path(__FILE__));
define('MODULARITY_INTERACTIVE_MAP_URL', plugins_url('', __FILE__));
define('MODULARITY_INTERACTIVE_MAP_TEMPLATE_PATH', MODULARITY_INTERACTIVE_MAP_PATH . 'templates/');

define('MODULARITY_INTERACTIVE_MAP_MODULE_VIEW_PATH', MODULARITY_INTERACTIVE_MAP_PATH . 'source/php/Module/views');
define('MODULARITY_INTERACTIVE_MAP_MODULE_PATH', MODULARITY_INTERACTIVE_MAP_PATH . 'source/php/Module/');

load_plugin_textdomain('modularity-interactive-map', false, plugin_basename(dirname(__FILE__)) . '/languages');

// Autoload from plugin
if (file_exists(MODULARITY_INTERACTIVE_MAP_PATH . 'vendor/autoload.php')) {
    require_once MODULARITY_INTERACTIVE_MAP_PATH . 'vendor/autoload.php';
}
require_once MODULARITY_INTERACTIVE_MAP_PATH . 'Public.php';

// View paths
add_filter('Municipio/blade/view_paths', function ($array){
    return $array;
}, 2, 1);
add_filter('/Modularity/externalViewPath', function ($arr) {
    $arr['mod-interactive-map'] = MODULARITY_INTERACTIVE_MAP_MODULE_VIEW_PATH;
    return $arr;
}, 10, 3);

// Start application
new ModularityInteractiveMap\App();
