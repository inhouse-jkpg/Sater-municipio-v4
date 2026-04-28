<?php

    //Tests of functionality.
    error_reporting(E_ALL);

    //Wp false functions
    function add_filter($a, $b)
    {
        return $a;
    };

    function add_action($a, $b)
    {
        return $a;
    };

    function home_url()
    {
        return "http://homeurl.com";
    }

    //The good stuff
    require 'App.php';

    //Run it!
    $ssl = new ForceSSL\App();

    echo $ssl->replaceInlineUrls("This is a string containing a usual http://homeurl.com/ and we want to make it an https://homeurl.com.");
