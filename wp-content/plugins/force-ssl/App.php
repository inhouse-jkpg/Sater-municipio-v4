<?php

namespace ForceSSL;

class App
{
    public function __construct()
    {

        // Basic force
        $this->forceSSL();

        //Redirects
        add_action('template_redirect', array($this, 'redirectToSSL'), 5);
        add_action('admin_init', array($this, 'redirectToSSL'), 5);
        add_action('login_init', array($this, 'redirectToSSL'), 5);
        add_action('rest_api_init', array($this, 'redirectToSSL'), 5);

        //Sanitazion
        add_filter('the_permalink', array($this, 'makeUrlHttps'), 700);
        add_filter('wp_get_attachment_url', array($this, 'makeUrlHttps'), 700);
        add_filter('wp_get_attachment_image_src', array($this, 'makeUrlHttps'), 700);
        add_filter('script_loader_src', array($this, 'makeUrlHttps'), 700);
        add_filter('style_loader_src', array($this, 'makeUrlHttps'), 700);
        add_filter('the_content', array($this, 'replaceInlineUrls'), 700);
        add_filter('widget_text', array($this, 'replaceInlineUrls'), 700);

        //Fix site url / home url
        add_filter('option_siteurl', array($this, 'makeUrlHttps'), 700);
        add_filter('option_home', array($this, 'makeUrlHttps'), 700);

    }

    public function forceSSL()
    {
        if (!$this->isUsingSSLProxy()) {
            if (!defined('FORCE_SSL_ADMIN')) {
                define('FORCE_SSL_ADMIN', true);
            }
            if (!defined('FORCE_SSL_LOGIN')) {
                define('FORCE_SSL_LOGIN', true);
            }
        } else {
            if (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false)) {
                $_SERVER['HTTPS']='on';
            }
        }
    }

    public function redirectToSSL()
    {
        if (!is_ssl() && !$this->isUsingSSLProxy()) {
            wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
            exit();
        }
    }

    public function makeUrlHttps($url)
    {
        return apply_filters('force_ssl_make_url_https', preg_replace('(^https?://)', 'https://', $url));
    }

    public function replaceInlineUrls($content)
    {
        return str_replace(home_url('/', 'http'), $this->makeUrlHttps(home_url('/', 'https')), $content);
    }

    public function isUsingSSLProxy()
    {
        if ((defined('SSL_PROXY') && SSL_PROXY === true)) {
            return true;
        }
        return false;
    }
}
