<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_58eb4fce51bb7',
    'title' => __('Easy reading', 'easy-reading'),
    'fields' => array(
        0 => array(
            'key' => 'field_58eb4fe58de9d',
            'label' => __('Easy to read content', 'easy-reading'),
            'name' => 'easy_reading_select',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => __('Check this box to add easy to read content version.', 'easy-reading'),
            'default_value' => 0,
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
        1 => array(
            'key' => 'field_58eb4fed8de9e',
            'label' => __('Alternate content', 'easy-reading'),
            'name' => 'easy_reading_content',
            'type' => 'wysiwyg',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_58eb4fe58de9d',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'tabs' => 'all',
            'toolbar' => 'full',
            'media_upload' => 1,
            'delay' => 0,
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'settings',
                'operator' => '==',
                'value' => '0',
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