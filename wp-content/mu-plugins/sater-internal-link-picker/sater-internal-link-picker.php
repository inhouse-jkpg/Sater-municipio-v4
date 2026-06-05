<?php
/**
 * Plugin Name: Säter Intern länkväljare
 * Description: Förbättrar Modularitys interna länkfält (Bild, Slider, Lista, Index): visar Sidor först, tar bort Media/tekniska posttyper och höjer antalet sökträffar så rätt sida hittas snabbare.
 * Version: 1.0.1
 * Author: Säter kommun
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Set while an internal-link ACF query is running (WP_Query ignores custom args).
 */
function sater_internal_link_picker_mark_query_active(): void
{
    $GLOBALS['sater_internal_link_picker_active'] = true;
}

function sater_internal_link_picker_is_query_active(): bool
{
    return !empty($GLOBALS['sater_internal_link_picker_active']);
}

function sater_internal_link_picker_clear_query_active(): void
{
    $GLOBALS['sater_internal_link_picker_active'] = false;
}

/**
 * Default post types selectable as internal links, in display order.
 * "page" is intentionally first so editors find pages immediately.
 *
 * Adjust via the filter 'sater/internal_link_picker/post_types'.
 *
 * @return array<int, string>
 */
function sater_internal_link_picker_post_types(): array
{
    $postTypes = ['page', 'news', 'events', 'post'];

    /**
     * Filter the post types (and their order) offered in internal link fields.
     *
     * @param array<int, string> $postTypes
     */
    $postTypes = apply_filters('sater/internal_link_picker/post_types', $postTypes);

    // Keep only registered post types, preserve given order, drop duplicates.
    $registered = get_post_types([], 'names');
    $postTypes  = array_values(array_unique(array_filter(
        $postTypes,
        static fn ($postType): bool => is_string($postType) && isset($registered[$postType])
    )));

    return $postTypes;
}

/**
 * Number of results fetched per AJAX page when searching/browsing.
 * ACF defaults to 20, which buries pages behind other post types.
 */
function sater_internal_link_picker_per_page(): int
{
    return (int) apply_filters('sater/internal_link_picker/posts_per_page', 50);
}

/**
 * Whether the ACF query is browsing (empty search) vs actively searching.
 *
 * @param array<string, mixed> $args
 */
function sater_internal_link_picker_is_browsing(array $args): bool
{
    return !isset($args['s']) || $args['s'] === '' || $args['s'] === null;
}

/**
 * Apply the Säter internal-link constraints to an ACF query args array.
 *
 * When the dropdown opens (no search term) only pages are loaded so "Sidor"
 * is the first and only group. When the editor types a search term, all
 * allowed post types are included with "page" first in the list.
 *
 * @param array<string, mixed> $args
 * @return array<string, mixed>
 */
function sater_internal_link_picker_filter_args(array $args): array
{
    $allTypes = sater_internal_link_picker_post_types();

    if (sater_internal_link_picker_is_browsing($args)) {
        $args['post_type'] = in_array('page', $allTypes, true) ? ['page'] : $allTypes;
    } elseif (!empty($allTypes)) {
        $args['post_type'] = $allTypes;
    }

    $args['posts_per_page'] = sater_internal_link_picker_per_page();
    sater_internal_link_picker_mark_query_active();

    return $args;
}

/**
 * Keep SQL result order aligned with our post-type priority when multiple
 * types are queried (search). Runs after custom-events-order (prio 10)
 * which otherwise replaces orderby and pushes events to the top.
 */
add_filter('posts_orderby', static function (string $orderby, \WP_Query $query): string {
    if (!is_admin() || !sater_internal_link_picker_is_query_active()) {
        return $orderby;
    }

    $postTypes = array_values(array_filter((array) $query->get('post_type')));
    if (count($postTypes) <= 1) {
        return $orderby;
    }

    $allowed = sater_internal_link_picker_post_types();
    $ordered = array_values(array_filter(
        $allowed,
        static fn (string $postType): bool => in_array($postType, $postTypes, true)
    ));

    if (count($ordered) < 2) {
        return $orderby;
    }

    global $wpdb;
    $escaped = array_map('esc_sql', $ordered);

    return "FIELD({$wpdb->posts}.post_type,'" . implode("','", $escaped) . "'),"
        . " {$wpdb->posts}.menu_order ASC, {$wpdb->posts}.post_title ASC";
}, 11, 2);

add_filter('posts_results', static function (array $posts): array {
    sater_internal_link_picker_clear_query_active();

    return $posts;
}, 11, 1);

/**
 * page_link fields are always link pickers (Bild, Slider, etc.).
 */
add_filter('acf/fields/page_link/query', static function ($args) {
    return sater_internal_link_picker_filter_args(is_array($args) ? $args : []);
}, 99, 1);

/**
 * post_object is used for many purposes, so only known internal-link
 * fields are targeted (not manual post listing, modal picker, etc.).
 */

// Lista (Inlay List): field "link_internal".
add_filter('acf/fields/post_object/query/name=link_internal', static function ($args) {
    return sater_internal_link_picker_filter_args(is_array($args) ? $args : []);
}, 99, 1);

// Index: field "page" (key field_569cf1252cfc9).
add_filter('acf/fields/post_object/query/key=field_569cf1252cfc9', static function ($args) {
    return sater_internal_link_picker_filter_args(is_array($args) ? $args : []);
}, 99, 1);

/**
 * Reorder grouped Select2 results so "Sidor" is always first when searching
 * across multiple post types (ACF/Select2 may otherwise sort alphabetically).
 */
add_action('admin_enqueue_scripts', static function (): void {
    $scriptPath = __DIR__ . '/admin.js';
    if (!is_readable($scriptPath)) {
        return;
    }

    wp_enqueue_script(
        'sater-internal-link-picker',
        content_url('mu-plugins/sater-internal-link-picker/admin.js'),
        ['acf-input'],
        (string) filemtime($scriptPath),
        true
    );

    $labels = [];
    foreach (sater_internal_link_picker_post_types() as $postType) {
        $object = get_post_type_object($postType);
        if ($object !== null) {
            $labels[] = $object->labels->name;
        }
    }

    wp_localize_script('sater-internal-link-picker', 'saterInternalLinkPicker', [
        'groupOrder' => $labels,
    ]);
}, 20);
