<?php
/**
 * Plugin Name: Säter Tillgänglighet (A11y)
 * Description: Accessibility fixes for WCAG compliance: sticky header and archive date picker.
 * Version: 1.0.0
 * Author: Säter kommun
 * License: MIT
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SATER_A11Y_COMPONENT_VIEWS_DIR = __DIR__ . '/views/components';
const SATER_A11Y_MODULARITY_VIEWS_DIR = __DIR__ . '/views/modularity';
const SATER_A11Y_ETJANSTER_PAGE_SLUG = 'e-tjanster-och-blanketter';
const SATER_A11Y_MI_IMAGE_FIELD_KEY = 'field_64ff2355d91bb';

add_action('wp_enqueue_scripts', 'sater_a11y_enqueue_assets', 100);
add_action('template_redirect', 'sater_a11y_ob_start', 1);
add_filter('ComponentLibrary/ViewPaths', 'sater_a11y_prepend_component_views', 1);
add_filter('/Modularity/externalViewPath', 'sater_a11y_external_view_paths', 20, 1);
add_filter(
    'ComponentLibrary/Component/Image/Data',
    'sater_a11y_fix_responsive_images',
    10,
    1
);
add_filter(
    'ComponentLibrary/Component/Icon/Data',
    'sater_a11y_icon_data',
    10,
    1
);
add_filter(
    'ComponentLibrary/Component/Button/Data',
    'sater_a11y_button_data',
    10,
    1
);
add_filter(
    'ComponentLibrary/Component/Button/Attribute',
    'sater_a11y_button_attribute',
    10,
    1
);
add_filter(
    'ComponentLibrary/Component/Field/Data',
    'sater_a11y_field_data',
    10,
    1
);
add_filter(
    'Modularity/Display/mod-manualinput/viewData',
    'sater_a11y_flag_etjanster_page_cards',
    5,
    1
);
add_filter('acf/validate_value/type=image', 'sater_a11y_skip_etjanster_mi_image_alt_validation', 11, 4);
add_filter('ComponentLibrary/Component/Image/Attribute', 'sater_a11y_decorative_card_image_attributes', 10, 1);
add_filter('ComponentLibrary/Component/Image/Alt', 'sater_a11y_decorative_card_image_alt', 10, 2);
add_filter(
    'ComponentLibrary/Component/Iframe/Data',
    'sater_a11y_youtube_iframe_data',
    10,
    1
);

/**
 * Whether the button currently being rendered has visible text content.
 */
class Sater_A11y_Control_Label_State
{
    public static bool $hasVisibleText = false;
}

/**
 * Prioritize Säter component view overrides for accessibility fixes.
 *
 * @param array<int, string> $viewPaths
 * @return array<int, string>
 */
