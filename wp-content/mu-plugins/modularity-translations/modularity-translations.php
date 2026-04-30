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
}

// Included via `mu-plugins/loader.php` during `muplugins_loaded`, so load immediately.
Sater_Modularity_LoadCoreTextdomain();
Sater_Modularity_LoadBundledAddonTextdomains();

// Retry at plugins_loaded to handle edge cases where locale changes later.
add_action('plugins_loaded', 'Sater_Modularity_LoadCoreTextdomain', 0);
add_action('plugins_loaded', 'Sater_Modularity_LoadBundledAddonTextdomains', 0);

