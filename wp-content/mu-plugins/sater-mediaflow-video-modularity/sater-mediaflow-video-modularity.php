<?php
/**
 * Plugin Name: Säter Modularity Video: Mediaflow
 * Description: Adds Mediaflow video selection to the Modularity Video module (mod-video).
 * Version: 1.0.3
 * Author: Säter kommun
 * License: MIT
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SATER_VIDEO_GROUP_KEY = 'group_57454ae7b0e9a';
const SATER_VIDEO_TYPE_FIELD_KEY = 'field_57454c24d44d8';
const SATER_VIDEO_TYPE_MEDIAFLOW = 'mediaflow';
const SATER_MEDIAFLOW_ID_FIELD_KEY = 'field_sater_mediaflow_video_id';
const SATER_MEDIAFLOW_EMBED_FIELD_KEY = 'field_sater_mediaflow_video_embed';
const SATER_MEDIAFLOW_USAGE_META_KEY = '_sater_mediaflow_video_usage_id';

define('SATER_MEDIAFLOW_VIDEO_PATH', __DIR__ . '/');
define('SATER_MEDIAFLOW_VIDEO_URL', plugin_dir_url(__FILE__));

/**
 * @return bool
 */
function sater_mediaflow_video_is_available(): bool
{
    return function_exists('mediaflow_get_access_token');
}

add_action('acf/init', static function (): void {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    require_once SATER_MEDIAFLOW_VIDEO_PATH . 'acf-fields.php';
    sater_mediaflow_video_register_field_group();
}, 25);

add_filter('acf/load_field_group', 'sater_mediaflow_video_patch_field_group', 99, 1);
add_filter('acf/load_field/key=' . SATER_VIDEO_TYPE_FIELD_KEY, 'sater_mediaflow_video_patch_type_field', 10, 1);
if (is_admin()) {
    add_action('admin_enqueue_scripts', 'sater_mediaflow_video_enqueue_admin_assets', 100);
    add_action('acf/save_post', 'sater_mediaflow_video_sync_usage', 20);
    add_action('admin_notices', 'sater_mediaflow_video_admin_notices');
}

add_action('wp_enqueue_scripts', 'sater_mediaflow_video_maybe_enqueue_frontend_assets_early', 20);

add_filter('Modularity/Display/mod-video/viewData', 'sater_mediaflow_video_filter_view_data', 10, 1);
add_filter('Modularity/Block/Data', 'sater_mediaflow_video_filter_block_data', 10, 3);

/**
 * @param array<string, mixed>|false $fieldGroup
 * @return array<string, mixed>|false
 */
function sater_mediaflow_video_patch_field_group($fieldGroup)
{
    if (!is_array($fieldGroup) || ($fieldGroup['key'] ?? '') !== SATER_VIDEO_GROUP_KEY) {
        return $fieldGroup;
    }

    if (empty($fieldGroup['fields']) || !is_array($fieldGroup['fields'])) {
        return $fieldGroup;
    }

    $hasMediaflowFields = false;

    foreach ($fieldGroup['fields'] as $field) {
        if (!is_array($field)) {
            continue;
        }

        if (($field['key'] ?? '') === SATER_MEDIAFLOW_EMBED_FIELD_KEY) {
            $hasMediaflowFields = true;
            break;
        }
    }

    if ($hasMediaflowFields) {
        return $fieldGroup;
    }

    $mediaflowFields = sater_mediaflow_video_get_mediaflow_fields();
    $insertAt = null;

    foreach ($fieldGroup['fields'] as $index => $field) {
        if (is_array($field) && ($field['key'] ?? '') === 'field_57454c7ad44dc') {
            $insertAt = $index + 1;
            break;
        }
    }

    if ($insertAt === null) {
        $fieldGroup['fields'] = array_merge($fieldGroup['fields'], $mediaflowFields);
    } else {
        array_splice($fieldGroup['fields'], $insertAt, 0, $mediaflowFields);
    }

    return $fieldGroup;
}

/**
 * @param array<string, mixed>|false $field
 * @return array<string, mixed>|false
 */
function sater_mediaflow_video_patch_type_field($field)
{
    if (!is_array($field)) {
        return $field;
    }

    $field['choices'] = is_array($field['choices'] ?? null) ? $field['choices'] : [];
    $field['choices'][SATER_VIDEO_TYPE_MEDIAFLOW] = __('Mediaflow', 'sater-mediaflow-video');

    return $field;
}

