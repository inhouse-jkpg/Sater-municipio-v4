<?php
/**
 * Plugin Name: Säter Manual Input: Accordion FAQ fields
 * Description: Shows link, image, and icon on Manuell inmatning accordion rows (Posts-style FAQ) and renders them on the frontend.
 * Version: 1.3.1
 * Author: Municipio SE
 * License: MIT
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SATER_MIAF_REPEATER_KEY = 'field_64ff22b2d91b7';
const SATER_MIAF_DISPLAY_AS_CONDITIONAL_KEY = 'field_6752f959acfda';
const SATER_MIAF_LINK_FIELD_KEY = 'field_64ff232ad91ba';
const SATER_MIAF_IMAGE_FIELD_KEY = 'field_64ff2355d91bb';
const SATER_MIAF_BOX_ICON_FIELD_KEY = 'field_65293de2a26c7';
const SATER_MIAF_VIEWS_DIR = __DIR__ . '/views';

/**
 * Admin: patch Modularity Manual Input repeater sub fields for accordion FAQ use.
 */
if (is_admin()) {
    add_filter('acf/load_field', 'sater_manualinput_accordion_patch_repeater_sub_fields', 20, 1);
    add_filter('acf/load_field/name=box_icon', 'sater_manualinput_accordion_load_box_icon_choices', 10, 1);
}

/**
 * Frontend: Posts expandable-list view + styling.
 */
add_filter('/Modularity/externalViewPath', 'sater_manualinput_accordion_external_view_paths', 10, 1);
add_filter('Modularity/Display/mod-manualinput/viewData', 'sater_manualinput_accordion_flag_posts_style', 5, 1);
add_filter('Modularity/Display/mod-manualinput/viewData', 'sater_manualinput_accordion_filter_view_data', 30, 1);
add_filter('ComponentLibrary/Component/Card/Modifier', 'sater_manualinput_accordion_card_modifier', 20, 2);
add_filter('Modularity/Display/mod-manualinput/Markup', 'sater_manualinput_accordion_inject_styles', 10, 2);
add_filter('ComponentLibrary/Component/Accordion/Class', 'sater_manualinput_accordion_accordion_class', 20, 2);
add_action('wp_enqueue_scripts', 'sater_manualinput_accordion_enqueue_styles', 999);

/**
 * Frontend styles for red accordion headers.
 */
function sater_manualinput_accordion_enqueue_styles(): void
{
    if (is_admin()) {
        return;
    }

    $accordionCss = __DIR__ . '/assets/accordion.css';
    if (is_readable($accordionCss)) {
        wp_enqueue_style(
            'sater-miaf-accordion',
            plugin_dir_url(__FILE__) . 'assets/accordion.css',
            [],
            (string) filemtime($accordionCss)
        );
    }

    $cardsCss = __DIR__ . '/assets/cards.css';
    if (is_readable($cardsCss)) {
        wp_enqueue_style(
            'sater-miaf-cards',
            plugin_dir_url(__FILE__) . 'assets/cards.css',
            ['styleguide-css', 'municipio-css'],
            (string) filemtime($cardsCss)
        );
    }
}

/**
 * @param array<string, string|array<int, string>> $paths
 * @return array<string, string|array<int, string>>
 */
function sater_manualinput_accordion_external_view_paths(array $paths): array
{
    if (!defined('MODULARITY_PATH')) {
        return $paths;
    }

    $paths['mod-manualinput'] = [
        SATER_MIAF_VIEWS_DIR,
        MODULARITY_PATH . 'source/php/Module/Posts/views',
        MODULARITY_PATH . 'source/php/Module/ManualInput/views',
    ];

    return $paths;
}

/**
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_manualinput_accordion_flag_posts_style(array $data): array
{
    $moduleId = isset($data['ID']) && is_numeric($data['ID']) ? (int) $data['ID'] : 0;

    if ($moduleId > 0 && sater_manualinput_accordion_module_uses_accordion($moduleId)) {
        $GLOBALS['sater_miaf_posts_accordion'] = true;
    }

    return $data;
}

/**
 * @param array<int, string> $modifiers
 * @param array<int, string> $contexts
 * @return array<int, string>
 */
function sater_manualinput_accordion_card_modifier(array $modifiers, array $contexts): array
{
    if (empty($GLOBALS['sater_miaf_posts_accordion'])) {
        return $modifiers;
    }

    $allowedContexts = ['module.posts.expandablelist', 'module.manual-input.accordion'];
    if (array_intersect($allowedContexts, $contexts) === []) {
        return $modifiers;
    }

    $themeMod = (string) get_theme_mod('mod_posts_expandablelist_modifier', 'none');
    if (class_exists('\Kirki')) {
        $value = \Kirki::get_option('mod_posts_expandablelist_modifier');
        if (is_string($value) && $value !== '') {
            $themeMod = $value;
        }
    }

    if ($themeMod !== 'none' && !in_array($themeMod, $modifiers, true)) {
        $modifiers[] = $themeMod;
    }

    if (!in_array('panel', $modifiers, true)) {
        $modifiers[] = 'panel';
    }

    return $modifiers;
}

