<?php

/*
Plugin Name:    Plugins URL Everywhere
Description:    Allow Plugins URL function to create urls towards any directory
Version:        1.0
Author:         Sebastian Thulin
*/

namespace WPMUPluginsURLEverywhere;

/**
 * Class WPMUPluginsURLEverywhere
 *
 * Main class for the Plugins URL Everywhere plugin.
 */
class WPMUPluginsURLEverywhere
{

  /**
   * Constructor for the WPMUPluginsURLEverywhere class.
   * Registers the 'init' action hook to initialize the filter.
   */
  public function __construct()
  {
    add_action('init', [$this, 'initFilter'], 2);
  }

  /**
   * Initialize the filter by registering the 'plugins_url' filter hook.
   */
  public function initFilter() {
    add_filter('plugins_url', [$this, 'filterPluginsUrl'], 10, 3);
  }

  /**
   * Filter the plugins URL to remove the document root and specified prefixes.
   *
   * @param string $url    The URL to be filtered.
   * @param string $path   The path to the requested resource.
   * @param string $plugin The plugin file path.
   *
   * @return string The filtered URL.
   */
  public function filterPluginsUrl($url, $path, $plugin) {
    $documentRoot = $this->getDocumentRoot();

    if($documentRoot) {
      $url = $this->removePath($url, $documentRoot);
    }
    return $url;
  }

  /**
   * Remove specified prefixes from the URL.
   *
   * @param string $url         The URL to be modified.
   * @param string $documentRoot The document root path.
   * @param array  $prefixes     An array of prefixes to remove.
   *
   * @return string The modified URL.
   */
  private function removePath($url, $documentRoot, $prefixes = ['/wp-content/plugins']) {
    foreach($prefixes as $prefix) {
      $url = str_replace($prefix . $documentRoot, "", $url); 
    }
    return $url;
  }

  /**
   * Get the document root path from the server environment.
   *
   * @return string|false The document root path or false if not available.
   */
  private function getDocumentRoot () {
    if(isset($_SERVER['DOCUMENT_ROOT'])) {
      return $_SERVER['DOCUMENT_ROOT']; 
    }
    return false;
  }
}

new \WPMUPluginsURLEverywhere\WPMUPluginsURLEverywhere();
