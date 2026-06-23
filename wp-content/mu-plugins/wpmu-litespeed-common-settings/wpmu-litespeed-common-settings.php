<?php

/*
Plugin Name:    WPMU Litespeed Common Settings
Description:    Adds input field for a google maps api key.
Version:        1.0
Author:         Sebastian Thulin
*/

namespace WPMULitespeedCommonSettings;

class WPMULitespeedCommonSettings
{
  /**
   * The prefix for option keys.
   * @var string
   */
  private $optionPrefix = 'litespeed.conf';

  /**
   * The WordPress database object.
   * @var \wpdb
   */
  private $db;

  /**
   * Initializes the database and registers action hooks.
   * @return void
   */
  public function __construct()
  {
    add_action('admin_init', [$this, 'removeOptionsPage']);
    add_action('admin_init', function(){
      if($this->shouldPropagateSettings()) {
        $this->initDB();
        $this->propagateOptions(); 
      }
    }, PHP_INT_MAX); 
  }

  /**
   * Removes the LiteSpeed Options page from the WordPress admin menu.
   * This function is only executed on subsites, not the main site.
   */
  public function removeOptionsPage() {

    global $menu;

    // Check if it's the main site
    if (is_main_site() || empty($menu)) {
      return;
    }

    //Hide "Settings → LiteSpeed Cache".
    remove_submenu_page('options-general.php', 'litespeed-cache-options');

    //Hide "LiteSpeed Cache".
    remove_menu_page('litespeed');
    //Hide "LiteSpeed Cache → Dashboard".
    remove_submenu_page('litespeed', 'litespeed');
    //Hide "LiteSpeed Cache → Presets".
    remove_submenu_page('litespeed', 'litespeed-presets');
    //Hide "LiteSpeed Cache → General".
    remove_submenu_page('litespeed', 'litespeed-general');
    //Hide "LiteSpeed Cache → Cache".
    remove_submenu_page('litespeed', 'litespeed-cache');
    //Hide "LiteSpeed Cache → CDN".
    remove_submenu_page('litespeed', 'litespeed-cdn');
    //Hide "LiteSpeed Cache → Image Optimization".
    remove_submenu_page('litespeed', 'litespeed-img_optm');
    //Hide "LiteSpeed Cache → Page Optimization".
    remove_submenu_page('litespeed', 'litespeed-page_optm');
    //Hide "LiteSpeed Cache → Database".
    remove_submenu_page('litespeed', 'litespeed-db_optm');
    //Hide "LiteSpeed Cache → Crawler".
    remove_submenu_page('litespeed', 'litespeed-crawler');
    //Hide "LiteSpeed Cache → Toolbox".
    remove_submenu_page('litespeed', 'litespeed-toolbox');
  }

  /**
   * Initializes the WordPress database object.
   * @return void
   */
  public function initDB()
  {
    $this->globalToLocal('wpdb', 'db');
  }

  /**
   * Determine if action should trigger propagation.
   */
  private function shouldPropagateSettings():bool {
    if(isset($_GET['page']) && $_GET['page'] == "litespeed-cache") {
      if(isset($_POST['litespeed-submit']) && !empty($_POST['litespeed-submit'])) {
        return true;
      }
    }
    return false;
  }

  /**
   * Propagates options to different sites.
   * @return void
   */
  public function propagateOptions() {
    $options  = $this->getLiteSpeedOptionKeys();
    $sites    = $this->getSites();

    if(is_array($sites) && !empty($sites)) {
      
      foreach($sites as $key => $site) {
        if(!$key) {
          continue;
        }
        switch_to_blog($site->blog_id);
          if(is_array($options) && !empty($options)) {
            foreach($options as $option) {
              update_option($option->option_name, $option->option_value);
            }
          }
        restore_current_blog(); 
      }
    }
  } 

  /**
   * Retrieves a list of sites.
   * @return array An array of site objects.
   */
  private function getSites() {
    return get_sites(
      [
        'number' => PHP_INT_MAX
      ]
    );
  }

  /**
   * Retrieves LiteSpeed option keys from the database.
   * @return array
   */
  private function getLiteSpeedOptionKeys(): array
  {
    $optionsTable = $this->db->get_blog_prefix(BLOG_ID_CURRENT_SITE) . "options";

    $metaKeys = (array) $this->db->get_results(
      $x = str_replace(
        "[LKR]",
        "%",
        $this->db->prepare(
          "SELECT option_name, option_value FROM " . $optionsTable . " WHERE option_name LIKE %s LIMIT 300",
          [
            $this->db->esc_like($this->optionPrefix) . "[LKR]"
          ]
        )
      )
    );

    return $metaKeys;
  }

  /**
   * Creates a local copy of the global instance.
   * The target variable should be defined in the class header as private or public.
   * @param string $global The name of the global variable that should be made local.
   * @param string|null $local Handle the global with the name of this string locally.
   * @return bool Returns true if the global variable was successfully copied to a local variable, false otherwise.
   */
  private function globalToLocal($global, $local = null)
  {
    global $$global;

    if (is_null($$global)) {
      return false;
    }

    if (is_null($local)) {
      $this->$global = $$global;
    } else {
      $this->$local = $$global;
    }

    return true;
  }
}

new \WPMULitespeedCommonSettings\WPMULitespeedCommonSettings();