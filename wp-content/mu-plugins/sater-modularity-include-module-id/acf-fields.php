<?php

declare(strict_types=1);

/**
 * ACF fields for the "Include module (ID)" module.
 *
 * @category   WordPress
 * @package    Sater
 * @author     Municipio SE <dev@municipio.se>
 * @license    MIT https://opensource.org/licenses/MIT
 * @link       https://sater.se
 * @since      1.0.0
 * @phpVersion 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action(
    'acf/init',
    static function (): void {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(
            [
                'key' => 'group_sater_modularity_include_module_id',
                'title' => __('Include module (ID)', 'sater'),
                'fields' => [
                    [
                        'key' => 'field_sater_modularity_include_module_id_target',
                        'label' => __('Module ID', 'sater'),
                        'name' => 'module_id',
                        'type' => 'number',
                        'instructions' => __('Paste a Modularity module post ID (type mod-*).', 'sater'),
                        'required' => 0,
                        'min' => 1,
                        'step' => 1,
                    ],
                ],
                'location' => [
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            // Truncated form (ModuleManager::prefixSlug max 20 chars).
                            'value' => 'mod-includemodulei',
                        ],
                    ],
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            // Full form (may exist on older or migrated data).
                            'value' => 'mod-includemoduleid',
                        ],
                    ],
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/includemoduleid',
                        ],
                    ],
                ],
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
                'show_in_rest' => 0,
            ]
        );
    }
);

