<?php
/**
 * Plugin Name: Säter Modularity Video: Mediaflow
 * Description: Adds Mediaflow video selection to the Modularity Video module (mod-video).
 * Version: 1.0.1
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

if (is_admin()) {
    add_action('acf/init', static function (): void {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        require_once SATER_MEDIAFLOW_VIDEO_PATH . 'acf-fields.php';
        sater_mediaflow_video_register_field_group();
    }, 25);

    add_filter('acf/load_field_group', 'sater_mediaflow_video_patch_field_group', 99, 1);
    add_filter('acf/load_field/key=' . SATER_VIDEO_TYPE_FIELD_KEY, 'sater_mediaflow_video_patch_type_field', 10, 1);

    add_action('admin_enqueue_scripts', 'sater_mediaflow_video_enqueue_admin_assets', 100);
    add_action('acf/save_post', 'sater_mediaflow_video_sync_usage', 20);
    add_action('admin_notices', 'sater_mediaflow_video_admin_notices');
}

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

    return sater_mediaflow_video_apply_mediaflow_embed($viewData);
}

/**
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function sater_mediaflow_video_apply_mediaflow_embed(array $data): array
{
    if (($data['type'] ?? '') !== SATER_VIDEO_TYPE_MEDIAFLOW) {
        return $data;
    }

    $embed = $data['mediaflow_embed'] ?? '';

    if ($embed === '' && !empty($data['ID']) && is_numeric($data['ID'])) {
        $embed = (string) get_field('mediaflow_embed', (int) $data['ID']);
    }

    $embed = trim($embed);

    if ($embed !== '') {
        $data['embedCode'] = $embed;
    }

    return $data;
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
