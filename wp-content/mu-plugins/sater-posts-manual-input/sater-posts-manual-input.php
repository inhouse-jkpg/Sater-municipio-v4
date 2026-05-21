<?php
/**
 * Plugin Name: Säter Modularity Posts: Manual input data source
 * Description: Restores Posts "Manual input" ACF fields removed from upstream Modularity. Survives Composer deploy.
 * Version: 2.3.0
 * Author: Municipio SE
 * License: MIT
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SATER_POSTS_SOURCE_GROUP_KEY = 'group_571dfaabc3fc5';
const SATER_POSTS_SOURCE_FIELD_KEY = 'field_571dfaafe6984';
const SATER_POSTS_DATA_REPEATER_KEY = 'field_576258d3110b0';
const SATER_POSTS_DISPLAY_AS_FIELD_KEY = 'field_571dfd4c0d9d9';
const SATER_POSTS_DISPLAY_AS_DEFAULT = 'expandable-list';

/** image_select keys => template slug (mod-posts-displau.php) */
const SATER_POSTS_DISPLAY_AS_IMAGE_SELECT_MAP = [
    '6762ed43da0e4' => 'expandable-list',
    '6762eee7da0e7' => 'grid',
    '6762eeeeda0e8' => 'features-grid',
    '6762eef4da0e9' => 'index',
    '6762eefeda0ea' => 'collection',
    '6762ef12da0eb' => 'list',
    '6762ef6ada0ec' => 'news',
    '6762ef74da0ed' => 'segment',
];

const SATER_POSTS_DISPLAY_AS_VALID_SLUGS = [
    'expandable-list',
    'grid',
    'features-grid',
    'index',
    'collection',
    'list',
    'news',
    'segment',
    'slider',
    'items',
];

/**
 * Register production "Data source" field group (includes Manual input).
 */
add_action(
    'acf/init',
    static function (): void {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        if (function_exists('acf_remove_local_field_group')) {
            acf_remove_local_field_group(SATER_POSTS_SOURCE_GROUP_KEY);
        }

        require_once __DIR__ . '/mod-posts-source.php';
    },
    20
);

/**
 * Patch DB-synced field group when ACF loads it from the database.
 *
 * @param array<string, mixed>|false $fieldGroup
 * @return array<string, mixed>|false
 */
function sater_posts_manual_input_patch_field_group($fieldGroup)
{
    if (!is_array($fieldGroup) || ($fieldGroup['key'] ?? '') !== SATER_POSTS_SOURCE_GROUP_KEY) {
        return $fieldGroup;
    }

    if (empty($fieldGroup['fields']) || !is_array($fieldGroup['fields'])) {
        return $fieldGroup;
    }

    $hasRepeater = false;

    foreach ($fieldGroup['fields'] as $index => $field) {
        if (!is_array($field)) {
            continue;
        }

        if (($field['key'] ?? '') === SATER_POSTS_SOURCE_FIELD_KEY) {
            if (!isset($field['choices']) || !is_array($field['choices'])) {
                $field['choices'] = [];
            }
            if (!isset($field['choices']['input'])) {
                $field['choices']['input'] = __('Manual input', 'modularity');
            }
            $fieldGroup['fields'][$index] = $field;
            continue;
        }

        if (($field['key'] ?? '') === SATER_POSTS_DATA_REPEATER_KEY) {
            $hasRepeater = true;
        }
    }

    if (!$hasRepeater && function_exists('acf_get_field')) {
        $repeater = acf_get_field(SATER_POSTS_DATA_REPEATER_KEY);
        if (is_array($repeater)) {
            $fieldGroup['fields'][] = $repeater;
        }
    }

    return $fieldGroup;
}

add_filter('acf/load_field_group', 'sater_posts_manual_input_patch_field_group', 99, 1);

/**
 * Resolve posts_display_as to a real Blade slug.
 *
 * @param mixed $value   Raw meta / ACF value.
 * @param int   $postId  mod-posts post ID.
 * @return string
 */
function sater_posts_manual_input_resolve_display_as($value, int $postId = 0): string
{
    if (is_string($value)) {
        $value = trim($value);
    }

    if (is_string($value) && $value !== '') {
        if (isset(SATER_POSTS_DISPLAY_AS_IMAGE_SELECT_MAP[$value])) {
            return SATER_POSTS_DISPLAY_AS_IMAGE_SELECT_MAP[$value];
        }
        if (in_array($value, SATER_POSTS_DISPLAY_AS_VALID_SLUGS, true)) {
            return $value;
        }
    }

    if ($postId > 0) {
        $conditional = get_post_meta($postId, 'posts_display_as_conditional', true);
        if (is_string($conditional) && trim($conditional) !== '') {
            $resolved = sater_posts_manual_input_resolve_display_as($conditional, 0);
            if ($resolved !== SATER_POSTS_DISPLAY_AS_DEFAULT || trim($conditional) !== '') {
                return $resolved;
            }
        }
    }

    return SATER_POSTS_DISPLAY_AS_DEFAULT;
}

/**
 * @param mixed $value
 * @param mixed $postId
 * @param mixed $field
 * @return mixed
 */
