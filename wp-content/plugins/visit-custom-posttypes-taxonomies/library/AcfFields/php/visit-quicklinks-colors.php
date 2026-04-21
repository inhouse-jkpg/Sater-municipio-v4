<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_641d735c44589',
    'title' => __('Menu item color', 'visit'),
    'fields' => array(
        0 => array(
            'key' => 'field_641d735ce2f28',
            'label' => __('Color', 'visit'),
            'name' => 'menu_item_color',
            'aria-label' => '',
            'type' => 'color_picker',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'enable_opacity' => 1,
            'return_format' => 'string',
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'nav_menu_item',
                'operator' => '==',
                'value' => 'location/quicklinks-menu',
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
}