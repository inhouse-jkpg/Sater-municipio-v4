<?php
/**
 * Plugin Name: Säter Modularity Posts: Manual input data source
 * Description: Restores the "Manual input" Posts module data source removed from upstream Modularity 6.17+.
 * Version: 1.0.2
 * Author: Municipio SE
 * License: MIT
 * Requires PHP: 8.0
 *
 * @category   WordPress
 * @package    Sater
 * @author     Municipio SE <dev@municipio.se>
 * @license    MIT https://opensource.org/licenses/MIT
 * @link       https://sater.se
 * @since      1.0.0
 * @phpVersion 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Modularity Posts "Data source" field group and field keys (must load before acf-fields.php).
 */
const SATER_POSTS_DATA_SOURCE_GROUP_KEY = 'group_571dfaabc3fc5';
const SATER_POSTS_DATA_SOURCE_FIELD_KEY = 'field_571dfaafe6984';
const SATER_POSTS_DATA_INPUT_REPEATER_KEY = 'field_576258d3110b0';
const SATER_POSTS_MANUAL_INPUT_ICON_FIELD_KEY = 'field_62a309f9c59bb';

require_once __DIR__ . '/acf-fields.php';
require_once __DIR__ . '/register-posts-source-field-group.php';

/**
 * Re-register the full local field group after Modularity loads (plugins_loaded).
 *
 * ACF filters alone are not enough when a DB-synced copy of the group exists;
 * replacing the local registration matches the production server snapshot.
 */
add_action(
    'acf/init',
    static function (): void {
        if (function_exists('acf_remove_local_field_group')) {
            acf_remove_local_field_group(SATER_POSTS_DATA_SOURCE_GROUP_KEY);
        }

        sater_posts_manual_input_register_posts_source_field_group();
    },
    20
);

/**
 * Patch field group when ACF loads from the database (synced copy).
 *
 * @param array<string, mixed>|false $fieldGroup ACF field group.
 * @return array<string, mixed>|false
 */
function sater_posts_manual_input_filter_load_field_group($fieldGroup)
{
    if (!is_array($fieldGroup)) {
        return $fieldGroup;
    }

    return sater_posts_manual_input_patch_posts_source_field_group($fieldGroup);
}

add_filter('acf/load_field_group', 'sater_posts_manual_input_filter_load_field_group', 99, 1);
add_filter(
    'acf/load_field_group/key=' . SATER_POSTS_DATA_SOURCE_GROUP_KEY,
    'sater_posts_manual_input_filter_load_field_group',
    99,
    1
);

/**
 * Patch fields list when ACF builds the edit screen.
 *
 * @param array<int, array<string, mixed>>|false $fields  Fields.
 * @param array<string, mixed>|string|false    $parent Parent group/field.
 * @return array<int, array<string, mixed>>|false
 */
function sater_posts_manual_input_filter_load_fields($fields, $parent)
{
    if (!is_array($fields)) {
        return $fields;
    }

    $parentKey = is_array($parent) ? ($parent['key'] ?? '') : (string) $parent;

    if ($parentKey !== SATER_POSTS_DATA_SOURCE_GROUP_KEY) {
        return $fields;
    }

    return sater_posts_manual_input_patch_posts_source_fields($fields);
}

add_filter('acf/load_fields', 'sater_posts_manual_input_filter_load_fields', 99, 2);

/**
 * Last resort at render time: ensure Manual input is a dropdown choice.
 *
 * @param array<string, mixed>|false $field ACF field.
 * @return array<string, mixed>|false
 */
function sater_posts_manual_input_filter_prepare_field($field)
{
    if (!is_array($field)) {
        return $field;
    }

    if (($field['name'] ?? '') !== 'posts_data_source') {
        return $field;
    }

    return sater_posts_manual_input_patch_data_source_field($field);
}

add_filter('acf/prepare_field/name=posts_data_source', 'sater_posts_manual_input_filter_prepare_field', 99, 1);
add_filter(
    'acf/prepare_field/key=' . SATER_POSTS_DATA_SOURCE_FIELD_KEY,
    'sater_posts_manual_input_filter_prepare_field',
    99,
    1
);

/**
 * Icon dropdown for manual input rows.
 */
add_filter(
    'Municipio/Admin/Acf/PrefillIconChoice',
    static function (array $fieldNames): array {
        $fieldNames[] = 'item_icon';
        $fieldNames[] = SATER_POSTS_MANUAL_INPUT_ICON_FIELD_KEY;

        return $fieldNames;
    }
);
