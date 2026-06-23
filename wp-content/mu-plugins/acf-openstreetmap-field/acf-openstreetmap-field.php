<?php

/*
Plugin Name:    Acf OpenStreetMap Field
Description:    Open Street Map field for Advanced Custom Fields
Version:        1.0
Author:         Niclas Norin
*/

use AcfOpenStreetMap\CacheBust;

if (! defined('WPINC')) {
    die;
}

define('ACFOPENSTREETMAP_PATH', plugin_dir_path(__FILE__));
define('ACFOPENSTREETMAP_URL', plugins_url('', __FILE__));

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

add_action('init', function () {
    $domain = 'acf-openstreetmap-field';
    $locale = determine_locale();
    $mofile = plugin_dir_path(__FILE__) . 'languages/' . $domain . '-' . $locale . '.mo';

    load_textdomain($domain, $mofile);
});

add_action( 'acf/include_field_types', 'addAcfOpenStreetMapField');

add_action('admin_enqueue_scripts', 'loadScriptsAndStyle', 10);
add_action('enqueue_block_editor_assets', 'loadScriptsAndStyle', 10);

function getCacheBust() {
    static $cacheBust = null;

    if ($cacheBust === null) {
        $cacheBust = new CacheBust();
    }

    return $cacheBust;
}

function addAcfOpenStreetMapField() {
    require_once ACFOPENSTREETMAP_PATH . 'source/php/field.php';
}

/**
 * Enqueue scripts and styles in the admin
 */
function loadScriptsAndStyle() {

    wp_register_style(
        'css-main',
        ACFOPENSTREETMAP_URL . '/dist/' .
        getCacheBust()->name('css/main-map.css')
    );

    wp_register_script(
        'js-init-map',
        ACFOPENSTREETMAP_URL . '/dist/' .
        getCacheBust()->name('js/init-map.js'),
        array('acf-input', 'jquery'),
    );

    wp_localize_script('js-init-map', 'language', \AcfOpenStreetMap\Lang::getLang());    

    wp_enqueue_script('js-init-map');
    wp_enqueue_style('css-main');
}

?>