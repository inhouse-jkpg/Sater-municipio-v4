<?php

/**
 * IncludeModuleId module implementation.
 *
 * @category   WordPress
 * @package    Sater
 * @author     Municipio SE <dev@municipio.se>
 * @license    MIT https://opensource.org/licenses/MIT
 * @link       https://sater.se
 * @since      1.0.0
 * @phpVersion 8.0
 */

namespace Modularity\Module\IncludeModuleId;

use WP_Post;

/**
 * Modularity module that includes another module by ID.
 *
 * @category WordPress
 * @package  Sater
 * @author   Municipio SE <dev@municipio.se>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://sater.se
 */
class IncludeModuleId extends \Modularity\Module
{
    public $slug = 'includemoduleid';
    public $supports = [];

    /**
     * Module initialization.
     *
     * @return void
     */
    public function init()
    {
        $this->nameSingular = __('Include module (ID)', 'sater');
        $this->namePlural = __('Include modules (ID)', 'sater');
        $this->description = __('Renders a Modularity module by pasting its ID.', 'sater');
    }

    /**
     * Module view data.
     *
     * @return array
     */
    public function data(): array
    {
        $targetId = $this->_resolveTargetModuleId();

        $markup = '';
        if (function_exists('Sater_MI_Render_Modularity_Module_safe')) {
            $currentId = is_numeric($this->ID) ? (int) $this->ID : 0;
            $markup = Sater_MI_Render_Modularity_Module_safe($targetId, $currentId);
        } else {
            $markup = $this->_renderIncludedModuleFallback($targetId);
        }

        return [
            'targetId' => $targetId,
            'includedMarkup' => $markup,
        ];
    }

    /**
     * Same idea as Manual Input: ACF keys may appear as snake_case or camelCase depending on context.
     *
     * @return int
     */
    private function _resolveTargetModuleId(): int
    {
        $fields = $this->getFields() ?? [];
        foreach (['module_id', 'moduleId'] as $key) {
            if (isset($fields[$key]) && is_numeric($fields[$key])) {
                return (int) $fields[$key];
            }
        }

        $raw = get_post_meta((int) $this->ID, 'module_id', true);
        if (is_numeric($raw)) {
            return (int) $raw;
        }

        return 0;
    }

    /**
     * Kept in sync with Sater_MI_Render_Modularity_Module_safe when the Manual Input mu-plugin is disabled.
     *
     * @param int $targetId The referenced module ID.
     *
     * @return string
     */
    private function _renderIncludedModuleFallback(int $targetId): string
    {
        static $stack = [];

        if ($targetId <= 0) {
            return '';
        }

        $currentId = is_numeric($this->ID) ? (int) $this->ID : 0;
        if ($currentId > 0 && $targetId === $currentId) {
            return '';
        }

        if (in_array($targetId, $stack, true)) {
            return '';
        }
        if (count($stack) >= 1) {
            return '';
        }

        $post = get_post($targetId);
        if (!$post instanceof WP_Post) {
            return '';
        }

        if (strpos((string) $post->post_type, 'mod-') !== 0) {
            return '';
        }

        $stack[] = $targetId;
        $html = (string) do_shortcode('[modularity id="' . $targetId . '"]');
        array_pop($stack);

        $html = trim($html);
        if ($html === '') {
            return '';
        }

        return '<div class="sater-mi-included-module">' . $html . '</div>';
    }
}

