<?php
/**
 * Plugin Name: Säter Content Scheduler fixes
 * Description: Corrects Content Scheduler unpublish cron timing and purges news listing caches when posts change.
 * Version: 1.0.0
 * Author: Säter kommun
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SATER_NEWS_POST_TYPE = 'news';
const SATER_UNPUBLISH_HOOK = 'unpublish_post';
const SATER_UNPUBLISH_CRON_ACTIONS = ['draft', 'trash'];
const SATER_UNPUBLISH_DEFAULT_ACTION = 'draft';

add_action('save_post', 'sater_reschedule_unpublish_after_content_scheduler', 5);
add_action(SATER_UNPUBLISH_HOOK, 'sater_unpublish_post', 5, 2);
add_action('transition_post_status', 'sater_purge_news_caches_on_status_change', 10, 3);
add_action(SATER_UNPUBLISH_HOOK, 'sater_purge_news_caches_after_scheduled_unpublish', 20, 2);
add_action('init', 'sater_disable_latest_news_events_fragment_cache', 20);

/**
 * Content Scheduler schedules unpublish with a date string instead of a Unix timestamp.
 * Run after its save_post handler (priority 1) and replace with a correct cron entry.
 */
function sater_reschedule_unpublish_after_content_scheduler(int $postId): void
{
    if (wp_is_post_revision($postId) || get_post_type($postId) !== SATER_NEWS_POST_TYPE) {
        return;
    }

    sater_clear_unpublish_cron_for_post($postId);

    if (!isset($_POST['unpublish-active']) || $_POST['unpublish-active'] !== 'true') {
        return;
    }

    $meta = sater_get_unpublish_time_metadata_from_request();
    if ($meta === null) {
        return;
    }

    $timestamp = sater_compile_unpublish_timestamp($meta);
    if ($timestamp <= 0) {
        return;
    }

    $action = sater_get_unpublish_action($postId);
    update_post_meta($postId, 'unpublish-action', $action);

    wp_schedule_single_event($timestamp, SATER_UNPUBLISH_HOOK, [$postId, $action]);
}

function sater_get_unpublish_action(int $postId): string
{
    if (isset($_POST['unpublish-action']) && $_POST['unpublish-action'] !== '') {
        $action = sanitize_key((string) $_POST['unpublish-action']);
    } else {
        $stored = get_post_meta($postId, 'unpublish-action', true);
        $action = is_string($stored) && $stored !== '' ? sanitize_key($stored) : SATER_UNPUBLISH_DEFAULT_ACTION;
    }

    if (!in_array($action, SATER_UNPUBLISH_CRON_ACTIONS, true)) {
        return SATER_UNPUBLISH_DEFAULT_ACTION;
    }

    return $action;
}

/**
 * Run scheduled unpublish using post meta when cron args are incomplete.
 * Replaces Content Scheduler handler for this hook (priority 10).
 */
function sater_unpublish_post(int|array $postId, string $action = SATER_UNPUBLISH_DEFAULT_ACTION): void
{
    remove_all_actions(SATER_UNPUBLISH_HOOK, 10);

    if (is_array($postId)) {
        $action = isset($postId['action']) ? sanitize_key((string) $postId['action']) : SATER_UNPUBLISH_DEFAULT_ACTION;
        $postId = isset($postId['post_id']) ? (int) $postId['post_id'] : 0;
    }

    if ($postId <= 0) {
        return;
    }

    $action = sater_resolve_unpublish_action_for_post($postId, $action);

    $postStatus = get_post_status($postId);
    if (!is_string($postStatus) || !in_array($postStatus, ['publish', 'private', 'password', 'draft'], true)) {
        return;
    }

    if ($action === 'draft') {
        wp_update_post([
            'ID' => $postId,
            'post_status' => 'draft',
        ]);
        return;
    }

    wp_trash_post($postId);
}

function sater_resolve_unpublish_action_for_post(int $postId, string $action): string
{
    $stored = get_post_meta($postId, 'unpublish-action', true);

    if (is_string($stored) && in_array($stored, SATER_UNPUBLISH_CRON_ACTIONS, true)) {
        return $stored;
    }

    if (in_array($action, SATER_UNPUBLISH_CRON_ACTIONS, true)) {
        return $action;
    }

    return SATER_UNPUBLISH_DEFAULT_ACTION;
}

/**
 * @return array{aa: string, mm: string, jj: string, hh: string, mn: string}|null
 */
function sater_get_unpublish_time_metadata_from_request(): ?array
{
    $keys = ['unpublish-aa', 'unpublish-mm', 'unpublish-jj', 'unpublish-hh', 'unpublish-mn'];

    foreach ($keys as $key) {
        if (!array_key_exists($key, $_POST)) {
            return null;
        }
    }

    return [
        'aa' => sanitize_text_field((string) $_POST['unpublish-aa']),
        'mm' => sanitize_text_field((string) $_POST['unpublish-mm']),
        'jj' => sanitize_text_field((string) $_POST['unpublish-jj']),
        'hh' => sanitize_text_field((string) $_POST['unpublish-hh']),
        'mn' => sanitize_text_field((string) $_POST['unpublish-mn']),
    ];
}

/**
 * @param array{aa: string, mm: string, jj: string, hh: string, mn: string} $meta
 */
