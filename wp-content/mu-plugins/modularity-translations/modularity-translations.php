<?php
/**
 * Plugin Name: Modularity translations loader
 * Description: Load Modularity-related textdomains early and ship Swedish translations for select add-ons (incl. modularity-latest-news).
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

    // Säter Latest Events (Modularity) – source lives in mu-plugins; explicit load for stage/prod.
    Sater_Modularity_LoadTextdomainFromFile(
        'modularity-latest-news',
        $base . '/modularity-latest-news-' . $locale . '.mo'
    );
    if (
        function_exists('is_textdomain_loaded')
        && !is_textdomain_loaded('modularity-latest-news')
        && str_starts_with(strtolower((string) $locale), 'sv')
    ) {
        Sater_Modularity_LoadTextdomainFromFile(
            'modularity-latest-news',
            $base . '/modularity-latest-news-sv_SE.mo'
        );
    }
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
 * Full Swedish labels for the `news` CPT (matches Säter plugin + theme overrides).
 *
 * Municipio dynamic post types use sprintf(__('All %s', 'municipio'), $englishName), which yields
 * "Alla News" when the UI name in ACF is "News" and the locale is Swedish.
 *
 * @return array<string, string>
 */
function Sater_Modularity_GetSwedishNewsCptLabels(): array
{
    return [
        'name'               => 'Nyheter',
        'singular_name'      => 'Nyhet',
        'menu_name'          => 'Nyheter',
        'name_admin_bar'     => 'Nyhet',
        'all_items'          => 'Alla nyheter',
        'add_new'            => 'Skapa ny',
        'add_new_item'       => 'Lägg till nyhet',
        'new_item'           => 'Ny nyhet',
        'edit_item'          => 'Redigera nyhet',
        'update_item'        => 'Uppdatera nyhet',
        'view_item'          => 'Granska nyhet',
        'search_items'       => 'Sök: Nyhet',
        'parent_item_colon'  => 'Föräldra nyhet',
        'not_found'          => 'Inga nyheter hittades',
        'not_found_in_trash' => 'Inga nyheter i papperskorgen',
    ];
}

/**
 * When Municipio registers a dynamic CPT whose slug resolves to `news`, replace English-based labels.
 *
 * @param array<string, string> $labels          Built labels.
 * @param array<string, mixed>  $typeDefinition  ACF row from avabile_dynamic_post_types.
 * @return array<string, string>
 */
function Sater_Modularity_FilterMunicipioDynamicNewsLabels(array $labels, array $typeDefinition): array
{
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    if (!Sater_Municipio_IsSwedishLocale((string) $locale)) {
        return $labels;
    }

    $slug = sanitize_title(substr((string) ($typeDefinition['post_type_name'] ?? ''), 0, 19));
    if ($slug !== 'news') {
        return $labels;
    }

    return array_merge($labels, Sater_Modularity_GetSwedishNewsCptLabels());
}

add_filter('Municipio/CustomPostType/labels', 'Sater_Modularity_FilterMunicipioDynamicNewsLabels', 25, 2);

/**
 * Late pass so `news` labels stay Swedish even if another component registered the CPT first.
 *
 * @param array<string, mixed> $args      Post type args.
 * @param string               $post_type Post type name.
 * @return array<string, mixed>
 */
function Sater_Modularity_FilterRegisterPostTypeArgsNews(array $args, string $post_type): array
{
    if ($post_type !== 'news') {
        return $args;
    }

    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    if (!Sater_Municipio_IsSwedishLocale((string) $locale)) {
        return $args;
    }

    if (!isset($args['labels']) || !is_array($args['labels'])) {
        $args['labels'] = [];
    }

    $args['label'] = 'Nyheter';
    $args['labels'] = array_merge($args['labels'], Sater_Modularity_GetSwedishNewsCptLabels());

    return $args;
}

add_filter('register_post_type_args', 'Sater_Modularity_FilterRegisterPostTypeArgsNews', 100, 2);

/**
 * Last-resort fix if a submenu title was already built as "Alla News".
 *
 * @return void
 */
