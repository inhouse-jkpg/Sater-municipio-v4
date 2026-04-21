<?php

namespace ModularityInteractiveMap;

use ModularityInteractiveMap\Helper\CacheBust;

class App
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

        add_action('plugins_loaded', array($this, 'registerModule'));
    }

    /**
     * Register the module
     * @return void
     */
    public function registerModule()
    {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITY_INTERACTIVE_MAP_MODULE_PATH, 
                'InteractiveMap' 
            );
        }
    }

    /**
     * Enqueue required style
     * @return void
     */
    public function enqueueStyles()
    {
        global $current_screen;

        if ($current_screen->id !== 'mod-interactive-map') {
            return;
        }

        wp_enqueue_style('modularity-interactive-map', MODULARITY_INTERACTIVE_MAP_URL . '/dist/' . CacheBust::name('css/modularity-interactive-map.css'), null, '3.0.0');
    }

    /**
     * Enqueue required scripts
     * @return void
     */
    public function enqueueScripts()
    {
        global $current_screen;

        if ($current_screen->id !== 'mod-interactive-map') {
            return;
        }

        wp_enqueue_script('modularity-interactive-map', MODULARITY_INTERACTIVE_MAP_URL . '/dist/' . CacheBust::name('js/modularity-interactive-map-admin.js'), array('jquery'), '3.0.0', false);
        wp_localize_script('modularity-interactive-map', 'ModInteractiveMapLang', array(
            'close' => __('Close'),
            'remove' => __('Remove'),
            'description' => __('Description'),
            'link' => __('Link', 'modularity-interactive-map'),
            'title' => __('Title')
        ));
    }
}