function sater_compile_unpublish_timestamp(array $meta): int
{
    $local = sprintf(
        '%s-%s-%s %s:%s:00',
        $meta['aa'],
        $meta['mm'],
        $meta['jj'],
        $meta['hh'],
        $meta['mn']
    );

    $datetime = date_create_immutable_from_format('Y-m-d H:i:s', $local, wp_timezone());

    if ($datetime === false) {
        return 0;
    }

    return $datetime->getTimestamp();
}

function sater_clear_unpublish_cron_for_post(int $postId): void
{
    $argSets = [
        ['post_id' => $postId],
    ];

    foreach (SATER_UNPUBLISH_CRON_ACTIONS as $action) {
        $argSets[] = ['post_id' => $postId, 'action' => $action];
        $argSets[] = [$postId, $action];
    }

    foreach ($argSets as $args) {
        while ($scheduled = wp_next_scheduled(SATER_UNPUBLISH_HOOK, $args)) {
            wp_unschedule_event($scheduled, SATER_UNPUBLISH_HOOK, $args);
        }
    }
}

function sater_purge_news_caches_on_status_change(string $newStatus, string $oldStatus, WP_Post $post): void
{
    if ($post->post_type !== SATER_NEWS_POST_TYPE) {
        return;
    }

    // Only news that is (or was) visible in listings can affect cached output.
    // Covers: publishing, scheduling, going live, editing a live post, and unpublishing.
    // Skips: opening the editor (auto-draft), draft edits, and autosaves.
    $visibleStatuses = ['publish', 'future'];
    $wasVisible = in_array($oldStatus, $visibleStatuses, true);
    $isVisible = in_array($newStatus, $visibleStatuses, true);

    if ($wasVisible || $isVisible) {
        sater_schedule_news_cache_purge();
    }
}

function sater_purge_news_caches_after_scheduled_unpublish(int $postId, string $action = SATER_UNPUBLISH_DEFAULT_ACTION): void
{
    if (get_post_type($postId) !== SATER_NEWS_POST_TYPE) {
        return;
    }

    sater_schedule_news_cache_purge();
}

/**
 * Defer the heavy purge to shutdown so the editor's publish request returns
 * immediately. The module-usage scan and PURGE requests then run after the
 * response is flushed to the client.
 */
function sater_schedule_news_cache_purge(): void
{
    static $scheduled = false;

    if ($scheduled) {
        return;
    }

    $scheduled = true;

    add_action('shutdown', 'sater_purge_news_listing_modules', 0);
}

function sater_disable_latest_news_events_fragment_cache(): void
{
    if (!class_exists(\Modularity\ModuleManager::class)) {
        return;
    }

    $moduleSlug = 'mod-latestnewsevents';

    if (!isset(\Modularity\ModuleManager::$moduleSettings[$moduleSlug])) {
        return;
    }

    \Modularity\ModuleManager::$moduleSettings[$moduleSlug]['cache_ttl'] = 0;
}

function sater_get_module_cache_group(): string
{
    $group = 'modules';

    if (function_exists('is_multisite') && is_multisite()) {
        $group .= '-' . get_current_blog_id();
    }

    return $group;
}

function sater_purge_news_listing_modules(): void
{
    // Release the HTTP response before the expensive scan when running on shutdown.
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    $cacheGroup = sater_get_module_cache_group();
    $moduleIds = sater_get_news_listing_module_ids();

    foreach ($moduleIds as $moduleId) {
        wp_cache_delete($moduleId, $cacheGroup);
    }

    sater_purge_pages_using_modules($moduleIds);
}

/**
 * @return int[]
 */
function sater_get_news_listing_module_ids(): array
{
    $moduleIds = [];

    $latestNewsEvents = get_posts([
        'post_type' => 'mod-latestnewsevents',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'fields' => 'ids',
        'suppress_filters' => true,
    ]);

    if (is_array($latestNewsEvents)) {
        $moduleIds = array_merge($moduleIds, array_map('intval', $latestNewsEvents));
    }

    $postsModules = get_posts([
        'post_type' => 'mod-posts',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'meta_query' => [
            [
                'key' => 'posts_data_post_type',
                'value' => SATER_NEWS_POST_TYPE,
                'compare' => '=',
            ],
        ],
        'fields' => 'ids',
        'suppress_filters' => true,
    ]);

    if (is_array($postsModules)) {
        $moduleIds = array_merge($moduleIds, array_map('intval', $postsModules));
    }

    return array_values(array_unique($moduleIds));
}

/**
 * @param int[] $moduleIds
 */
function sater_purge_pages_using_modules(array $moduleIds): void
{
    if ($moduleIds === [] || !class_exists(\Modularity\ModuleManager::class)) {
        return;
    }

    $pageIds = [];

    foreach ($moduleIds as $moduleId) {
        $usage = \Modularity\ModuleManager::getModuleUsage($moduleId);

        if (!is_array($usage)) {
            continue;
        }

        foreach ($usage as $page) {
            if (isset($page->post_id)) {
                $pageIds[(int) $page->post_id] = true;
            }
        }
    }

    foreach (array_keys($pageIds) as $pageId) {
        $permalink = get_the_permalink($pageId);

        if (!is_string($permalink) || $permalink === '') {
            continue;
        }

        wp_remote_request($permalink, [
            'method' => 'PURGE',
            'timeout' => 2,
            'redirection' => 0,
            'blocking' => false,
        ]);
    }
}
