<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_57db7edf10454',
    'title' => __('Announcement', 'modularity-noticeboard'),
    'fields' => array(
        0 => array(
            'key' => 'field_57db7f09b2a53',
            'label' => __('Link', 'modularity-noticeboard'),
            'name' => 'link',
            'aria-label' => '',
            'type' => 'url',
            'instructions' => __('Link to a file', 'modularity-noticeboard'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
        ),
        1 => array(
            'key' => 'field_57db7f7cb2a54',
            'label' => __('Meeting date', 'modularity-noticeboard'),
            'name' => 'meeting_date',
            'aria-label' => '',
            'type' => 'date_picker',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'display_format' => 'd/m/Y',
            'return_format' => 'd/m/Y',
            'first_day' => 1,
        ),
        2 => array(
            'key' => 'field_58bfb86c760e3',
            'label' => __('Announcement type', 'modularity-noticeboard'),
            'name' => 'announcement_type',
            'aria-label' => '',
            'type' => 'taxonomy',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'taxonomy' => 'announcement_type',
            'add_term' => 1,
            'save_terms' => 1,
            'load_terms' => 1,
            'return_format' => 'id',
            'field_type' => 'radio',
            'allow_null' => 0,
            'acfe_bidirectional' => array(
                'acfe_bidirectional_enabled' => '0',
            ),
            'bidirectional' => 0,
            'multiple' => 0,
            'bidirectional_target' => array(
            ),
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'announcement',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
    'show_in_rest' => 0,
    'acfe_display_title' => '',
    'acfe_autosync' => array(
        0 => 'json',
    ),
    'acfe_form' => 0,
    'acfe_meta' => '',
    'acfe_note' => '',
));
}