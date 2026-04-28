<?php

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_63dbb0ca3dab5',
    'title' => __('Cuisine', 'visit'),
    'fields' => array(
        0 => array(
            'key' => 'field_63dbb0ca18ed4',
            'label' => __('Cuisine', 'visit'),
            'name' => 'cuisine',
            'aria-label' => '',
            'type' => 'taxonomy',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'taxonomy' => 'cuisine',
            'add_term' => 1,
            'save_terms' => 1,
            'load_terms' => 1,
            'return_format' => 'id',
            'field_type' => 'multi_select',
            'allow_null' => 1,
            'acfe_bidirectional' => array(
                'acfe_bidirectional_enabled' => '0',
            ),
            'multiple' => 0,
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_taxonomy',
                'operator' => '==',
                'value' => 'activity:ata-dricka',
            ),
        ),
        1 => array(
            0 => array(
                'param' => 'post_taxonomy',
                'operator' => '==',
                'value' => 'activity:mat-dryck',
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
    'acfe_note' => 'Display on places categorised under "Food & Drink"',
    ));
}
