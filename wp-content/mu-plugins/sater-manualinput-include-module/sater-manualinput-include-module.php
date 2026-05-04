<?php
/**
 * Plugin Name: Säter Modularity Manual Input: Include module by ID
 * Description: Adds an "Included module ID" field to Manual Input items and renders the referenced module safely.
 * Version: 1.0.0
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
 * ACF field keys used by Modularity Manual Input.
 *
 * Source: wp-content/plugins/modularity/source/php/AcfFields/php/mod-manual-input.php
 */
const SATER_MI_REPEATER_FIELD_KEY = 'field_64ff22b2d91b7'; // manual_inputs repeater

/**
 * Add a "Included module ID" sub field to Manual Input repeater items.
 *
 * We add this as a local field so we don't have to modify the Modularity package.
 */
add_action(
    'acf/init',
    static function (): void {
        if (!function_exists('acf_add_local_field')) {
            return;
        }

        // Avoid duplicate field registration (in case of multiple loads).
        if (function_exists('acf_get_field')
            && acf_get_field('field_sater_manualinput_included_module_id')
        ) {
            return;
        }

        acf_add_local_field(
            [
                'key' => 'field_sater_manualinput_included_module_id',
                'label' => __('Included module ID', 'sater'),
                'name' => 'included_module_id',
                'type' => 'number',
                'instructions' => __(
                    'Optional. Paste a Modularity module post ID (post type starts with "mod-") to render inside this item.',
                    'sater'
                ),
                'required' => 0,
                'parent' => SATER_MI_REPEATER_FIELD_KEY,
                'min' => 1,
                'step' => 1,
                'wrapper' => [
                    'width' => '25',
                ],
            ]
        );
    }
);

/**
 * Render a Modularity module by ID with a recursion guard.
 *
 * - Only allows post types starting with "mod-"
 * - Prevents self-include
 * - Prevents recursion and deep nesting (max depth 1)
 *
 * @param int $moduleId        The referenced module post ID.
 * @param int $currentModuleId The current Manual Input module ID.
 *
 * @return string
 */
function Sater_MI_Render_Modularity_Module_safe(int $moduleId, int $currentModuleId = 0): string
{
    static $stack = [];

    if ($moduleId <= 0) {
        return '';
    }

    // Prevent self include (e.g. a module including itself).
    if ($currentModuleId > 0 && $moduleId === $currentModuleId) {
        return '';
    }

    // Prevent recursion and deep nesting.
    if (in_array($moduleId, $stack, true)) {
        return '';
    }

    if (count($stack) >= 1) {
        return '';
    }

    $post = get_post($moduleId);
    if (!$post instanceof WP_Post) {
        return '';
    }

    if (strpos((string) $post->post_type, 'mod-') !== 0) {
        return '';
    }

    $stack[] = $moduleId;
    $html = (string) do_shortcode('[modularity id="' . $moduleId . '"]');
    array_pop($stack);

    $html = trim($html);
    if ($html === '') {
        return '';
    }

    return '<div class="sater-mi-included-module">' . $html . '</div>';
}

/**
 * Inject included module markup into each Manual Input item's content.
 *
 * This avoids modifying Modularity templates. The Manual Input views already
 * output `$input['content']`, so we append rendered markup directly.
 */
add_filter(
    'Modularity/Display/mod-manualinput/viewData',
    static function (array $data): array {
        if (empty($data['manualInputs']) || !is_array($data['manualInputs'])) {
            return $data;
        }

        $currentModuleId = 0;
        if (isset($data['ID']) && is_numeric($data['ID'])) {
            $currentModuleId = (int) $data['ID'];
        }

        foreach ($data['manualInputs'] as $idx => $input) {
            if (!is_array($input)) {
                continue;
            }

            // After Modularity camelCase conversion, included_module_id becomes includedModuleId.
            $includedId = 0;
            if (isset($input['includedModuleId']) && is_numeric($input['includedModuleId'])) {
                $includedId = (int) $input['includedModuleId'];
            } elseif (isset($input['included_module_id'])
                && is_numeric($input['included_module_id'])
            ) {
                $includedId = (int) $input['included_module_id'];
            }

            if ($includedId <= 0) {
                continue;
            }

            $markup = Sater_MI_Render_Modularity_Module_safe($includedId, $currentModuleId);
            if ($markup === '') {
                continue;
            }

            $content = (string) ($input['content'] ?? '');
            $separator = $content !== '' ? "\n" : '';

            $input['content'] = $content . $separator . $markup;
            $data['manualInputs'][$idx] = $input;
        }

        return $data;
    },
    20
);