function Sater_Modularity_FixNewsAdminSubmenu(): void
{
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    if (!Sater_Municipio_IsSwedishLocale((string) $locale)) {
        return;
    }

    global $submenu;
    $hook = 'edit.php?post_type=news';
    if (empty($submenu[$hook]) || !is_array($submenu[$hook])) {
        return;
    }

    foreach ($submenu[$hook] as $index => $item) {
        if (!isset($item[0]) || !is_string($item[0])) {
            continue;
        }
        if ($item[0] === 'Alla News') {
            $submenu[$hook][$index][0] = 'Alla nyheter';
        }
    }
}

add_action('admin_menu', 'Sater_Modularity_FixNewsAdminSubmenu', 9999);

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
 * Swedish admin string overrides keyed by text domain.
 *
 * Maps English msgids from screenshots and codebase to Swedish. Kept in an MU plugin so deploy does
 * not depend on theme or plugin .mo compilation.
 *
 * @return array<string, array<string, string>>
 */
function Sater_Modularity_SwedishGettextMapByDomain(): array
{
    return [
        'municipio' => [
            'Reset filter'           => 'Rensa filter',
            'Select'                 => 'Välj',
            // Dynamic CPT: sprintf(__('All %s'), 'News') under Swedish locale.
            'Alla News'              => 'Alla nyheter',
            'API Resources'          => 'API-resurser',
            'Modal Content'          => 'Modalinnehåll',
            'All Modal Contents'     => 'Alla modalinnehåll',
            'Post type modules'      => 'Posttyp moduler',
            'Add New Modal Content'  => 'Lägg till modalinnehåll',
            'Add Modal Content'      => 'Lägg till modalinnehåll',
            'New Modal Content'      => 'Nytt modalinnehåll',
            'Edit Modal Content'     => 'Redigera modalinnehåll',
            'Update Modal Content'   => 'Uppdatera modalinnehåll',
            'View Modal Content'     => 'Visa modalinnehåll',
            'View Modal Contents'    => 'Visa modalinnehåll',
            'Search For Modal Content' => 'Sök modalinnehåll',
            'Default post types'   => 'Standardinläggstyper',
            'Default Pages'        => 'Standardsidor',
            'Default pages'        => 'Standardsidor',
        ],
        'default' => [
            // Municipio Modularity `Editor.php` uses __() without text domain for the options page title.
            'Modularity editor'    => 'Modularitetsredigerare',
            'News'                 => 'Nyheter',
            // CPT `label` used __( 'news' ); English UI often title-cases this to "News".
            'news'                 => 'Nyheter',
            'Add New Media File'   => 'Lägg till ny mediefil',
            'Add New Media'        => 'Lägg till nytt medium',
            'Default Pages'        => 'Standardsidor',
            'Default pages'        => 'Standardsidor',
        ],
        'modularity' => [
            'News'                 => 'Nyheter',
            'Post type modules'    => 'Posttyp moduler',
            'Modal Content'        => 'Modalinnehåll',
            'All Modal Contents'   => 'Alla modalinnehåll',
            'Modularity'           => 'Modularitet',
            // Editor screen (see `templates/editor/modularity-sidebar-drop-area.php`, `source/php/Editor.php`).
            'Modularity editor'    => 'Modularitetsredigerare',
            'Editor'               => 'Redigerare',
            'Content'              => 'Innehåll',
            'Modules'              => 'Moduler',
            'Drag your modules here…' => 'Dra dina moduler hit …',
            'Drag your modules here...' => 'Dra dina moduler hit …',
            'Show modules'         => 'Visa moduler',
            'before'               => 'före',
            'after'                => 'efter',
            'Hide global widgets'  => 'Dölj globala widgetar',
            'Save modules'         => 'Spara moduler',
            'Enabled modules'      => 'Aktiva moduler',
            'Hero'                 => 'Huvudsektion',
        ],
        'action-scheduler' => [
            'Scheduled Actions'        => 'Schemalagda åtgärder',
            'Search Scheduled Actions' => 'Sök schemalagda åtgärder',
        ],
        'wds' => [
            'SEO'                    => 'Sökoptimering',
            'Post Types'             => 'Inläggstyper',
            'Taxonomies'             => 'Taxonomier',
            'Redirection'            => 'Omdirigering',
            'URL Redirection'        => 'URL-omdirigering',
        ],
        'redirection' => [
            'Redirection'            => 'Omdirigering',
            'Redirection Extended'   => 'Utökad omdirigering',
        ],
    ];
}

