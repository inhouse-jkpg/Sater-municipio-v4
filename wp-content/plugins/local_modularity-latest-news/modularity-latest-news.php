<?php

/**
 * Plugin Name:       Modularity Latest Events
 * Plugin URI:        https://github.com/helsingborg-stad/modularity-latest-news
 * Description:       Display Latest Events
 * Version: 1.0.0
 * Author:            Niklas Holmgren
 * Author URI:        #
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       mod-latest-news
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITYLATEST_NEWS_PATH', plugin_dir_path(__FILE__));
define('MODULARITYLATEST_NEWS_URL', plugins_url('', __FILE__));
define('MODULARITYLATEST_NEWS_TEMPLATE_PATH', MODULARITYLATEST_NEWS_PATH . 'templates/');
define('MODULARITYLATEST_NEWS_VIEW_PATH', MODULARITYLATEST_NEWS_PATH . 'views/');
define('MODULARITYLATEST_NEWS_MODULE_VIEW_PATH', plugin_dir_path(__FILE__) . 'source/php/Module/views');
define('MODULARITYLATEST_NEWS_MODULE_PATH', MODULARITYLATEST_NEWS_PATH . 'source/php/Module/');


// Autoload from plugin
if (file_exists(MODULARITYLATEST_NEWS_PATH . 'vendor/autoload.php')) {
    require_once MODULARITYLATEST_NEWS_PATH . 'vendor/autoload.php';
}
require_once MODULARITYLATEST_NEWS_PATH . 'Public.php';

// Acf auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-latest-news');
    $acfExportManager->setExportFolder(MODULARITYLATEST_NEWS_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'latest-news' => 'group_61ea7a87e8e9f'
    ));
    $acfExportManager->import();
});

// Modularity 3.0 ready - ViewPath for Component library
add_filter('/Modularity/externalViewPath', function ($arr) {
    $arr['mod-latest-news'] = MODULARITYLATEST_NEWS_MODULE_VIEW_PATH;
    return $arr;
}, 10, 3);

// Start application
new ModularityLatestNewsEvents\App();
