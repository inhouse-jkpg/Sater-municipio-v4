<?php

namespace FlowboxModularity;

class App
{
    public function __construct()
    {
        //Add the available jobs module
        add_action('Modularity', function () {
            new \FlowboxModularity\Flowbox();
        });

        add_filter('acf/settings/load_json', array($this, 'jsonLoadPath'));
    }

    public function jsonLoadPath($paths)
    {
        $paths[] = FLOWBOXMODULARITY_PATH . 'source/acf-export';
        return $paths;
    }

}
