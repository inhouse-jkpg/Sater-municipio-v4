<?php

/*
Plugin Name:    WPMU Invalidate Login Pagecache
Description:    Invalidates pagecache on submit of the login form.
Version:        1.0.0
Author:         Sebastian Thulin
*/

namespace WPMUInvalidateLoginPagecache;

class WPMUInvalidateLoginPagecache
{
    public function __construct()
    {

        add_action('login_enqueue_scripts', [$this, 'addLoginScript']);
    }

    public function addLoginScript()
    {   
        wp_enqueue_script('wpmu-invalidate-login-pagecache', plugins_url('assets/js/loginCacheInvalidator.js', __FILE__), array(), '1.0', true);
    }
}

new \WPMUInvalidateLoginPagecache\WPMUInvalidateLoginPagecache();
