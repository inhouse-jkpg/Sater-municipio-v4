<?php
/**
 * Plugin Name: Modularity translations loader
 * Description: Load Modularity-related textdomains early and ship Swedish translations for select add-ons.
 *
 * @category WordPress
 * @package  Sater
 * @author   Municipio SE <dev@municipio.se>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://sater.se
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load a textdomain from an explicit .mo file path.
 *
 * @param string $domain Textdomain.
 * @param string $mofile Absolute path to .mo file.
 *
 * @return void
 */
function Sater_Modularity_LoadTextdomainFromFile(string $domain, string $mofile): void
{
    if (function_exists('is_textdomain_loaded') && is_textdomain_loaded($domain)) {
        return;
    }

    if (file_exists($mofile)) {
        load_textdomain($domain, $mofile);
    }
}

/**
 * Load Modularity core translations early.
 *
 * @return void
 */
function Sater_Modularity_LoadCoreTextdomain(): void
{
    $domain = 'modularity';
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    $mofile = WP_PLUGIN_DIR . '/' . $domain . '/languages/' . $domain . '-' . $locale . '.mo';

    Sater_Modularity_LoadTextdomainFromFile($domain, $mofile);
}

/**
 * Load bundled add-on translations (kept in this mu-plugin).
 *
 * @return void
 */
function Sater_Modularity_LoadBundledAddonTextdomains(): void
{
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    $base = __DIR__ . '/languages';

    Sater_Modularity_LoadTextdomainFromFile(
        'modularity-contact',
        $base . '/modularity-contact-' . $locale . '.mo'
    );

    Sater_Modularity_LoadTextdomainFromFile(
        'modularity-contact-banner',
        $base . '/modularity-contact-banner-' . $locale . '.mo'
    );

    Sater_Modularity_LoadTextdomainFromFile(
        'mod-my-pages',
        $base . '/mod-my-pages-' . $locale . '.mo'
    );
}

// Included via `mu-plugins/loader.php` during `muplugins_loaded`, so load immediately.
Sater_Modularity_LoadCoreTextdomain();
Sater_Modularity_LoadBundledAddonTextdomains();

/**
 * True when Swedish (any sv_* locale).
 *
 * @param string $locale Locale string.
 * @return bool
 */
function Sater_Municipio_IsSwedishLocale(string $locale): bool
{
    $locale = strtolower($locale);
    return str_starts_with($locale, 'sv');
}

/**
 * Empty-archive copy for events when category and or date filters are active.
 *
 * @return string|null Custom message or null to keep Municipio default.
 */
function Sater_Municipio_GetEventsArchiveFilteredEmptyMessage(): ?string
{
    $hasCats = isset($_GET['evenemangskategorier'])
        && is_array($_GET['evenemangskategorier'])
        && !empty(array_filter($_GET['evenemangskategorier']));

    $from = isset($_GET['from']) ? (string) $_GET['from'] : '';
    $to   = isset($_GET['to']) ? (string) $_GET['to'] : '';
    if ($from === '' && $to === '' && function_exists('get_query_var')) {
        $from = (string) get_query_var('from', '');
        $to   = (string) get_query_var('to', '');
    }
    $hasDates = (trim($from) !== '' || trim($to) !== '');

    if ($hasCats && !$hasDates) {
        return 'Det finns inga evenemang inom den kategorin.';
    }

    if (!$hasCats && $hasDates) {
        return 'Det finns inga evenemang under de här datumen.';
    }

    if ($hasCats && $hasDates) {
        return 'Det finns inga evenemang som matchar dina val.';
    }

    return null;
}

/**
 * Set archive empty notice after Municipio built $viewData (gettext on msgid is too late once $lang->noResult is already translated).
 *
 * @param mixed $viewData Municipio Blade view data.
 * @return mixed
 */
function Sater_Municipio_PatchEventsArchiveViewDataNoResult($viewData)
{
    if (!is_array($viewData)) {
        return $viewData;
    }

    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    if (!Sater_Municipio_IsSwedishLocale((string) $locale)) {
        return $viewData;
    }

    $postType = $viewData['postType'] ?? '';
    if ($postType !== 'events' && $postType !== 'event') {
        return $viewData;
    }

    if (!empty($viewData['posts'])) {
        return $viewData;
    }

    $custom = Sater_Municipio_GetEventsArchiveFilteredEmptyMessage();
    if ($custom === null) {
        return $viewData;
    }

    if (isset($viewData['lang']) && is_object($viewData['lang'])) {
        $viewData['lang']->noResult = $custom;
    } elseif (isset($viewData['lang']) && is_array($viewData['lang'])) {
        $viewData['lang']['noResult'] = $custom;
    }

    return $viewData;
}

add_filter('Municipio/Template/viewData', 'Sater_Municipio_PatchEventsArchiveViewDataNoResult', 99);

/**
 * Override select Municipio translations for sv_SE.
 *
 * We do this in an MU plugin so production deploy does not depend on theme .mo compilation or caches.
 *
 * @param string $translation Translated text.
 * @param string $text        Source text.
 * @param string $domain      Textdomain.
 * @return string
 */
function Sater_Municipio_TranslationOverrides(string $translation, string $text, string $domain): string
{
    if ($domain !== 'municipio') {
        return $translation;
    }

    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    if (!Sater_Municipio_IsSwedishLocale((string) $locale)) {
        return $translation;
    }

    if ($text === 'Reset filter') {
        return 'Rensa filter';
    }

    return $translation;
}

add_filter('gettext', 'Sater_Municipio_TranslationOverrides', 20, 3);

// Retry at plugins_loaded to handle edge cases where locale changes later.
add_action('plugins_loaded', 'Sater_Modularity_LoadCoreTextdomain', 0);
add_action('plugins_loaded', 'Sater_Modularity_LoadBundledAddonTextdomains', 0);

