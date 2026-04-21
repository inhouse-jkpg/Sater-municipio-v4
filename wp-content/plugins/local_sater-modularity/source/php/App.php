<?php

namespace SaterModularity;

class App
{
    public function __construct()
    {
        //Add the latest module
        add_action('Modularity', function () {
            new \SaterModularity\LatestModule();
        });

        add_filter('acf/settings/load_json', array($this, 'jsonLoadPath'));
    }

    public function jsonLoadPath($paths)
    {
        $paths[] = SATERMODULARITY_PATH . 'source/acf-export';
        return $paths;
    }
}
