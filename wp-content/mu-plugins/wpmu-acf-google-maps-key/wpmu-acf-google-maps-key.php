<?php

/*
Plugin Name:    WPMU Google Maps Key
Description:    Adds input field for a google maps api key.
Version:        1.0
Author:         Sebastian Thulin
*/

namespace WPMUAcfGoogleMapsKey;

class WPMUAcfGoogleMapsKey
{

  private $fieldOptionName = 'acf_google_api_key';

  public function __construct()
  {
      add_action('init', [$this, 'addOptionsPage']);
      add_action('init', [$this, 'addOptionsField']);
      add_filter('acf/fields/google_map/api', [$this, 'filterOption'], 10, 1);
  }

  public function addOptionsField() {
    if( function_exists('acf_add_local_field_group') ):
      acf_add_local_field_group(array(
        'key' => 'group_643d4ad89569a',
        'title' => 'ACF Google Maps Settings',
        'fields' => array(
          array(
            'key' => 'field_643d4ad829c99',
            'label' => 'Google Maps API Key',
            'name' => $this->fieldOptionName,
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '',
              'class' => '',
              'id' => '',
            ),
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
          ),
        ),
        'location' => array(
          array(
            array(
              'param' => 'options_page',
              'operator' => '==',
              'value' => 'acf-google-maps-key',
            ),
          ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'left',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
        'acfe_display_title' => '',
        'acfe_autosync' => '',
        'acfe_form' => 0,
        'acfe_meta' => '',
        'acfe_note' => '',
      ));
    endif;		
  }

  public function addOptionsPage($data)
  {
    if(function_exists('acf_add_options_page')) {
      acf_add_options_page(array(
        'page_title'    => 'ACF Google Maps Key',
        'menu_title'    => 'ACF Google Maps Key',
        'menu_slug'     => 'acf-google-maps-key',
        'capability'    => 'edit_posts',
        'redirect'      => false,
        'parent'        => 'options-general.php'
      ));
    }
  }

  public function filterOption($api) {
    if ($apiKeySetting = get_field($this->fieldOptionName, 'option')) {
      $api['key'] = $apiKeySetting;
    }
    return $api;
  }
}

new \WPMUAcfGoogleMapsKey\WPMUAcfGoogleMapsKey();
