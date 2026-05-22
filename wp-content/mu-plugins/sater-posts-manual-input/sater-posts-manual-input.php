<?php
/**
 * Plugin Name: Säter Modularity Posts: Manual input data source
 * Description: Restores Posts "Manual input" ACF fields removed from upstream Modularity. Survives Composer deploy.
 * Version: 2.5.0
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
 * Admin only: register field group and patch DB-synced group.
 * Modularity already imports mod-posts-source on plugins_loaded; duplicating that on
 * every frontend request was the main performance hit.
 */
if (is_admin()) {
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

    add_filter('acf/load_field_group', 'sater_posts_manual_input_patch_field_group', 99, 1);

    add_filter(
        'Municipio/Admin/Acf/PrefillIconChoice',
        static function (array $fieldNames): array {
            $fieldNames[] = 'item_icon';

            return $fieldNames;
        }
    );
}

/**
 * Frontend + admin: skip ExpandableListTemplate (the_content) for manual input modules.
 */
add_filter('acf/load_value/name=posts_display_as', 'sater_posts_manual_input_filter_display_as_value', 99, 3);

add_filter('Modularity/Module/Posts/template', 'sater_posts_manual_input_filter_posts_template', 0, 2);
add_filter('Modularity/Module/Posts/template', 'sater_posts_manual_input_guard_posts_template', 999, 2);
add_filter('Modularity/Display/mod-posts/viewData', 'sater_posts_manual_input_filter_view_data', 99, 1);

/**
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
        static $repeaterField = null;

        if ($repeaterField === null) {
            $repeaterField = acf_get_field(SATER_POSTS_DATA_REPEATER_KEY) ?: false;
        }

        if (is_array($repeaterField)) {
            $fieldGroup['fields'][] = $repeaterField;
        }
    }

    return $fieldGroup;
}

/**
 * @param int $moduleId
 */
function sater_posts_manual_input_get_data_source(int $moduleId): string
{
    static $cache = [];

    if ($moduleId < 1) {
        return '';
    }

    if (!array_key_exists($moduleId, $cache)) {
        $cache[$moduleId] = (string) get_post_meta($moduleId, 'posts_data_source', true);
    }

    return $cache[$moduleId];
}

/**
 * @param int $postId
 */
function sater_posts_manual_input_is_mod_posts(int $postId): bool
{
    return $postId > 0 && get_post_type($postId) === 'mod-posts';
}

/**
 * @param mixed $value
 * @param int   $postId
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
 * @return mixed
 */
function sater_posts_manual_input_filter_display_as_value($value, $postId = 0, $field = null)
{
    $id = is_numeric($postId) ? (int) $postId : 0;

    if (!sater_posts_manual_input_is_mod_posts($id)) {
        return $value;
    }

    if (sater_posts_manual_input_get_data_source($id) === 'input') {
        return '';
    }

    return sater_posts_manual_input_resolve_display_as($value, $id);
}

/**
 * @param object|null $module
 */
function sater_posts_manual_input_module_uses_manual_input($module): bool
{
    if (!is_object($module) || empty($module->ID)) {
        return false;
    }

    if (($module->data['posts_data_source'] ?? null) === 'input') {
        return true;
    }

    return sater_posts_manual_input_get_data_source((int) $module->ID) === 'input';
}

/**
 * Build accordion once per module per request. No get_field(), transients, or wp_kses_post.
 *
 * @param array<int|string, mixed> $rows
 * @return array<int, array<string, mixed>>
 */
function sater_posts_manual_input_build_accordion(array $rows, int $moduleId): array
{
    static $built = [];

    if (isset($built[$moduleId])) {
        return $built[$moduleId];
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
        if ($content !== '') {
            $content = apply_filters(
                'sater_posts_manual_input_accordion_item_content',
                $content,
                $row,
                $moduleId,
                (int) $index
            );
        }

        $item = [
            'heading' => $title,
            'content' => $content,
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

    $built[$moduleId] = $accordion;

    return $accordion;
}

/**
 * @param object|null $module
 */
function sater_posts_manual_input_prepare_manual_module($module): void
{
    if (!sater_posts_manual_input_module_uses_manual_input($module)) {
        return;
    }

    $moduleId = (int) $module->ID;
    $fields = is_array($module->fields ?? null) ? $module->fields : [];

    if (!empty($fields['data']) && is_array($fields['data'])) {
        $module->data['prepareAccordion'] = sater_posts_manual_input_build_accordion($fields['data'], $moduleId);
    } elseif (!isset($module->data['prepareAccordion'])) {
        $module->data['prepareAccordion'] = [];
    }

    $module->data['posts'] = [];
    $module->data['allow_freetext_filtering'] = !empty($fields['allow_freetext_filtering']);
}

/**
 * @param mixed $template
 * @param mixed $module
 * @return mixed
 */
function sater_posts_manual_input_filter_posts_template($template, $module = null)
{
    if (!sater_posts_manual_input_module_uses_manual_input($module)) {
        return $template;
    }

    sater_posts_manual_input_prepare_manual_module($module);

    $postId = is_object($module) && isset($module->ID) ? (int) $module->ID : 0;
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
    if ($slug === '') {
        $slug = SATER_POSTS_DISPLAY_AS_DEFAULT;
    }

    if (is_object($module)) {
        $module->data['posts_display_as'] = $slug;
        if (is_array($module->fields ?? null)) {
            $module->fields['posts_display_as'] = $slug;
        }
    }

    return $slug . '.blade.php';
}

/**
 * @param mixed $template
 * @param mixed $module
 * @return mixed
 */
function sater_posts_manual_input_guard_posts_template($template, $module = null)
{
    if (!sater_posts_manual_input_module_uses_manual_input($module)) {
        return $template;
    }

    if (!is_string($template)) {
        return SATER_POSTS_DISPLAY_AS_DEFAULT . '.blade.php';
    }

    $stripped = preg_replace('/\.(blade\.php|php)$/', '', $template) ?? $template;
    if ($stripped === '' || $stripped === '.blade' || $template === '.blade.php') {
        return SATER_POSTS_DISPLAY_AS_DEFAULT . '.blade.php';
    }

    return $template;
}

/**
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_posts_manual_input_filter_view_data(array $data): array
{
    $moduleId = (int) ($data['ID'] ?? 0);

    if (($data['posts_data_source'] ?? '') === 'input' && $moduleId > 0) {
        if (!isset($data['prepareAccordion']) || !is_array($data['prepareAccordion'])) {
            $fields = is_array($data['fields'] ?? null) ? $data['fields'] : [];
            $rows = is_array($fields['data'] ?? null) ? $fields['data'] : [];
            $data['prepareAccordion'] = sater_posts_manual_input_build_accordion($rows, $moduleId);
        }

        $data['posts'] = [];
    }

    if (!isset($data['prepareAccordion']) || !is_array($data['prepareAccordion'])) {
        $data['prepareAccordion'] = [];
    }

    return $data;
}
