<?php

namespace ModularityLatestNewsEvents;

class App
{
    public function __construct()
    {

        //Register module
        add_action('plugins_loaded', array($this, 'registerModule'));

        // Add view paths
        add_filter('Municipio/blade/view_paths', array($this, 'addViewPaths'), 1, 1);

    }


    /**
     * Register the module
     * @return void
     */
    public function registerModule()
    {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITYLATEST_NEWS_MODULE_PATH,
                'LatestNews'
            );
        }
    }

    /**
     * Add searchable blade template paths
     * @param array  $array Template paths
     * @return array        Modified template paths
     */
    public function addViewPaths($array)
    {
        // Templates live under source/php/Module/views (not the empty "views/" folder).
        $path = MODULARITYLATEST_NEWS_MODULE_VIEW_PATH;
        if (!is_dir($path)) {
            return $array;
        }

        if (is_child_theme()) {
            array_splice($array, 2, 0, array($path));
        } else {
            array_unshift($array, $path);
        }

        return $array;
    }

}
