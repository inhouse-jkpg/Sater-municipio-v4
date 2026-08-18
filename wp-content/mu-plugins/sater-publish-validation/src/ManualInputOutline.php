<?php

declare(strict_types=1);

namespace Sater\PublishValidation;

final class ManualInputOutline
{
    public const POST_TYPE = 'mod-manualinput';
    public const REPEATER_KEY = 'field_64ff22b2d91b7';
    public const CONTENT_KEY = 'field_64ff231ed91b9';
    public const TITLE_KEY = 'field_64ff22fdd91b8';
    public const LINK_KEY = 'field_64ff232ad91ba';
    public const LINK_TEXT_KEY = 'field_65002bce6d459';
    private const MAX_MODULES = 50;

    /**
     * @param array<string, mixed>|null $acf
     */
    public static function htmlForPost(int $postId, string $postType, string $title, ?array $acf = null): string
    {
        if ($postType === self::POST_TYPE) {
            return self::htmlForModule(
                $postId,
                $title,
                $acf,
                self::isTitleHidden($postId, $acf !== null)
            );
        }

        if ($postId < 1) {
            return '';
        }

        return self::htmlForPageModules($postId);
    }

    /**
     * Link labels from Manual Input rows that actually have a URL.
     *
     * @param array<string, mixed>|null $acf
     * @return array<int, string>
     */
    public static function linkLabelsForPost(int $postId, string $postType, ?array $acf = null): array
    {
        if ($postType === self::POST_TYPE) {
            return self::linkLabelsForModule($postId, $acf);
        }

        if ($postId < 1) {
            return [];
        }

        $labels = [];
        foreach (self::manualInputIdsOnPage($postId) as $moduleId) {
            $labels = array_merge($labels, self::linkLabelsForModule($moduleId, null));
        }

        return $labels;
    }

    /**
     * @param array<string, mixed>|null $acf
     */
    public static function htmlForModule(int $moduleId, string $title, ?array $acf, bool $hideTitle): string
    {
        $parts = [];

        $normalizedTitle = trim(wp_strip_all_tags($title));
        if (!$hideTitle && $normalizedTitle !== '') {
            $parts[] = '<h2>' . esc_html($normalizedTitle) . '</h2>';
        }

        foreach (self::contentFragments($moduleId, $acf) as $fragment) {
            $parts[] = $fragment;
        }

        return implode("\n", $parts);
    }

    public static function htmlForPageModules(int $pageId): string
    {
        $moduleIds = self::manualInputIdsOnPage($pageId);
        if ($moduleIds === []) {
            return '';
        }

        $parts = [];
        foreach ($moduleIds as $moduleId) {
            $title = (string) get_post_field('post_title', $moduleId);
            $hideTitle = (bool) get_post_meta($moduleId, 'modularity-module-hide-title', true);
            $html = self::htmlForModule($moduleId, $title, null, $hideTitle);
            if ($html !== '') {
                $parts[] = $html;
            }
        }

        return implode("\n", $parts);
    }

