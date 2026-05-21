<?php
/**
 * ACF field definitions for Posts module Manual input (data repeater).
 *
 * Field keys match Modularity <= 6.16.x so existing module meta keeps working.
 *
 * @package Sater
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Patch the Modularity Posts "Data source" field group with Manual input support.
 *
 * @param array<string, mixed> $fieldGroup ACF field group definition.
 * @return array<string, mixed>
 */
function sater_posts_manual_input_patch_posts_source_field_group(array $fieldGroup): array
{
    if (($fieldGroup['key'] ?? '') !== SATER_POSTS_DATA_SOURCE_GROUP_KEY) {
        return $fieldGroup;
    }

    if (empty($fieldGroup['fields']) || !is_array($fieldGroup['fields'])) {
        return $fieldGroup;
    }

    $fieldGroup['fields'] = sater_posts_manual_input_patch_posts_source_fields($fieldGroup['fields']);

    return $fieldGroup;
}

/**
 * Add Manual input choice and Data input repeater to the field list when missing.
 *
 * @param array<int, array<string, mixed>> $fields Field definitions.
 * @return array<int, array<string, mixed>>
 */
function sater_posts_manual_input_patch_posts_source_fields(array $fields): array
{
    $hasDataRepeater = false;

    foreach ($fields as $index => $field) {
        if (!is_array($field)) {
            continue;
        }

        if (($field['key'] ?? '') === SATER_POSTS_DATA_SOURCE_FIELD_KEY) {
            $fields[$index] = sater_posts_manual_input_patch_data_source_field($field);
            continue;
        }

        if (($field['key'] ?? '') === SATER_POSTS_DATA_INPUT_REPEATER_KEY) {
            $hasDataRepeater = true;
        }
    }

    if (!$hasDataRepeater) {
        $fields[] = sater_posts_manual_input_get_data_repeater_field_for_group();
    }

    return $fields;
}

/**
 * Ensure the data source select includes Manual input.
 *
 * @param array<string, mixed> $field ACF field definition.
 * @return array<string, mixed>
 */
function sater_posts_manual_input_patch_data_source_field(array $field): array
{
    if (($field['name'] ?? '') !== 'posts_data_source') {
        return $field;
    }

    if (!isset($field['choices']) || !is_array($field['choices'])) {
        $field['choices'] = [];
    }

    if (!isset($field['choices']['input'])) {
        $field['choices']['input'] = __('Manual input', 'modularity');
    }

    return $field;
}

/**
 * Whether the Data input repeater is already registered.
 */
function sater_posts_manual_input_has_data_repeater_field(): bool
{
    if (!function_exists('acf_get_field')) {
        return false;
    }

    return (bool) acf_get_field(SATER_POSTS_DATA_INPUT_REPEATER_KEY);
}

/**
 * Register the "Data input" repeater as a standalone local field (fallback).
 */
function sater_posts_manual_input_register_data_repeater_field(): void
{
    if (!function_exists('acf_add_local_field')) {
        return;
    }

    $field = sater_posts_manual_input_get_data_repeater_field_for_group();
    $field['parent'] = SATER_POSTS_DATA_SOURCE_GROUP_KEY;
    $field['menu_order'] = 50;

    acf_add_local_field($field);
}

/**
 * Repeater field for use inside the field group fields array.
 *
 * @return array<string, mixed>
 */
function sater_posts_manual_input_get_data_repeater_field_for_group(): array
{
    $repeaterKey = SATER_POSTS_DATA_INPUT_REPEATER_KEY;

    return [
        'key' => $repeaterKey,
        'label' => __('Data input', 'modularity'),
        'name' => 'data',
        'type' => 'repeater',
        'instructions' => '',
        'required' => 1,
        'conditional_logic' => [
            [
                [
                    'field' => SATER_POSTS_DATA_SOURCE_FIELD_KEY,
                    'operator' => '==',
                    'value' => 'input',
                ],
            ],
        ],
        'wrapper' => [
            'width' => '',
            'class' => '',
            'id' => '',
        ],
        'aria-label' => '',
        'min' => 1,
        'max' => 0,
        'layout' => 'block',
        'button_label' => __('Add', 'modularity'),
        'collapsed' => '',
        'rows_per_page' => 20,
        'acfe_repeater_stylised_button' => 0,
        'sub_fields' => [
            [
                'key' => 'field_576258f4110b1',
                'label' => __('Titel', 'modularity'),
                'name' => 'post_title',
                'type' => 'text',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'aria-label' => '',
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'readonly' => 0,
                'disabled' => 0,
                'parent_repeater' => $repeaterKey,
            ],
            [
                'key' => 'field_57625914110b2',
                'label' => __('Innehåll', 'modularity'),
                'name' => 'post_content',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'aria-label' => '',
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 0,
                'parent_repeater' => $repeaterKey,
            ],
            [
                'key' => 'field_576261c3ef10e',
                'label' => __('Permalink', 'modularity'),
                'name' => 'permalink',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'aria-label' => '',
                'default_value' => '',
                'placeholder' => '',
                'parent_repeater' => $repeaterKey,
            ],
            [
                'key' => 'field_57625930110b3',
                'label' => __('Bild', 'modularity'),
                'name' => 'image',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'uploader' => '',
                'acfe_thumbnail' => 0,
                'return_format' => 'id',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'parent_repeater' => $repeaterKey,
            ],
            [
                'key' => 'field_62a309f9c59bb',
                'label' => __('Icon', 'modularity'),
                'name' => 'item_icon',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'choices' => [],
                'default_value' => false,
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'ajax' => 0,
                'return_format' => 'value',
                'allow_custom' => 0,
                'placeholder' => '',
                'search_placeholder' => '',
                'parent_repeater' => $repeaterKey,
            ],
            [
                'key' => 'field_57625a3e188da',
                'label' => __('Column values', 'modularity'),
                'name' => 'column_values',
                'type' => 'repeater',
                'instructions' => __(
                    'Column values if expandable list is selected.',
                    'modularity'
                ),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'aria-label' => '',
                'min' => 0,
                'max' => 0,
                'layout' => 'table',
                'button_label' => __('Add', 'modularity'),
                'collapsed' => '',
                'rows_per_page' => 20,
                'acfe_repeater_stylised_button' => 0,
                'parent_repeater' => $repeaterKey,
                'sub_fields' => [
                    [
                        'key' => 'field_57625a67188db',
                        'label' => __('Value', 'modularity'),
                        'name' => 'value',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'aria-label' => '',
                        'default_value' => '',
                        'maxlength' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'readonly' => 0,
                        'disabled' => 0,
                        'parent_repeater' => 'field_57625a3e188da',
                    ],
                ],
            ],
        ],
    ];
}
