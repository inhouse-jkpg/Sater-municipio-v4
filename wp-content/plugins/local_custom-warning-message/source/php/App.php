<?php

namespace CustomWarningMessage;

class App
{
    public function __construct()
    {
        add_filter('acf/settings/load_json', array($this, 'jsonLoadPath'));

        new WarningMessage();
    }

    public function jsonLoadPath($paths)
    {
        $paths[] = CUSTOMWARNINGMESSAGE_PATH . 'source/acf-export';
        return $paths;
    }
}
