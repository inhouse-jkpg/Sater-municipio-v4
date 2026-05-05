<?php
/**
 * Plugin Name: Säter – post språk (ACF lang)
 * Description: Standardvärde för Modularity-fältet "lang" och normalisering av auto/tomt till webbplatsens språk.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Språkkod i samma form som Modularity ACF (t.ex. sv-se), avledd från get_bloginfo('language').
 */
function sater_post_lang_default_modularity_code(): string
{
    $locale = get_bloginfo('language');
    if (!is_string($locale) || $locale === '') {
        return 'sv-se';
    }

    return strtolower(str_replace('_', '-', $locale));
}

/**
 * Sant om vi ska ersätta tom/auto med sajtens språk (inte Modularity-moduler mod-*).
 */
function sater_post_lang_should_normalize_acf_lang_value(mixed $post_id): bool
{
    if (is_numeric($post_id) && (int) $post_id > 0) {
        $pt = get_post_type((int) $post_id);

        return !is_string($pt) || !str_starts_with($pt, 'mod-');
    }

    if (!is_admin() || !function_exists('get_current_screen')) {
        return true;
    }

    $screen = get_current_screen();
    if (!$screen || empty($screen->post_type)) {
        return true;
    }

    return !str_starts_with((string) $screen->post_type, 'mod-');
}

/**
 * (1) När ACF läser "lang": tom eller auto ska betyda webbplatsens språk (default för redaktörer).
 */
add_filter('acf/load_value/name=lang', 'sater_post_lang_acf_load_value_lang', 5, 3);
function sater_post_lang_acf_load_value_lang($value, mixed $post_id, array $field): mixed
{
    unset($field);

    if (!sater_post_lang_should_normalize_acf_lang_value($post_id)) {
        return $value === false || $value === null ? $value : (is_string($value) ? $value : (string) $value);
    }

    if ($value === null || $value === false || $value === '' || $value === 'auto') {
        return sater_post_lang_default_modularity_code();
    }

    return is_string($value) ? $value : (string) $value;
}

/**
 * (1) Säkerhet: om något fortfarande satt post_language till "auto" på WP_Post-objektet, ta bort det.
 */
add_filter('Municipio/Helper/Post/postObject', 'sater_post_lang_strip_auto_post_language', 5, 1);
function sater_post_lang_strip_auto_post_language(object $postObject): object
{
    if (!isset($postObject->post_language)) {
        return $postObject;
    }

    $pl = strtolower((string) $postObject->post_language);
    if ($pl === '' || $pl === 'auto') {
        unset($postObject->post_language);
    }

    return $postObject;
}