/**
 * @param array<int, string> $class
 * @param array<int, string> $context
 * @return array<int, string>
 */
function sater_manualinput_accordion_accordion_class(array $class, array $context): array
{
    if (empty($GLOBALS['sater_miaf_posts_accordion'])) {
        return $class;
    }

    if (!in_array('sater-miaf-accordion', $class, true)) {
        $class[] = 'sater-miaf-accordion';
    }

    return $class;
}

/**
 * @param mixed $markup
 * @param object $module
 * @return mixed
 */
function sater_manualinput_accordion_inject_styles($markup, $module)
{
    if (!empty($GLOBALS['sater_miaf_posts_accordion']) && is_string($markup)) {
        $cssPath = __DIR__ . '/assets/accordion.css';
        if (is_readable($cssPath)) {
            $css = (string) file_get_contents($cssPath);
            $markup = '<style id="sater-miaf-accordion-css">' . $css . '</style>' . $markup;
        }
    }

    unset($GLOBALS['sater_miaf_posts_accordion']);

    return $markup;
}

/**
 * @param array<string, mixed> $field
 * @return array<string, mixed>
 */
function sater_manualinput_accordion_patch_repeater_sub_fields(array $field): array
{
    if (($field['parent'] ?? '') !== SATER_MIAF_REPEATER_KEY) {
        return $field;
    }

    if (($field['key'] ?? '') === SATER_MIAF_LINK_FIELD_KEY || ($field['name'] ?? '') === 'link') {
        // Upstream hides Link when Accordion; allow all layouts (label matches Posts manual input).
        $field['label'] = __('Permalink', 'modularity');
        $field['conditional_logic'] = 0;
        return $field;
    }

    if (($field['key'] ?? '') === SATER_MIAF_IMAGE_FIELD_KEY || ($field['name'] ?? '') === 'image') {
        // Upstream hides Image for List and Accordion; allow Accordion, keep hidden for List only.
        $field['conditional_logic'] = [
            [
                [
                    'field' => SATER_MIAF_DISPLAY_AS_CONDITIONAL_KEY,
                    'operator' => '!=',
                    'value' => 'list',
                ],
            ],
        ];
        return $field;
    }

    if (($field['key'] ?? '') === SATER_MIAF_BOX_ICON_FIELD_KEY || ($field['name'] ?? '') === 'box_icon') {
        // Upstream: icon on box/list/card/block only — add Accordion (OR group).
        $field['conditional_logic'] = [
            [
                [
                    'field' => SATER_MIAF_DISPLAY_AS_CONDITIONAL_KEY,
                    'operator' => '==',
                    'value' => 'box',
                ],
            ],
            [
                [
                    'field' => SATER_MIAF_DISPLAY_AS_CONDITIONAL_KEY,
                    'operator' => '==',
                    'value' => 'list',
                ],
            ],
            [
                [
                    'field' => SATER_MIAF_DISPLAY_AS_CONDITIONAL_KEY,
                    'operator' => '==',
                    'value' => 'card',
                ],
            ],
            [
                [
                    'field' => SATER_MIAF_DISPLAY_AS_CONDITIONAL_KEY,
                    'operator' => '==',
                    'value' => 'block',
                ],
            ],
            [
                [
                    'field' => SATER_MIAF_DISPLAY_AS_CONDITIONAL_KEY,
                    'operator' => '==',
                    'value' => 'accordion',
                ],
            ],
        ];
        return $field;
    }

    return $field;
}

/**
 * Cached icon choices (do not register box_icon with Municipio PrefillIconChoice).
 *
 * @param array<string, mixed> $field
 * @return array<string, mixed>
 */
function sater_manualinput_accordion_load_box_icon_choices(array $field): array
{
    static $choices = null;

    if ($choices !== null) {
        $field['choices'] = $choices;
        return $field;
    }

    $built = ['' => __('None', 'municipio')];

    if (class_exists('\Municipio\Helper\Icons')) {
        $materialIcons = \Municipio\Helper\Icons::getIcons();
        if (is_array($materialIcons)) {
            foreach ($materialIcons as $icon) {
                $icon = (string) $icon;
                $built[$icon] =
                    '<i class="material-symbols material-symbols-rounded material-symbols-sharp material-symbols-outlined" style="float:left;">' .
                    esc_html($icon) . '</i>' .
                    '<span style="height:24px;display:inline-block;line-height:24px;margin-left:8px;">' .
                    esc_html(str_replace('_', ' ', $icon)) . '</span>';
            }
        }
    }

    if (
        class_exists('\ComponentLibrary\Helper\Icons') &&
        class_exists('\ComponentLibrary\Cache\WpCache')
    ) {
        $customIcons = (new \ComponentLibrary\Helper\Icons(new \ComponentLibrary\Cache\WpCache()))->getIcons();
        if (is_array($customIcons)) {
            foreach ($customIcons as $key => $customIcon) {
                if (str_contains((string) $key, 'Filled')) {
                    continue;
                }
                $built[(string) $key] =
                    '<span class="material-symbols material-symbols-rounded material-symbols-sharp material-symbols-outlined" style="float:left;">' .
                    esc_html((string) $customIcon) . '</span>' .
                    '<span style="height:24px;display:inline-block;line-height:24px;margin-left:8px;">' .
                    esc_html(str_replace('_', ' ', (string) $key)) . '</span>';
            }
        }
    }

    $choices = $built;
    $field['choices'] = $choices;

    return $field;
}