    /**
     * @return array<int, int>
     */
    private static function manualInputIdsOnPage(int $pageId): array
    {
        $sidebars = get_post_meta($pageId, 'modularity-modules', true);
        if (!is_array($sidebars) || $sidebars === []) {
            return [];
        }

        $ids = [];
        foreach ($sidebars as $sidebar) {
            if (!is_array($sidebar)) {
                continue;
            }

            foreach ($sidebar as $module) {
                if (!is_array($module) || empty($module['postid'])) {
                    continue;
                }

                if (self::isHiddenModule($module)) {
                    continue;
                }

                $ids[] = (int) $module['postid'];
                if (count($ids) >= self::MAX_MODULES) {
                    break 2;
                }
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === []) {
            return [];
        }

        $posts = get_posts([
            'post_type' => self::POST_TYPE,
            'post__in' => $ids,
            'posts_per_page' => count($ids),
            'post_status' => ['publish', 'private', 'future'],
            'orderby' => 'post__in',
            'suppress_filters' => true,
        ]);

        if (!is_array($posts) || $posts === []) {
            return [];
        }

        $found = [];
        foreach ($posts as $post) {
            if ($post instanceof \WP_Post) {
                $found[] = (int) $post->ID;
            }
        }

        return $found;
    }

    /**
     * @param array<string, mixed> $module
     */
    private static function isHiddenModule(array $module): bool
    {
        if (!isset($module['hidden'])) {
            return false;
        }

        $hidden = $module['hidden'];

        return $hidden === true || $hidden === 1 || $hidden === '1' || $hidden === 'true';
    }

    /**
     * @param array<string, mixed>|null $acf
     * @return array<int, string>
     */
    private static function linkLabelsForModule(int $moduleId, ?array $acf): array
    {
        $labels = [];

        foreach (self::inputRows($moduleId, $acf) as $row) {
            $link = self::rowString($row, 'link', self::LINK_KEY);
            if ($link === '') {
                continue;
            }

            $title = self::rowString($row, 'title', self::TITLE_KEY);
            if ($title !== '') {
                $labels[] = $title;
            }

            $linkText = self::rowString($row, 'link_text', self::LINK_TEXT_KEY);
            if ($linkText !== '') {
                $labels[] = $linkText;
            }
        }

        return $labels;
    }

    /**
     * @param array<string, mixed>|null $acf
     * @return array<int, array<string, mixed>>
     */
    private static function inputRows(int $moduleId, ?array $acf): array
    {
        if (is_array($acf) && $acf !== []) {
            $repeater = $acf[self::REPEATER_KEY] ?? $acf['manual_inputs'] ?? null;
            if (is_array($repeater)) {
                return array_values(array_filter($repeater, 'is_array'));
            }
        }

        if ($moduleId < 1 || !function_exists('get_field')) {
            return [];
        }

        $rows = get_field('manual_inputs', $moduleId);

        return is_array($rows) ? array_values(array_filter($rows, 'is_array')) : [];
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function rowString(array $row, string $name, string $key): string
    {
        $value = $row[$key] ?? $row[$name] ?? '';
        if (!is_string($value) && !is_numeric($value)) {
            return '';
        }

        return trim((string) wp_unslash((string) $value));
    }

    /**
     * @param array<string, mixed>|null $acf
     * @return array<int, string>
     */
    private static function contentFragments(int $moduleId, ?array $acf): array
    {
        if (is_array($acf) && $acf !== []) {
            $fromRequest = self::contentsFromAcf($acf);
            if ($fromRequest !== []) {
                return $fromRequest;
            }
        }

        return self::contentsFromSavedModule($moduleId);
    }

    /**
     * @return array<int, string>
     */
    private static function contentsFromSavedModule(int $moduleId): array
    {
        if ($moduleId < 1 || !function_exists('get_field')) {
            return [];
        }

        $rows = get_field('manual_inputs', $moduleId);
        if (!is_array($rows)) {
            return [];
        }

        $fragments = [];
        foreach ($rows as $row) {
            if (!is_array($row) || empty($row['content']) || !is_string($row['content'])) {
                continue;
            }

            $content = trim($row['content']);
            if ($content !== '') {
                $fragments[] = $content;
            }
        }

        return $fragments;
    }

    /**
     * @param array<string, mixed> $acf
     * @return array<int, string>
     */
    private static function contentsFromAcf(array $acf): array
    {
        $repeater = $acf[self::REPEATER_KEY] ?? $acf['manual_inputs'] ?? null;
        if (!is_array($repeater)) {
            return self::collectContentStrings($acf);
        }

        $fragments = [];
        foreach ($repeater as $row) {
            if (!is_array($row)) {
                continue;
            }

            $content = $row[self::CONTENT_KEY] ?? $row['content'] ?? '';
            if (is_string($content) && trim($content) !== '') {
                $fragments[] = (string) wp_unslash($content);
            }
        }

        return $fragments;
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private static function collectContentStrings(mixed $value): array
    {
        $fragments = [];

        if (!is_array($value)) {
            return $fragments;
        }

        foreach ($value as $key => $child) {
            if (($key === 'content' || $key === self::CONTENT_KEY) && is_string($child) && trim($child) !== '') {
                $fragments[] = (string) wp_unslash($child);
                continue;
            }

            $fragments = array_merge($fragments, self::collectContentStrings($child));
        }

        return $fragments;
    }

    private static function isTitleHidden(int $moduleId, bool $preferRequest): bool
    {
        if ($preferRequest) {
            return isset($_POST['modularity-module-hide-title'])
                && (string) $_POST['modularity-module-hide-title'] !== ''
                && (string) $_POST['modularity-module-hide-title'] !== '0';
        }

        if ($moduleId < 1) {
            return false;
        }

        return (bool) get_post_meta($moduleId, 'modularity-module-hide-title', true);
    }
}