/**
 * Apply Swedish gettext overrides for configured domains.
 *
 * @param string $translation Translated text.
 * @param string $text        Source text.
 * @param string $domain      Textdomain.
 * @return string
 */
function Sater_Modularity_SwedishGettext(string $translation, string $text, string $domain): string
{
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    if (!Sater_Municipio_IsSwedishLocale((string) $locale)) {
        return $translation;
    }

    $map = Sater_Modularity_SwedishGettextMapByDomain();
    if (isset($map[$domain][$text])) {
        return $map[$domain][$text];
    }

    return $translation;
}

/**
 * Swedish gettext with context (e.g. admin menu labels).
 *
 * @param string $translation Translated text.
 * @param string $text        Source text.
 * @param string $context     Context.
 * @param string $domain      Textdomain.
 * @return string
 */
function Sater_Modularity_SwedishGettextWithContext(
    string $translation,
    string $text,
    string $context,
    string $domain
): string {
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    if (!Sater_Municipio_IsSwedishLocale((string) $locale)) {
        return $translation;
    }

    if ($domain === 'action-scheduler' && $context === 'Admin menu name' && $text === 'Scheduled Actions') {
        return 'Schemalagda åtgärder';
    }

    return $translation;
}

add_filter('gettext', 'Sater_Modularity_SwedishGettext', 20, 3);
add_filter('gettext_with_context', 'Sater_Modularity_SwedishGettextWithContext', 20, 4);

/**
 * Localize Modularity editor title suffix (template slug is not passed through gettext).
 *
 * @param array<string, mixed> $editing Value from `Modularity/is_editing`.
 * @return array<string, mixed>
 */
function Sater_Modularity_TranslateEditorContextTitle(array $editing): array
{
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    if (!Sater_Municipio_IsSwedishLocale((string) $locale)) {
        return $editing;
    }

    if (!isset($editing['title']) || !is_string($editing['title'])) {
        return $editing;
    }

    static $titles = null;
    if ($titles === null) {
        $titles = [
            'single-news'   => 'enskild nyhet',
            'archive-news'  => 'nyhetsarkiv',
            'single-post'   => 'enskilt inlägg',
            'archive-post'  => 'inläggsarkiv',
            'single-page'   => 'enskild sida',
            'archive-page'  => 'sidarkiv',
            'home'          => 'startsida',
        ];
    }

    $slug = $editing['title'];
    if (isset($titles[$slug])) {
        $editing['title'] = $titles[$slug];
    }

    return $editing;
}

add_filter('Modularity/is_editing', 'Sater_Modularity_TranslateEditorContextTitle', 10, 1);

/**
 * The sidebar template prints the word "widgets" as raw text (no translation wrapper).
 *
 * @return void
 */
function Sater_Modularity_EditorWidgetsLabelScript(): void
{
    if (($GLOBALS['pagenow'] ?? '') !== 'options.php' || ($_GET['page'] ?? '') !== 'modularity-editor') {
        return;
    }

    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    if (!Sater_Municipio_IsSwedishLocale((string) $locale)) {
        return;
    }

    echo '<script>jQuery(function($){$(".modularity-sidebar-options select").each(function(){$(this).parent().contents().filter(function(){return this.nodeType===Node.TEXT_NODE&&this.textContent.trim()==="widgets";}).each(function(){this.textContent=" widgetar ";});});});</script>';
}

add_action('admin_print_footer_scripts', 'Sater_Modularity_EditorWidgetsLabelScript', 99);

// Retry at plugins_loaded to handle edge cases where locale changes later.
add_action('plugins_loaded', 'Sater_Modularity_LoadCoreTextdomain', 0);
add_action('plugins_loaded', 'Sater_Modularity_LoadBundledAddonTextdomains', 0);