/**
 * @param int $moduleId
 */
function sater_manualinput_accordion_module_uses_accordion(int $moduleId): bool
{
    if ($moduleId < 1) {
        return false;
    }

    $displayAs = (string) get_post_meta($moduleId, 'display_as', true);
    if ($displayAs === 'accordion') {
        return true;
    }

    $conditional = (string) get_post_meta($moduleId, 'display_as_conditional', true);

    return $conditional === 'accordion';
}

/**
 * @param array<string, mixed>|object $image
 */
function sater_manualinput_accordion_image_markup($image): string
{
    if (is_object($image) && method_exists($image, 'getUrl')) {
        $url = (string) $image->getUrl();
        if ($url === '') {
            return '';
        }
        return '<figure class="accordion-item__image"><img src="' . esc_url($url) . '" alt="" loading="lazy" /></figure>';
    }

    if (!is_array($image) || empty($image['src'])) {
        return '';
    }

    $alt = isset($image['alt']) ? (string) $image['alt'] : '';

    return '<figure class="accordion-item__image"><img src="' . esc_url((string) $image['src']) . '" alt="' .
        esc_attr($alt) . '" loading="lazy" /></figure>';
}

/**
 * Build accordion rows in the same shape as Posts expandable-list (prepareAccordion).
 *
 * @param array<int, array<string, mixed>> $inputs
 * @return array<int, array<string, mixed>>
 */
function sater_manualinput_accordion_build_prepare_accordion(array $inputs, int $moduleId): array
{
    $accordion = [];

    foreach ($inputs as $index => $input) {
        if (!is_array($input)) {
            continue;
        }

        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            continue;
        }

        $columnValues = $input['accordionColumnValues'] ?? [];
        if (!is_array($columnValues)) {
            $columnValues = [];
        }

        $extraColumns = [];
        if ($columnValues !== []) {
            $first = (string) ($columnValues[0] ?? '');
            if ($first === $title) {
                $extraColumns = array_slice($columnValues, 1);
            } elseif (count($columnValues) > 1) {
                $extraColumns = $columnValues;
            }
        }

        $item = [
            'heading' => $title,
            'content' => (string) ($input['content'] ?? ''),
            'classList' => ['sater-miaf-accordion__section'],
            'attributeList' => ['data-js-item-id' => 'manual-' . $moduleId . '-' . $index],
        ];

        if ($extraColumns !== []) {
            $item['column_values'] = array_map(
                static fn ($value): string => is_scalar($value) ? (string) $value : '',
                $extraColumns
            );
        }

        $accordion[] = $item;
    }

    return $accordion;
}

/**
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_manualinput_accordion_filter_view_data(array $data): array
{
    $moduleId = isset($data['ID']) && is_numeric($data['ID']) ? (int) $data['ID'] : 0;

    if ($moduleId < 1 || !sater_manualinput_accordion_module_uses_accordion($moduleId)) {
        return $data;
    }

    if (empty($data['manualInputs']) || !is_array($data['manualInputs'])) {
        $data['prepareAccordion'] = [];
        return $data;
    }

    foreach ($data['manualInputs'] as $idx => $input) {
        if (!is_array($input)) {
            continue;
        }

        $input['attributeList'] = array_merge(
            is_array($input['attributeList'] ?? null) ? $input['attributeList'] : [],
            ['data-js-item-id' => 'manual-' . $moduleId . '-' . $idx]
        );
        $input['classList'] = array_merge(
            is_array($input['classList'] ?? null) ? $input['classList'] : [],
            ['sater-miaf-accordion__section']
        );

        $content = (string) ($input['content'] ?? '');
        $parts = [];

        $icon = (string) ($input['icon'] ?? $input['boxIcon'] ?? '');
        if ($icon !== '') {
            $parts[] =
                '<span class="material-symbols material-symbols-rounded material-symbols-outlined accordion-item__icon" aria-hidden="true">' .
                esc_html($icon) . '</span>';
        }

        $imageMarkup = sater_manualinput_accordion_image_markup($input['image'] ?? null);
        if ($imageMarkup !== '') {
            $parts[] = $imageMarkup;
        }

        if ($content !== '') {
            $parts[] = $content;
        }

        $link = (string) ($input['link'] ?? '');
        if ($link !== '') {
            $parts[] = '<p class="accordion-item__permalink"><a href="' . esc_url($link) . '">' .
                esc_html($link) . '</a></p>';
        }

        if ($parts !== []) {
            $input['content'] = implode("\n", $parts);
            $data['manualInputs'][$idx] = $input;
        }
    }

    $data['allow_freetext_filtering'] = !empty($data['freeTextFiltering']);
    $data['prepareAccordion'] = sater_manualinput_accordion_build_prepare_accordion($data['manualInputs'], $moduleId);

    return $data;
}
