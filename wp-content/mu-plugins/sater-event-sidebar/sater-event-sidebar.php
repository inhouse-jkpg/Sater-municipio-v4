<?php
/**
 * Plugin Name: Säter Event Sidebar
 * Description: Renders Evenemang ACF fields (start_datum, slut_datum, plats, pris, arrangor) as right-sidebar cards on single event pages. Survives Composer theme installs by overriding Municipio views and injecting view data.
 * Version: 1.0.0
 * Author: Säter kommun
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Sater_Event_Sidebar
{
    private const POST_TYPE = 'events';

    public function __construct()
    {
        // Must use Municipio/viewPaths (same pattern as sater-events-filtering).
        add_filter('Municipio/viewPaths', [$this, 'prependMunicipioViewPaths'], 1);

        // Inject eventData for all singles so the Blade override is safe when not an event.
        add_filter('Municipio/Template/single/viewData', [$this, 'injectEventData'], 20, 2);
        add_filter('Municipio/Template/events/single/viewData', [$this, 'injectEventData'], 20, 2);
    }

    /**
     * Absolute path to views/v3 (mirrors theme depth for Blade resolution).
     */
    private function pluginViewV3Root(): string
    {
        return rtrim(plugin_dir_path(__FILE__) . 'views/v3', '/\\');
    }

    /**
     * @param array<int, string> $paths
     * @return array<int, string>
     */
    public function prependMunicipioViewPaths(array $paths): array
    {
        $root = $this->pluginViewV3Root();
        if ($root === '' || !is_dir($root)) {
            return $paths;
        }

        $filtered = array_values(array_filter($paths, static function (string $p) use ($root): bool {
            return rtrim($p, '/\\') !== $root;
        }));

        // Append LAST so Blade prependLocation puts it FIRST.
        return array_merge($filtered, [$root]);
    }

    /**
     * @param array<string, mixed> $viewData
     * @param string|null $postType
     * @return array<string, mixed>
     */
    public function injectEventData(array $viewData, $postType = null): array
    {
        $viewData['eventData'] = $this->getEventData($postType);

        return $viewData;
    }

    /**
     * @param string|null $postType
     * @return array<string, string>|false
     */
    private function getEventData($postType = null)
    {
        $resolvedType = is_string($postType) && $postType !== ''
            ? $postType
            : (string) get_post_type();

        if ($resolvedType !== self::POST_TYPE) {
            return false;
        }

        $postId = (int) get_queried_object_id();
        if ($postId <= 0) {
            return false;
        }

        $eventData = [
            'start_datum' => $this->getFieldValue('start_datum', $postId),
            'slut_datum'  => $this->getFieldValue('slut_datum', $postId),
            'plats'       => $this->getFieldValue('plats', $postId),
            'pris'        => $this->getFieldValue('pris', $postId),
            'arrangor'    => $this->getFieldValue('arrangor', $postId),
        ];

        $hasAny = false;
        foreach ($eventData as $value) {
            if ($value !== '') {
                $hasAny = true;
                break;
            }
        }

        if (!$hasAny) {
            return false;
        }

        /**
         * Filter event sidebar field values before render.
         *
         * @param array<string, string> $eventData
         * @param int $postId
         */
        return apply_filters('sater_event_sidebar/event_data', $eventData, $postId);
    }

    private function getFieldValue(string $key, int $postId): string
    {
        if (function_exists('get_field')) {
            $value = get_field($key, $postId);
            if (is_string($value) || is_numeric($value)) {
                return trim((string) $value);
            }
        }

        $meta = get_post_meta($postId, $key, true);

        return is_string($meta) || is_numeric($meta) ? trim((string) $meta) : '';
    }
}

new Sater_Event_Sidebar();