/**
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_mediaflow_video_filter_view_data(array $data): array
{
    return sater_mediaflow_video_apply_mediaflow_embed($data);
}

/**
 * @param array<string, mixed> $viewData
 * @param array<string, mixed> $block
 * @param \Modularity\Module $module
 * @return array<string, mixed>
 */
function sater_mediaflow_video_filter_block_data(array $viewData, array $block, $module): array
{
    if (($module->slug ?? '') !== 'video') {
        return $viewData;
    }

    $blockData = $block['data'] ?? [];

    if (empty($viewData['type']) && !empty($blockData['type'])) {
        $viewData['type'] = $blockData['type'];
    }

    if (empty($viewData['mediaflow_embed']) && !empty($blockData['mediaflow_embed'])) {
        $viewData['mediaflow_embed'] = $blockData['mediaflow_embed'];
    }

    if (empty($viewData['placeholder_image']) && !empty($blockData['placeholder_image'])) {
        $viewData['placeholder_image'] = $blockData['placeholder_image'];
    }

    return sater_mediaflow_video_apply_mediaflow_embed($viewData);
}

/**
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_mediaflow_video_apply_mediaflow_embed(array $data): array
{
    $postId = isset($data['ID']) && is_numeric($data['ID']) ? (int) $data['ID'] : 0;

    if ($postId > 0 && get_post_type($postId) === 'mod-video') {
        $data['type'] = (string) get_post_meta($postId, 'type', true);
        $data['mediaflow_embed'] = (string) get_post_meta($postId, 'mediaflow_embed', true);
    }

    if (($data['type'] ?? '') !== SATER_VIDEO_TYPE_MEDIAFLOW) {
        return $data;
    }

    $embed = trim((string) ($data['mediaflow_embed'] ?? ''));

    if ($embed === '' && $postId > 0) {
        $embed = trim((string) get_field('mediaflow_embed', $postId));
    }

    if ($embed === '') {
        return $data;
    }

    $iframe = sater_mediaflow_video_normalize_embed($embed);
    $posterUrl = sater_mediaflow_video_resolve_custom_poster_url($data, $postId);

    $hasPoster = $posterUrl !== false && $iframe !== '';

    sater_mediaflow_video_maybe_enqueue_frontend_assets($hasPoster);

    if ($hasPoster) {
        $data['image'] = $posterUrl;
        $embedId = !empty($data['id']) ? (string) $data['id'] : 'embed-' . wp_unique_id();
        $data['id'] = $embedId;
        $data['embedCode'] = sater_mediaflow_video_build_poster_embed($iframe, $embedId, $posterUrl);
    } else {
        $data['image'] = false;
        $data['embedCode'] = $iframe;
    }

    return $data;
}

/**
 * Resolve Affischbild only when the editor explicitly selected placeholder_image.
 *
 * @param array<string, mixed> $data
 * @param int $postId
 * @return string|false
 */
function sater_mediaflow_video_resolve_custom_poster_url(array $data, int $postId): string|false
{
    $placeholder = $data['placeholder_image'] ?? null;

    if (empty($placeholder) && $postId > 0) {
        $placeholder = get_field('placeholder_image', $postId);
    }

    $attachmentId = 0;

    if (is_array($placeholder) && !empty($placeholder['id'])) {
        $attachmentId = (int) $placeholder['id'];
    } elseif (is_numeric($placeholder)) {
        $attachmentId = (int) $placeholder;
    }

    if ($attachmentId <= 0) {
        return false;
    }

    $src = wp_get_attachment_image_src($attachmentId, [1140, 641]);

    if (!is_array($src) || empty($src[0])) {
        return false;
    }

    return $src[0];
}

/**
 * @param string $iframe Normalized iframe markup.
 * @param string $embedId Unique DOM id for the deferred embed template.
 * @param string $posterUrl Poster image URL.
 * @return string
 */
function sater_mediaflow_video_build_poster_embed(string $iframe, string $embedId, string $posterUrl): string
{
    $playButton = '<span class="sater-mediaflow-play" role="presentation" aria-hidden="true"></span>';

    return sprintf(
        '<div class="sater-mediaflow-poster" data-embed-id="%1$s"><img src="%2$s" class="sater-mediaflow-poster__image" alt="">%4$s</div><script type="text/template" id="%1$s">%3$s</script>',
        esc_attr($embedId),
        esc_url($posterUrl),
        $iframe,
        $playButton
    );
}

