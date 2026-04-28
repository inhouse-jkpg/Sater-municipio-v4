<?php

namespace SaterCustomTypes;

class App
{
    public function __construct()
    {
        add_filter('acf/settings/load_json', array($this, 'jsonLoadPath'));
     
       new News();
       new Events();
       //   new GlobalFilter(); används ej
       //   new WarningMessage(); depracted. eget plugin
    }

    public function jsonLoadPath($paths)
    {
        $paths[] = SATERCUSTOMTYPES_PATH . 'source/acf-export';
        return $paths;
    }
}
