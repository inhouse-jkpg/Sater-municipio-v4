<?php
/*
 * Plugin Name: Force SSL
 * Plugin URI: -
 * Description: Force users to use ssl
 * Version: 3.0.2
 * Author: Sebastian Thulin
 * Author URI: -
 * Text domain: force-ssl
 *
 * Copyright (C) 2016
 */

//Location definition
define('FORCE_SSL_PATH', plugin_dir_path(__FILE__));

//The good stuff
require FORCE_SSL_PATH . 'App.php';

//Run it!
new ForceSSL\App();

//Admin interface
if (!defined('FORCE_SSL_ADMIN')) {
    define('FORCE_SSL_ADMIN', true);
}
