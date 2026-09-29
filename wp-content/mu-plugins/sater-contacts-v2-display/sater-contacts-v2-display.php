<?php
/**
 * Plugin Name: Säter Kontakter v2 display
 * Description: Visar jobbtitel före förvaltning/sektor på egna rader i Modularity Kontakter v2.
 * Version: 1.0.1
 * Author: Säter kommun
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SATER_CONTACTS_V2_VIEWS_DIR = __DIR__ . '/views';
const SATER_CONTACTS_V2_COMPONENT_VIEWS_DIR = __DIR__ . '/views/components';

add_filter('/Modularity/externalViewPath', 'sater_contacts_v2_external_view_paths', 10, 1);
add_filter('ComponentLibrary/ViewPaths', 'sater_contacts_v2_prepend_component_views', 1);
add_filter('body_class', 'sater_contacts_v2_body_class');
add_action('wp_enqueue_scripts', 'sater_contacts_v2_enqueue_assets', 100);
add_filter('acf/validate_value/type=image', 'sater_contacts_v2_skip_decorative_avatar_alt_validation', 11, 4);
add_filter('ComponentLibrary/Component/Image/Attribute', 'sater_contacts_v2_contact_avatar_attributes', 10, 1);

/**
 * Enable WCAG 1.4.11 border contrast on contact cards.
 *
 * Set in wp-config or config:
 *   define('SATER_CONTACTS_V2_A11Y_BORDERS', true);
 *
 * Or via filter:
 *   add_filter('sater_contacts_v2_a11y_borders_enabled', '__return_true');
 */
function sater_contacts_v2_a11y_borders_enabled(): bool
{
    if (defined('SATER_CONTACTS_V2_A11Y_BORDERS')) {
        return (bool) SATER_CONTACTS_V2_A11Y_BORDERS;
    }

    return (bool) apply_filters('sater_contacts_v2_a11y_borders_enabled', false);
}

/**
 * @param array<int, string> $classes
 * @return array<int, string>
 */
function sater_contacts_v2_body_class(array $classes): array
{
    if (sater_contacts_v2_a11y_borders_enabled()) {
        $classes[] = 'sater-contacts-v2--a11y-borders';
    }

    return $classes;
}

function sater_contacts_v2_enqueue_assets(): void
{
    $cssPath = __DIR__ . '/assets/contacts.css';

    if (!file_exists($cssPath)) {
        return;
    }

    wp_enqueue_style(
        'sater-contacts-v2-display',
        plugin_dir_url(__FILE__) . 'assets/contacts.css',
        [],
        (string) filemtime($cssPath)
    );
}

/**
 * Prioritize Säter cards view; fall back to Modularity for component partials.
 *
 * @param array<string, string|array<int, string>> $paths
 * @return array<string, string|array<int, string>>
 */
function sater_contacts_v2_external_view_paths(array $paths): array
{
    if (!defined('MODULARITY_PATH')) {
        return $paths;
    }

    $modularityViews = MODULARITY_PATH . 'source/php/Module/Contacts/views';

    // Blade prepends paths in array order; the last entry is searched first.
    // Put Modularity first so Säter's cards.blade.php wins, with Modularity as fallback for components.*.
    $paths['mod-contacts'] = [
        $modularityViews,
        SATER_CONTACTS_V2_VIEWS_DIR,
    ];

    return $paths;
}

/**
 * Override shared component templates for contact card accessibility.
 *
 * @param array<int, string> $viewPaths
 * @return array<int, string>
 */
function sater_contacts_v2_prepend_component_views(array $viewPaths): array
{
    if (!is_dir(SATER_CONTACTS_V2_COMPONENT_VIEWS_DIR)) {
        return $viewPaths;
    }

    $root = SATER_CONTACTS_V2_COMPONENT_VIEWS_DIR;

    $filtered = array_values(array_filter(
        $viewPaths,
        static function ($path) use ($root): bool {
            return rtrim((string) $path, '/\\') !== $root;
        }
    ));

    array_unshift($filtered, $root);

    return $filtered;
}

/**
 * Contact avatars are decorative; the name is shown in the signature.
 * Skip Municipio's media-library alt requirement for contact image fields.
 *
 * @param bool|string $valid
 * @param mixed $value
 * @param array<string, mixed> $field
 * @param string $input
 * @return bool|string
 */
function sater_contacts_v2_skip_decorative_avatar_alt_validation($valid, $value, $field, $input)
{
    $exemptFieldKeys = [
        'field_5805e5dc26dde', // Contacts v2 custom layout image
        'field_56c714f8d0a42', // User profile picture (User contact layout)
    ];

    if (!in_array($field['key'] ?? '', $exemptFieldKeys, true)) {
        return $valid;
    }

    if ($valid !== true && is_string($valid)) {
        return true;
    }

    return $valid;
}

/**
 * Contact avatars use alt="" (decorative). Suppress Municipio's editor-only
 * data-a11y-error flag when the avatar is explicitly marked decorative.
 *
 * The Attribute filter runs twice: array while building, then the rendered string.
 *
 * @param array<string, string>|string $attribute
 * @return array<string, string>|string
 */
function sater_contacts_v2_contact_avatar_attributes(array|string $attribute): array|string
{
    if (!is_array($attribute)) {
        if (strpos($attribute, 'data-decorative-avatar') === false) {
            return $attribute;
        }

        $attribute = preg_replace('/\s*data-a11y-error="[^"]*"/', '', $attribute);

        return (string) preg_replace('/\s*data-decorative-avatar="[^"]*"/', '', $attribute);
    }

    if (($attribute['data-decorative-avatar'] ?? '') !== 'true') {
        return $attribute;
    }

    unset($attribute['data-a11y-error'], $attribute['data-decorative-avatar']);

    return $attribute;
}