/**
 * Extract a clean iframe from Mediaflow's wrapper markup.
 *
 * @param string $embed Raw embed HTML from Mediaflow.
 * @return string
 */
function sater_mediaflow_video_normalize_embed(string $embed): string
{
    if (!preg_match('/<iframe\b[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>/i', $embed, $m)) {
        return $embed;
    }

    $src = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');

    if (str_starts_with($src, '//')) {
        $src = 'https:' . $src;
    }

    $title = 'Mediaflow video';
    if (preg_match('/\btitle=["\']([^"\']*)["\']/', $m[0], $t) && $t[1] !== '') {
        $title = html_entity_decode($t[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    return sprintf(
        '<iframe src="%s" title="%s" allow="fullscreen; autoplay; encrypted-media; picture-in-picture" allowfullscreen loading="lazy" style="border:0"></iframe>',
        esc_url($src),
        esc_attr($title)
    );
}

/**
 * @return int
 */
function sater_mediaflow_video_get_page_context_post_id(): int
{
    if (class_exists(\Modularity\Helper\Wp::class)) {
        $archiveSlug = \Modularity\Helper\Wp::getArchiveSlug();

        if (is_numeric($archiveSlug)) {
            return (int) $archiveSlug;
        }
    }

    global $post;

    if ($post instanceof WP_Post) {
        return (int) $post->ID;
    }

    if (class_exists(\Municipio\Helper\CurrentPostId::class)) {
        $postId = \Municipio\Helper\CurrentPostId::get();

        if (is_numeric($postId)) {
            return (int) $postId;
        }
    }

    return 0;
}

/**
 * @param mixed $placeholder
 * @return bool
 */
function sater_mediaflow_video_placeholder_is_set(mixed $placeholder): bool
{
    if (is_array($placeholder) && !empty($placeholder['id'])) {
        return (int) $placeholder['id'] > 0;
    }

    return is_numeric($placeholder) && (int) $placeholder > 0;
}

/**
 * @param array<int, array<string, mixed>> $blocks
 * @return bool
 */
function sater_mediaflow_video_blocks_contain_mediaflow(array $blocks): bool
{
    foreach ($blocks as $block) {
        if (($block['blockName'] ?? '') === 'acf/video') {
            if (($block['attrs']['data']['type'] ?? '') === SATER_VIDEO_TYPE_MEDIAFLOW) {
                return true;
            }
        }

        if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            if (sater_mediaflow_video_blocks_contain_mediaflow($block['innerBlocks'])) {
                return true;
            }
        }
    }

    return false;
}

/**
 * @param array<int, array<string, mixed>> $blocks
 * @return bool
 */
function sater_mediaflow_video_blocks_need_poster_assets(array $blocks): bool
{
    foreach ($blocks as $block) {
        if (($block['blockName'] ?? '') === 'acf/video') {
            $blockData = $block['attrs']['data'] ?? [];

            if (($blockData['type'] ?? '') === SATER_VIDEO_TYPE_MEDIAFLOW
                && sater_mediaflow_video_placeholder_is_set($blockData['placeholder_image'] ?? null)
            ) {
                return true;
            }
        }

        if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            if (sater_mediaflow_video_blocks_need_poster_assets($block['innerBlocks'])) {
                return true;
            }
        }
    }

    return false;
}

/**
 * @param int $postId
 * @return bool
 */
function sater_mediaflow_video_page_has_mediaflow(int $postId): bool
{
    $moduleSidebars = get_post_meta($postId, 'modularity-modules', true);

    if (is_array($moduleSidebars)) {
        foreach ($moduleSidebars as $sidebar) {
            if (!is_array($sidebar)) {
                continue;
            }

            foreach ($sidebar as $module) {
                if (!is_array($module)) {
                    continue;
                }

                $modulePostId = (int) ($module['postid'] ?? 0);

                if ($modulePostId <= 0 || get_post_type($modulePostId) !== 'mod-video') {
                    continue;
                }

                if (get_post_meta($modulePostId, 'type', true) === SATER_VIDEO_TYPE_MEDIAFLOW) {
                    return true;
                }
            }
        }
    }

    $post = get_post($postId);

    if (!$post instanceof WP_Post || $post->post_content === '' || !has_blocks($post->post_content)) {
        return false;
    }

    return sater_mediaflow_video_blocks_contain_mediaflow(parse_blocks($post->post_content));
}

/**
 * @param int $postId
 * @return bool
 */
function sater_mediaflow_video_page_needs_poster_assets(int $postId): bool
{
    $moduleSidebars = get_post_meta($postId, 'modularity-modules', true);

    if (is_array($moduleSidebars)) {
        foreach ($moduleSidebars as $sidebar) {
            if (!is_array($sidebar)) {
                continue;
            }

            foreach ($sidebar as $module) {
                if (!is_array($module)) {
                    continue;
                }

                $modulePostId = (int) ($module['postid'] ?? 0);

                if ($modulePostId <= 0 || get_post_type($modulePostId) !== 'mod-video') {
                    continue;
                }

                if (get_post_meta($modulePostId, 'type', true) !== SATER_VIDEO_TYPE_MEDIAFLOW) {
                    continue;
                }

                if (sater_mediaflow_video_placeholder_is_set(get_field('placeholder_image', $modulePostId))) {
                    return true;
                }
            }
        }
    }

    $post = get_post($postId);

    if (!$post instanceof WP_Post || $post->post_content === '' || !has_blocks($post->post_content)) {
        return false;
    }

    return sater_mediaflow_video_blocks_need_poster_assets(parse_blocks($post->post_content));
}

/**
 * @return void
 */
function sater_mediaflow_video_maybe_enqueue_frontend_assets_early(): void
{
    if (is_admin()) {
        return;
    }

    $postId = sater_mediaflow_video_get_page_context_post_id();

    if ($postId <= 0 || !sater_mediaflow_video_page_has_mediaflow($postId)) {
        return;
    }

    sater_mediaflow_video_maybe_enqueue_frontend_assets(
        sater_mediaflow_video_page_needs_poster_assets($postId)
    );
}

/**
 * @param bool $needsPosterJs
 * @return void
 */
function sater_mediaflow_video_maybe_enqueue_frontend_assets(bool $needsPosterJs = false): void
{
    static $cssEnqueued = false;
    static $jsEnqueued = false;

    $version = '1.0.9';

    if (!$cssEnqueued) {
        wp_enqueue_style(
            'sater-mediaflow-video-frontend',
            SATER_MEDIAFLOW_VIDEO_URL . 'assets/css/frontend.css',
            [],
            $version
        );
        $cssEnqueued = true;
    }

    if ($needsPosterJs && !$jsEnqueued) {
        wp_enqueue_script(
            'sater-mediaflow-video-frontend',
            SATER_MEDIAFLOW_VIDEO_URL . 'assets/js/frontend.js',
            [],
            $version,
            true
        );
        $jsEnqueued = true;
    }
}

/**
 * @return bool
 */
function sater_mediaflow_video_should_enqueue_admin_assets(): bool
{
    if (!is_admin()) {
        return false;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (!$screen instanceof WP_Screen) {
        return false;
    }

    if (($screen->post_type ?? '') === 'mod-video') {
        return true;
    }

    if (in_array($screen->base, ['post', 'page'], true)) {
        return true;
    }

    if (str_contains((string) ($screen->id ?? ''), 'modularity')) {
        return true;
    }

    if (class_exists(\Modularity\Helper\Wp::class) && \Modularity\Helper\Wp::isThickBox()) {
        return true;
    }

    return false;
}

/**
 * @return void
 */
function sater_mediaflow_video_admin_notices(): void
{
    if (!current_user_can('manage_options') || !sater_mediaflow_video_should_enqueue_admin_assets()) {
        return;
    }

    if (sater_mediaflow_video_is_available()) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo esc_html__(
        'Mediaflow plugin is not active. Activate it under Plugins to use Mediaflow in the Video module and open Settings → Mediaflow.',
        'sater-mediaflow-video'
    );
    echo '</p></div>';
}

/**
 * @return void
 */
function sater_mediaflow_video_enqueue_admin_assets(): void
{
    if (!sater_mediaflow_video_should_enqueue_admin_assets()) {
        return;
    }

    if (sater_mediaflow_video_is_available() && function_exists('mediaflow_enqueue_scripts')) {
        mediaflow_enqueue_scripts();
    }

    $version = '1.0.1';

    wp_enqueue_style(
        'sater-mediaflow-video-admin',
        SATER_MEDIAFLOW_VIDEO_URL . 'assets/css/admin.css',
        ['mediaflow-file-selector'],
        $version
    );

    $scriptDeps = ['jquery', 'acf-input'];

    if (sater_mediaflow_video_is_available()) {
        $scriptDeps[] = 'mediaflow';
        $scriptDeps[] = 'mediaflow-file-selector';
    }

    wp_enqueue_script(
        'sater-mediaflow-video-admin',
        SATER_MEDIAFLOW_VIDEO_URL . 'assets/js/admin.js',
        $scriptDeps,
        $version,
        true
    );

    wp_localize_script('sater-mediaflow-video-admin', 'saterMediaflowVideo', [
        'typeFieldKey' => SATER_VIDEO_TYPE_FIELD_KEY,
        'typeValue' => SATER_VIDEO_TYPE_MEDIAFLOW,
        'idFieldKey' => SATER_MEDIAFLOW_ID_FIELD_KEY,
        'embedFieldKey' => SATER_MEDIAFLOW_EMBED_FIELD_KEY,
        'pluginActive' => sater_mediaflow_video_is_available(),
        'settingsUrl' => admin_url('options-general.php?page=mediaflow'),
        'i18n' => [
            'openSelector' => __('Open Mediaflow file selector', 'sater-mediaflow-video'),
            'changeVideo' => __('Change video', 'sater-mediaflow-video'),
            'noToken' => __('Mediaflow API keys are missing. Configure them under Settings → Mediaflow (or MEDIAFLOW_* in the server environment).', 'sater-mediaflow-video'),
            'pluginInactive' => __('The Mediaflow plugin must be activated under Plugins before you can select videos.', 'sater-mediaflow-video'),
            'settingsLink' => __('Open Mediaflow settings', 'sater-mediaflow-video'),
            'previewLabel' => __('Selected video preview', 'sater-mediaflow-video'),
        ],
    ]);
}

/**
 * @param int|string $postId
 * @return void
 */
function sater_mediaflow_video_sync_usage($postId): void
{
    $postId = (int) $postId;

    if ($postId <= 0 || get_post_type($postId) !== 'mod-video') {
        return;
    }

    if (!sater_mediaflow_video_is_available()) {
        return;
    }

    $type = (string) get_field('type', $postId);
    $mediaflowId = (int) get_field('mediaflow_id', $postId);
    $previousId = (int) get_post_meta($postId, SATER_MEDIAFLOW_USAGE_META_KEY, true);

    if ($type === SATER_VIDEO_TYPE_MEDIAFLOW && $mediaflowId > 0) {
        sater_mediaflow_video_ping_usage($postId, $mediaflowId, false);

        if ($previousId > 0 && $previousId !== $mediaflowId) {
            sater_mediaflow_video_ping_usage($postId, $previousId, true);
        }

        update_post_meta($postId, SATER_MEDIAFLOW_USAGE_META_KEY, $mediaflowId);

        return;
    }

    if ($previousId > 0) {
        sater_mediaflow_video_ping_usage($postId, $previousId, true);
        delete_post_meta($postId, SATER_MEDIAFLOW_USAGE_META_KEY);
    }
}

/**
 * @param int $postId Module post ID (mod-video).
 * @param int $mediaflowId Mediaflow file ID.
 * @param bool $removed Whether the file was removed from the page.
 * @return void
 */
function sater_mediaflow_video_ping_usage(int $postId, int $mediaflowId, bool $removed): void
{
    $token = mediaflow_get_access_token();

    if (!$token) {
        return;
    }

    $user = wp_get_current_user();

    wp_remote_post(
        'https://customerapi.mediaflowpro.com/1/files/multiple/usages',
        [
            'body' => wp_json_encode([
                'amount' => 1,
                'date' => wp_date('Y-m-d'),
                'id' => [$mediaflowId],
                'project' => 'WordPress',
                'contact' => $user->exists() ? $user->display_name : 'WordPress',
                'removed' => $removed,
                'types' => ['web'],
                'web' => [
                    'page' => get_the_permalink($postId) ?: '',
                    'pageName' => get_the_title($postId) ?: '',
                ],
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 15,
        ]
    );
}