function sater_posts_manual_input_filter_display_as_value($value, $postId = 0, $field = null)
{
    $id = is_numeric($postId) ? (int) $postId : 0;

    // Manual input uses fake WP_Post rows (ID 0). Skipping getTemplateData() in Posts::template()
    // avoids Municipio preparePostObject(); accordion is built in viewData from the "data" repeater.
    if ($id > 0 && function_exists('get_field') && get_field('posts_data_source', $id) === 'input') {
        return '';
    }

    return sater_posts_manual_input_resolve_display_as($value, $id);
}

add_filter('acf/load_value/name=posts_display_as', 'sater_posts_manual_input_filter_display_as_value', 99, 3);
add_filter('acf/load_value/key=' . SATER_POSTS_DISPLAY_AS_FIELD_KEY, 'sater_posts_manual_input_filter_display_as_value', 99, 3);

/**
 * Build accordion rows from the Manual input repeater (ACF field "data").
 *
 * @param int $moduleId mod-posts post ID.
 * @return array<int, array<string, mixed>>
 */
function sater_posts_manual_input_build_accordion_from_repeater(int $moduleId): array
{
    if ($moduleId < 1 || !function_exists('get_field')) {
        return [];
    }

    $rows = get_field('data', $moduleId);
    if (!is_array($rows)) {
        return [];
    }

    $accordion = [];

    foreach ($rows as $index => $row) {
        if (!is_array($row)) {
            continue;
        }

        $title = trim((string) ($row['post_title'] ?? ''));
        if ($title === '') {
            continue;
        }

        $content = (string) ($row['post_content'] ?? '');
        $item = [
            'heading' => $title,
            'content' => $content !== '' ? apply_filters('the_content', $content) : '',
            'classList' => [],
            'attributeList' => ['data-js-item-id' => 'manual-' . $moduleId . '-' . $index],
        ];

        if (!empty($row['column_values']) && is_array($row['column_values'])) {
            $item['column_values'] = [];
            foreach ($row['column_values'] as $column) {
                $item['column_values'][] = is_array($column) ? (string) ($column['value'] ?? '') : '';
            }
        }

        $accordion[] = $item;
    }

    return $accordion;
}

/**
 * Fix template filename and sync display slug on the module instance.
 *
 * Do not call getTemplateData() here. A second run breaks manual-input posts because
 * preparePostObject expects WP_Post, not PostObjectInterface.
 *
 * @param mixed $template
 * @param mixed $module
 * @return mixed
 */
function sater_posts_manual_input_filter_posts_template($template, $module = null)
{
    $postId = 0;
    if (is_object($module) && isset($module->ID)) {
        $postId = (int) $module->ID;
    }

    $raw = '';
    if (is_object($module)) {
        $raw = $module->data['posts_display_as'] ?? $module->fields['posts_display_as'] ?? '';
    }

    if (is_string($template)) {
        $stripped = preg_replace('/\.(blade\.php|php)$/', '', $template) ?? $template;
        if ($stripped !== '' && $stripped !== '.blade' && !str_contains($stripped, '/')) {
            $raw = $raw ?: $stripped;
        }
    }

    $slug = sater_posts_manual_input_resolve_display_as($raw, $postId);

    if (is_object($module)) {
        $module->data['posts_display_as'] = $slug;
        if (is_array($module->fields ?? null)) {
            $module->fields['posts_display_as'] = $slug;
        }
    }

    return $slug . '.blade.php';
}

add_filter('Modularity/Module/Posts/template', 'sater_posts_manual_input_filter_posts_template', 1, 2);

/**
 * Supply accordion data for Manual input modules and guard against null.
 *
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_posts_manual_input_filter_view_data(array $data): array
{
    $moduleId = (int) ($data['ID'] ?? 0);

    if (($data['posts_data_source'] ?? '') === 'input' && $moduleId > 0) {
        $data['prepareAccordion'] = sater_posts_manual_input_build_accordion_from_repeater($moduleId);

        // expandable-list shows "Sök" when this key is missing; we skip getTemplateData() for manual input.
        $allowSearch = function_exists('get_field') ? get_field('allow_freetext_filtering', $moduleId) : false;
        $data['allow_freetext_filtering'] = !empty($allowSearch);
    }

    if (!isset($data['prepareAccordion']) || !is_array($data['prepareAccordion'])) {
        $data['prepareAccordion'] = [];
    }

    return $data;
}

add_filter('Modularity/Display/mod-posts/viewData', 'sater_posts_manual_input_filter_view_data', 99, 1);

/**
 * Ensure posts_display_as is resolved before Posts::data() reads fields.
 *
 * @param mixed $value
 * @param mixed $postId
 * @param mixed $field
 * @return mixed
 */
function sater_posts_manual_input_filter_posts_fields_on_load($value, $postId = 0, $field = null)
{
    if (!is_array($field)) {
        return $value;
    }

    if (($field['name'] ?? '') === 'posts_display_as') {
        return sater_posts_manual_input_filter_display_as_value($value, $postId, $field);
    }

    return $value;
}

add_filter('acf/load_value', 'sater_posts_manual_input_filter_posts_fields_on_load', 5, 3);

add_filter(
    'Municipio/Admin/Acf/PrefillIconChoice',
    static function (array $fieldNames): array {
        $fieldNames[] = 'item_icon';

        return $fieldNames;
    }
);
