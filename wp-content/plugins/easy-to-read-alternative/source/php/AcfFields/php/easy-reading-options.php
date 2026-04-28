<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_58eb9450b0a9f',
    'title' => __('Easy reading settings', 'easy-reading'),
    'fields' => array(
        0 => array(
            'key' => 'field_58eb9486a647a',
            'label' => __('Post types', 'easy-reading'),
            'name' => 'easy_reading_posttypes',
            'type' => 'posttype_select',
            'instructions' => __('Show easy reading field on selected post types.', 'easy-reading'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '30',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'allow_null' => 0,
            'multiple' => 1,
            'placeholder' => '',
            'disabled' => 0,
            'readonly' => 0,
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'options_page',
                'operator' => '==',
                'value' => 'easy-reading-options',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));
}