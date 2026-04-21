<?php

/*
Plugin Name:    WPMU Network Admin Url
Description:    Fixes network url's when running WordPress as multisite in a composer installed wp instance.
Version:        1.1
Author:         Sebastian Thulin
*/

namespace ComposerNetworkAdmin;

class ComposerNetworkAdmin
{
    public function __construct()
    {
        add_filter('network_admin_url', array($this, 'sanitizeNetworkAdminUrl'), 50, 2);
        if($this->shouldRun()) {
            add_filter('admin_url', array($this, 'sanitizeAdminUrl'), 50, 3);
        }
    }

    public function shouldRun() {

        if(!defined('MULTISITE') || !defined('SUBDOMAIN_INSTALL')) {
            return false;
        }

        if(MULTISITE == false) {
            return false;
        } 

        if(SUBDOMAIN_INSTALL == false) {
            return false;
        }

        return true;
    }

    public function sanitizeAdminUrl($url, $path, $blog_id)
    {
        if (strpos($url, '/wp/wp-admin') === false && !strpos($url, '/network')) {
            return str_replace('/wp-admin/', '/wp/wp-admin/', $url);
        }
        return $url;
    }

    public function sanitizeNetworkAdminUrl($url, $path)
    {
        if (strpos($url, '/wp/wp-admin/network') === false && strpos($url, '/network')) {
            return str_replace('/wp-admin/', '/wp/wp-admin/', $url);
        }
        return $url;
    }
}

new \ComposerNetworkAdmin\ComposerNetworkAdmin();