function sater_a11y_prepend_component_views(array $viewPaths): array
{
    if (!is_dir(SATER_A11Y_COMPONENT_VIEWS_DIR)) {
        return $viewPaths;
    }

    $root = SATER_A11Y_COMPONENT_VIEWS_DIR;

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
 * Override Modularity views for accessibility fixes (Manual Input, Inlay List).
 *
 * Merged with paths from other plugins (e.g. sater-manualinput-accordion-fields).
 * Blade searches the last path first, so append the a11y views directory.
 *
 * @param array<string, string|array<int, string>> $paths
 * @return array<string, string|array<int, string>>
 */
function sater_a11y_external_view_paths(array $paths): array
{
    if (!defined('MODULARITY_PATH') || !is_dir(SATER_A11Y_MODULARITY_VIEWS_DIR)) {
        return $paths;
    }

    $paths['mod-manualinput'] = sater_a11y_append_modularity_view_path(
        $paths['mod-manualinput'] ?? (MODULARITY_PATH . 'source/php/Module/ManualInput/views')
    );

    $paths['mod-inlaylist'] = sater_a11y_append_modularity_view_path(
        $paths['mod-inlaylist'] ?? (MODULARITY_PATH . 'source/php/Module/InlayList/views')
    );

    return $paths;
}

/**
 * Append the a11y Modularity views directory (last = searched first by Blade).
 *
 * @param string|array<int, string> $existing
 * @return array<int, string>
 */
function sater_a11y_append_modularity_view_path(string|array $existing): array
{
    if (!is_array($existing)) {
        $existing = [$existing];
    }

    $a11yRoot = rtrim(SATER_A11Y_MODULARITY_VIEWS_DIR, '/\\');
    $existing = array_values(array_filter(
        $existing,
        static function ($path) use ($a11yRoot): bool {
            return rtrim((string) $path, '/\\') !== $a11yRoot;
        }
    ));

    $existing[] = SATER_A11Y_MODULARITY_VIEWS_DIR;

    return $existing;
}

/**
 * Treat icons as decorative when a parent already requested aria-hidden.
 *
 * The default Button template passes aria-hidden on icons, but Icon::init()
 * overwrites it unless decorative is true.
 *
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_a11y_icon_data(array $data): array
{
    if (($data['attributeList']['aria-hidden'] ?? '') === 'true') {
        $data['decorative'] = true;
    }

    return $data;
}

/**
 * Track whether the current button has visible text for aria-label cleanup.
 *
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_a11y_button_data(array $data): array
{
    Sater_A11y_Control_Label_State::$hasVisibleText = !empty($data['text']);

    return $data;
}

/**
 * Remove redundant aria-label when visible button text is present.
 *
 * The Attribute filter is invoked twice: first with an array while building
 * attributes, then with the rendered string when getData() filters each key.
 *
 * @param array<string, string>|string $attribute
 * @return array<string, string>|string
 */
function sater_a11y_button_attribute(array|string $attribute): array|string
{
    if (!is_array($attribute) || !Sater_A11y_Control_Label_State::$hasVisibleText) {
        return $attribute;
    }

    unset($attribute['aria-label']);

    return $attribute;
}

/**
 * Hide decorative field icons when the field already has a text label.
 *
 * Covers the hero search magnifying glass and other labelled inputs.
 *
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_a11y_field_data(array $data): array
{
    if (empty($data['label']) || !is_array($data['icon'] ?? null)) {
        return $data;
    }

    $data['icon']['decorative'] = true;

    return $data;
}

/**
 * Whether the current request is the E-tjänster och blanketter page.
 */
function sater_a11y_is_etjanster_page(): bool
{
    return is_page(SATER_A11Y_ETJANSTER_PAGE_SLUG);
}

/**
 * Page ID for E-tjänster och blanketter, or null if the page does not exist.
 */
function sater_a11y_get_etjanster_page_id(): ?int
{
    $page = get_page_by_path(SATER_A11Y_ETJANSTER_PAGE_SLUG);

    return $page instanceof WP_Post ? (int) $page->ID : null;
}

/**
 * Flag E-tjänster Manual Input cards for link-grid a11y overrides (decorative images, non-heading titles).
 *
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_a11y_flag_etjanster_page_cards(array $data): array
{
    if (sater_a11y_is_etjanster_page()) {
        $GLOBALS['sater_a11y_etjanster_page_cards'] = true;
    }

    return $data;
}

/**
 * Whether a Modularity module is used on the E-tjänster page.
 */
function sater_a11y_module_used_on_etjanster_page(int $moduleId): bool
{
    $pageId = sater_a11y_get_etjanster_page_id();

    if ($pageId === null || $moduleId < 1) {
        return false;
    }

    if (!class_exists(\Modularity\Helper\ModuleUsageById::class)) {
        return false;
    }

    foreach (\Modularity\Helper\ModuleUsageById::getModuleUsageById((string) $moduleId) as $page) {
        if ((int) ($page->post_id ?? 0) === $pageId) {
            return true;
        }
    }

    return false;
}

/**
 * Manual Input card icons on E-tjänster are decorative; titles are in the card text.
 * Skip Municipio's media-library alt requirement when editing that module.
 *
 * @param bool|string $valid
 * @param mixed $value
 * @param array<string, mixed> $field
 * @param string $input
 * @return bool|string
 */
function sater_a11y_skip_etjanster_mi_image_alt_validation($valid, $value, $field, $input)
{
    if (($field['key'] ?? '') !== SATER_A11Y_MI_IMAGE_FIELD_KEY) {
        return $valid;
    }

    $postId = isset($_POST['post_ID']) ? (int) $_POST['post_ID'] : 0;

    if ($postId < 1 || !sater_a11y_module_used_on_etjanster_page($postId)) {
        return $valid;
    }

    if ($valid !== true && is_string($valid)) {
        return true;
    }

    return $valid;
}

/**
 * E-tjänster card icons use alt="" (decorative). Suppress Municipio's editor-only
 * data-a11y-error flag when explicitly marked decorative.
 *
 * The Attribute filter runs twice: array while building, then the rendered string.
 *
 * @param array<string, string>|string $attribute
 * @return array<string, string>|string
 */
function sater_a11y_decorative_card_image_attributes(array|string $attribute): array|string
{
    if (!is_array($attribute)) {
        if (strpos($attribute, 'data-decorative-card-image') === false) {
            return $attribute;
        }

        $attribute = preg_replace('/\s*data-a11y-error="[^"]*"/', '', $attribute);

        return (string) preg_replace('/\s*data-decorative-card-image="[^"]*"/', '', $attribute);
    }

    if (($attribute['data-decorative-card-image'] ?? '') !== 'true') {
        return $attribute;
    }

    unset($attribute['data-a11y-error'], $attribute['data-decorative-card-image']);

    return $attribute;
}

/**
 * Force empty alt on decorative E-tjänster card icons at render time.
 *
 * @param string|null $alt
 * @param array<int, string> $context
 * @return string|null
 */
function sater_a11y_decorative_card_image_alt($alt, array $context): ?string
{
    if (!empty($GLOBALS['sater_a11y_etjanster_page_cards'])) {
        return '';
    }

    return is_string($alt) ? $alt : null;
}

/**
 * Keep YouTube video iframes out of the tab order to avoid keyboard traps.
 *
 * YouTube exposes focusable endscreen links inside the cross-origin iframe.
 * Keyboard users reach the embed via skip / continue links instead.
 *
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_a11y_youtube_iframe_data(array $data): array
{
    if (($data['modifier'] ?? '') !== 'video') {
        return $data;
    }

    $src = $data['attributeList']['src'] ?? $data['src'] ?? '';

    if (!is_string($src) || !preg_match('#(?:youtube\.com|youtu\.be)#i', $src)) {
        return $data;
    }

    $data['attributeList']['tabindex'] = '-1';

    if (!isset($data['classList']) || !is_array($data['classList'])) {
        $data['classList'] = [];
    }

    $data['classList'][] = 'sater-a11y-youtube-embed';

    return $data;
}

function sater_a11y_should_enqueue_archive_datepicker(): bool
{
    if (is_admin()) {
        return false;
    }

    return is_archive() || is_author();
}

function sater_a11y_enqueue_archive_datepicker(): void
{
    if (!sater_a11y_should_enqueue_archive_datepicker()) {
        return;
    }

    $duetBase = plugin_dir_url(__FILE__) . 'assets/vendor/duet/dist/duet/';
    $duetPath = __DIR__ . '/assets/vendor/duet/dist/duet/duet.esm.js';

    if (is_readable($duetPath)) {
        wp_register_script_module(
            'duet-date-picker',
            $duetBase . 'duet.esm.js',
            [],
            '1.4.0'
        );
        wp_enqueue_script_module('duet-date-picker');

        wp_enqueue_style(
            'duet-date-picker',
            $duetBase . 'themes/default.css',
            [],
            '1.4.0'
        );
    }

    $cssPath = __DIR__ . '/assets/css/archive-datepicker.css';
    if (is_readable($cssPath)) {
        wp_enqueue_style(
            'sater-a11y-archive-datepicker',
            plugin_dir_url(__FILE__) . 'assets/css/archive-datepicker.css',
            ['duet-date-picker', 'styleguide-css', 'municipio-css'],
            (string) filemtime($cssPath)
        );
    }

    $jsPath = __DIR__ . '/assets/js/archive-datepicker.js';
    if (is_readable($jsPath)) {
        wp_enqueue_script(
            'sater-a11y-archive-datepicker',
            plugin_dir_url(__FILE__) . 'assets/js/archive-datepicker.js',
            [],
            (string) filemtime($jsPath),
            true
        );
    }
}

function sater_a11y_enqueue_assets(): void
{
    sater_a11y_enqueue_archive_datepicker();
    $cssPath = __DIR__ . '/assets/css/mobile-header.css';
    $jsPath  = __DIR__ . '/assets/js/header-scroll.js';

    if (file_exists($cssPath)) {
        wp_enqueue_style(
            'sater-a11y-mobile-header',
            plugin_dir_url(__FILE__) . 'assets/css/mobile-header.css',
            ['styleguide-css', 'municipio-css'],
            (string) filemtime($cssPath)
        );
    }

    $heroCssPath = __DIR__ . '/assets/css/hero.css';
    if (!is_admin() && is_readable($heroCssPath)) {
        wp_enqueue_style(
            'sater-a11y-hero',
            plugin_dir_url(__FILE__) . 'assets/css/hero.css',
            ['styleguide-css', 'municipio-css'],
            (string) filemtime($heroCssPath)
        );
    }

    if (!is_admin() && sater_a11y_is_etjanster_page()) {
        $etjansterCardsCss = __DIR__ . '/assets/css/etjanster-cards.css';
        if (is_readable($etjansterCardsCss)) {
            wp_enqueue_style(
                'sater-a11y-etjanster-cards',
                plugin_dir_url(__FILE__) . 'assets/css/etjanster-cards.css',
                ['styleguide-css', 'municipio-css'],
                (string) filemtime($etjansterCardsCss)
            );
        }
    }

    if (!is_admin()) {
        $inlaylistCss = __DIR__ . '/assets/css/inlaylist.css';
        if (is_readable($inlaylistCss)) {
            wp_enqueue_style(
                'sater-a11y-inlaylist',
                plugin_dir_url(__FILE__) . 'assets/css/inlaylist.css',
                ['styleguide-css', 'municipio-css'],
                (string) filemtime($inlaylistCss)
            );
        }

        $printCss = __DIR__ . '/assets/css/print.css';
        if (is_readable($printCss)) {
            wp_enqueue_style(
                'sater-a11y-print',
                plugin_dir_url(__FILE__) . 'assets/css/print.css',
                ['styleguide-css', 'municipio-css'],
                (string) filemtime($printCss)
            );
        }
    }

    // Only enqueue the scroll script when the header is configured as sticky.
    if (get_theme_mod('header_sticky') === 'sticky' && file_exists($jsPath)) {
        wp_enqueue_script(
            'sater-a11y-header-scroll',
            plugin_dir_url(__FILE__) . 'assets/js/header-scroll.js',
            [],
            (string) filemtime($jsPath),
            true
        );
    }

    if (is_admin()) {
        return;
    }

    $youtubeCssPath = __DIR__ . '/assets/css/youtube-embed-focus.css';
    if (is_readable($youtubeCssPath)) {
        wp_enqueue_style(
            'sater-a11y-youtube-embed-focus',
            plugin_dir_url(__FILE__) . 'assets/css/youtube-embed-focus.css',
            ['styleguide-css', 'municipio-css'],
            (string) filemtime($youtubeCssPath)
        );
    }

    $youtubeJsPath = __DIR__ . '/assets/js/youtube-embed-focus.js';
    if (is_readable($youtubeJsPath)) {
        wp_enqueue_script(
            'sater-a11y-youtube-embed-focus',
            plugin_dir_url(__FILE__) . 'assets/js/youtube-embed-focus.js',
            [],
            (string) filemtime($youtubeJsPath),
            true
        );
    }

    $tableCssPath = __DIR__ . '/assets/css/table-collapse-button.css';
    if (is_readable($tableCssPath)) {
        wp_enqueue_style(
            'sater-a11y-table-collapse-button',
            plugin_dir_url(__FILE__) . 'assets/css/table-collapse-button.css',
            ['styleguide-css', 'municipio-css'],
            (string) filemtime($tableCssPath)
        );
    }

    $tableColumnsCssPath = __DIR__ . '/assets/css/table-columns.css';
    if (is_readable($tableColumnsCssPath)) {
        wp_enqueue_style(
            'sater-a11y-table-columns',
            plugin_dir_url(__FILE__) . 'assets/css/table-columns.css',
            ['styleguide-css', 'municipio-css'],
            (string) filemtime($tableColumnsCssPath)
        );
    }

    $tableJsPath = __DIR__ . '/assets/js/table-collapse-button.js';
    if (is_readable($tableJsPath)) {
        wp_enqueue_script(
            'sater-a11y-table-collapse-button',
            plugin_dir_url(__FILE__) . 'assets/js/table-collapse-button.js',
            [],
            (string) filemtime($tableJsPath),
            true
        );
    }

    $galleryCssPath = __DIR__ . '/assets/css/gallery-keyboard.css';
    if (is_readable($galleryCssPath)) {
        wp_enqueue_style(
            'sater-a11y-gallery-keyboard',
            plugin_dir_url(__FILE__) . 'assets/css/gallery-keyboard.css',
            ['styleguide-css', 'municipio-css'],
            (string) filemtime($galleryCssPath)
        );
    }

    $galleryJsPath = __DIR__ . '/assets/js/gallery-keyboard.js';
    if (is_readable($galleryJsPath)) {
        wp_enqueue_script(
            'sater-a11y-gallery-keyboard',
            plugin_dir_url(__FILE__) . 'assets/js/gallery-keyboard.js',
            [],
            (string) filemtime($galleryJsPath),
            true
        );
    }

    $navKeyboardJsPath = __DIR__ . '/assets/js/nav-keyboard.js';
    if (is_readable($navKeyboardJsPath)) {
        wp_enqueue_script(
            'sater-a11y-nav-keyboard',
            plugin_dir_url(__FILE__) . 'assets/js/nav-keyboard.js',
            [],
            (string) filemtime($navKeyboardJsPath),
            true
        );
    }

    $navKeyboardCssPath = __DIR__ . '/assets/css/nav-keyboard.css';
    if (is_readable($navKeyboardCssPath)) {
        wp_enqueue_style(
            'sater-a11y-nav-keyboard',
            plugin_dir_url(__FILE__) . 'assets/css/nav-keyboard.css',
            ['styleguide-css', 'municipio-css'],
            (string) filemtime($navKeyboardCssPath)
        );
    }

    $contactBannerFooterCssPath = __DIR__ . '/assets/css/contact-banner-footer.css';
    if (is_readable($contactBannerFooterCssPath)) {
        wp_enqueue_style(
            'sater-a11y-contact-banner-footer',
            plugin_dir_url(__FILE__) . 'assets/css/contact-banner-footer.css',
            ['styleguide-css', 'municipio-css'],
            (string) filemtime($contactBannerFooterCssPath)
        );
    }
}

/**
 * Start output buffering so sater_a11y_swap_loading_attr() can post-process
 * the fully rendered HTML. Only fires on front-end page requests.
 *
 * @return void
 */
function sater_a11y_ob_start(): void
{
    if (is_admin() || wp_doing_ajax() || wp_is_json_request()) {
        return;
    }
    ob_start('sater_a11y_swap_loading_attr');
}

/**
 * Output-buffer callback: swap loading="lazy" -> loading="eager" on any <img>
 * that carries fetchpriority="high" (set by sater_a11y_fix_responsive_images
 * for full-bleed/cover images).
 *
 * The component-library template hardcodes loading="lazy" before the compiled
 * imgAttributes string, so the only way to override it is post-processing.
 * The fetchpriority attribute acts as a safe, self-contained marker so that
 * only our targeted images are affected.
 *
 * @param string $html Full page HTML.
 *
 * @return string
 */
function sater_a11y_swap_loading_attr(string $html): string
{
    return preg_replace_callback(
        '/<img\b[^>]*>/is',
        function (array $matches): string {
            $tag = $matches[0];
            if (strpos($tag, 'fetchpriority="high"') === false
                || strpos($tag, 'loading="lazy"') === false
            ) {
                return $tag;
            }
            return str_replace('loading="lazy"', 'loading="eager"', $tag);
        },
        $html
    ) ?? $html;
}

/**
 * Whether the image is rendered by the Modularity Bild (Image) module.
 *
 * @param array $data Component data array (pre-init).
 *
 * @return bool
 */
function sater_a11y_is_bild_module_image(array $data): bool
{
    $context = $data['context'] ?? [];
    if (is_string($context)) {
        return $context === 'module.image';
    }
    return is_array($context) && in_array('module.image', $context, true);
}

/**
 * Replace the component-library's container-query image strategy with a standard
 * srcset + sizes approach.
 *
 * Municipio's component-library normally outputs one <img> per breakpoint and uses
 * CSS @container rules to show only the matching variant. While accurate for
 * container-sized cards, browsers may download multiple image files before CSS
 * applies (display:none does not reliably suppress network requests).
 *
 * This filter fires before Image::init() runs, allowing us to replace the
 * ImageInterface src with a plain URL string. That causes init() to skip
 * handleImageProcessing() entirely (the container-query path), so:
 *  - only a single <img> is rendered (standard mode)
 *  - no c-image--container-query class is added
 *  - no per-image inline <style> block is output
 *
 * Data that handleImageProcessing() would have set is manually carried over:
 * srcset, sizes, alt text, object-position (focus point), aspect-ratio (CLS
 * prevention), and the LQIP background placeholder.
 *
 * @param array $data Component data array (pre-init).
 *
 * @return array
 */
function sater_a11y_fix_responsive_images(array $data): array
{
    $interface = \ComponentLibrary\Integrations\Image\ImageInterface::class;
    if (!($data['src'] instanceof $interface)) {
        return $data;
    }

    $src = $data['src'];
    $srcset = $src->getSrcSet();

    // Bild module images are used as page heroes site-wide. cover: true activates
    // Municipio's .c-image--cover layout so the image fills its container.
    if (sater_a11y_is_bild_module_image($data)) {
        $data['cover'] = true;
    }

    // Only intercept when srcset would be meaningful (medium/large images).
    // Small images return null from getSrcSet() and are left unchanged.
    if (!$srcset) {
        return $data;
    }

    // Carry over alt text before we lose the ImageInterface reference.
    $isDecorativeCard = ($data['attributeList']['data-decorative-card-image'] ?? '') === 'true';
    if (!$isDecorativeCard && empty($data['alt'])) {
        $data['alt'] = $src->getAltText() ?? '';
    }

    // Focus point as CSS object-position keeps the subject centred when the
    // image is cropped via object-fit: cover.
    $focusPoint = $src->getFocusPoint();
    $focusStyle = sprintf(
        'object-position: %s%% %s%%;',
        $focusPoint['left'],
        $focusPoint['top']
    );

    // Full-bleed images (hero, cover) fill 100vw; card/segment images sit in a
    // multi-column grid so a smaller hint avoids over-fetching.
    $isFullBleed = !empty($data['cover']) || !empty($data['fullWidth']);
    $sizes       = $isFullBleed
        ? '100vw'
        : '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw';

    // Full-bleed images are likely LCP candidates. fetchpriority="high" tells
    // the browser to prioritise the request ahead of other resources.
    // Only the first hero image per page gets high priority.
    // sater_a11y_ob_start() uses this as a marker to also flip loading="lazy"
    // (hardcoded in the component-library template) to loading="eager".
    static $lcpPriorityGiven = false;
    if ($isFullBleed && !$lcpPriorityGiven) {
        $data['imgAttributeList']['fetchpriority'] = 'high';
        $lcpPriorityGiven = true;
    }

    // Merge into imgAttributeList - picked up by buildAttributes() in init().
    $data['imgAttributeList']['srcset'] = $srcset;
    $data['imgAttributeList']['sizes']  = $sizes;
    $data['imgAttributeList']['style']
        = ($data['imgAttributeList']['style'] ?? '') . $focusStyle;

    // Preserve aspect ratio on the wrapper to prevent Cumulative Layout Shift.
    $noCover   = !($data['cover'] ?? false);
    $calcRatio = ($data['calculateAspectRatio'] ?? true);
    if ($noCover && $calcRatio) {
        foreach ($src->getContainerQueryData() as $item) {
            if (!empty($item['aspectRatio'])) {
                if (!isset($data['wrapperAttributes'])) {
                    $data['wrapperAttributes'] = [];
                }
                $data['wrapperAttributes']['style']
                    = ($data['wrapperAttributes']['style'] ?? '')
                    . 'aspect-ratio:' . $item['aspectRatio'] . ';';
                break;
            }
        }
    }

    // Preserve the LQIP background placeholder on the wrapper so the blurry
    // preview continues to show while the full image loads.
    $lqipUrl = $src->getLqipUrl();
    if ($lqipUrl && ($data['lqipEnabled'] ?? true)) {
        if (!isset($data['wrapperAttributes'])) {
            $data['wrapperAttributes'] = [];
        }
        $lqipBg = sprintf(
            'background-image: url(%s); background-size: cover;'
            . ' background-position: %s%% %s%%;',
            $lqipUrl,
            $focusPoint['left'],
            $focusPoint['top']
        );
        $data['wrapperAttributes']['style']
            = ($data['wrapperAttributes']['style'] ?? '') . $lqipBg;
    }

    // Replace ImageInterface with a plain URL string.
    // init() will take the else-branch (containerQueryData = null), skip
    // handleImageProcessing(), and render the standard single-<img> template.
    $data['src'] = $src->getUrl();

    return $data;
}
