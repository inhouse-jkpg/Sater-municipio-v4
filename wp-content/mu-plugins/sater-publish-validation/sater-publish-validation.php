<?php
/**
 * Plugin Name: Säter Publiceringsvalidering
 * Description: Publiceringsgrind för redaktörer: titel, rubrikstruktur (även manuell inmatning), alt-text och otydliga länktexter. Se README.md i pluginmappen.
 * Version: 1.0.0
 * Author: Säter kommun
 * License: MIT
 * Requires PHP: 8.1
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SATER_PUBLISH_VALIDATION_MIN_TITLE = 3;
const SATER_PUBLISH_VALIDATION_WARN_TITLE = 60;
const SATER_PUBLISH_VALIDATION_NOTICE_KEY = 'sater_publish_validation_notice';

require_once __DIR__ . '/src/ManualInputOutline.php';
require_once __DIR__ . '/src/Violation.php';
require_once __DIR__ . '/src/Snapshot.php';
require_once __DIR__ . '/src/RuleInterface.php';
require_once __DIR__ . '/src/Engine.php';
require_once __DIR__ . '/src/Rules/TitleLengthRule.php';
require_once __DIR__ . '/src/Rules/HeadingStructureRule.php';
require_once __DIR__ . '/src/Rules/ImageAltRule.php';
require_once __DIR__ . '/src/Rules/VagueLinkTextRule.php';

add_action('init', 'sater_publish_validation_boot');

function sater_publish_validation_boot(): void
{
    add_filter('wp_insert_post_data', 'sater_publish_validation_filter_insert_post_data', 99, 2);
    add_action('rest_api_init', 'sater_publish_validation_register_rest_gates');
    add_action('acf/validate_save_post', 'sater_publish_validation_acf_validate', 5);
    add_action('wp_ajax_sater_publish_validation_check', 'sater_publish_validation_ajax_check');
    add_action('admin_enqueue_scripts', 'sater_publish_validation_enqueue_admin_assets');
    add_action('admin_notices', 'sater_publish_validation_admin_notice');
    add_filter('redirect_post_location', 'sater_publish_validation_redirect_post_location');
}

/**
 * @return array<int, string>
 */
function sater_publish_validation_post_types(): array
{
    $postTypes = ['page', 'post', 'news', 'events', 'mod-manualinput'];

    /**
     * Filter which public post types are checked before publish.
     *
     * @param array<int, string> $postTypes
     */
    $postTypes = apply_filters('sater_publish_validation_post_types', $postTypes);

    return array_values(array_unique(array_filter(
        $postTypes,
        static fn ($postType): bool => is_string($postType) && $postType !== ''
    )));
}

function sater_publish_validation_is_supported_post_type(string $postType): bool
{
    return in_array($postType, sater_publish_validation_post_types(), true);
}

function sater_publish_validation_is_publish_status(string $status): bool
{
    return in_array($status, ['publish', 'future'], true);
}

function sater_publish_validation_can_bypass(int $postId): bool
{
    $bypass = defined('SATER_PUBLISH_VALIDATION_BYPASS') && SATER_PUBLISH_VALIDATION_BYPASS;

    /**
     * Allow skipping the publish gate (imports, emergencies).
     *
     * @param bool $bypass
     * @param int  $postId
     */
    return (bool) apply_filters('sater_publish_validation_bypass', $bypass, $postId);
}

/**
 * @return array<int, \Sater\PublishValidation\RuleInterface>
 */
function sater_publish_validation_rules(): array
{
    $rules = [
        new \Sater\PublishValidation\Rules\TitleLengthRule(
            SATER_PUBLISH_VALIDATION_MIN_TITLE,
            SATER_PUBLISH_VALIDATION_WARN_TITLE
        ),
        new \Sater\PublishValidation\Rules\HeadingStructureRule(),
        new \Sater\PublishValidation\Rules\ImageAltRule(),
        new \Sater\PublishValidation\Rules\VagueLinkTextRule(),
    ];

    /**
     * Add or replace validation rules. Each item must implement RuleInterface.
     *
     * @param array<int, \Sater\PublishValidation\RuleInterface> $rules
     */
    $rules = apply_filters('sater_publish_validation_rules', $rules);

    return array_values(array_filter(
        $rules,
        static fn ($rule): bool => $rule instanceof \Sater\PublishValidation\RuleInterface
    ));
}

function sater_publish_validation_engine(): \Sater\PublishValidation\Engine
{
    return new \Sater\PublishValidation\Engine(sater_publish_validation_rules());
}

/**
 * @return array<int, \Sater\PublishValidation\Violation>
 */
function sater_publish_validation_check(\Sater\PublishValidation\Snapshot $snapshot): array
{
    $violations = sater_publish_validation_engine()->check($snapshot);

    /**
     * Add custom violations without writing a rule class.
     *
     * @param array<int, \Sater\PublishValidation\Violation> $violations
     * @param \Sater\PublishValidation\Snapshot              $snapshot
     */
    $violations = apply_filters('sater_publish_validation_violations', $violations, $snapshot);

    return array_values(array_filter(
        $violations,
        static fn ($violation): bool => $violation instanceof \Sater\PublishValidation\Violation
    ));
}

/**
 * @param array<int, \Sater\PublishValidation\Violation> $violations
 * @return array<int, \Sater\PublishValidation\Violation>
 */
function sater_publish_validation_errors(array $violations): array
{
    return array_values(array_filter(
        $violations,
        static fn ($violation): bool => $violation->severity === 'error'
    ));
}

/**
 * @param array<string, mixed> $data
 * @param array<string, mixed> $postarr
 * @return array<string, mixed>
 */
function sater_publish_validation_filter_insert_post_data(array $data, array $postarr): array
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $data;
    }

    if (defined('WP_IMPORTING') && WP_IMPORTING) {
        return $data;
    }

    $postType = isset($data['post_type']) ? (string) $data['post_type'] : '';
    if (!sater_publish_validation_is_supported_post_type($postType)) {
        return $data;
    }

    $newStatus = isset($data['post_status']) ? (string) $data['post_status'] : '';
    if (!sater_publish_validation_is_publish_status($newStatus)) {
        return $data;
    }

    $postId = isset($postarr['ID']) ? (int) $postarr['ID'] : 0;
    if ($postId > 0 && wp_is_post_revision($postId)) {
        return $data;
    }

    if (sater_publish_validation_can_bypass($postId)) {
        return $data;
    }

    $snapshot = \Sater\PublishValidation\Snapshot::fromInsertData($data, $postarr);
    $violations = sater_publish_validation_check($snapshot);
    $errors = sater_publish_validation_errors($violations);

    if ($errors === []) {
        return $data;
    }

    $previousStatus = 'draft';
    if ($postId > 0) {
        $stored = get_post_status($postId);
        if (is_string($stored) && $stored !== '' && $stored !== 'auto-draft') {
            $previousStatus = $stored;
        }
    }

    if (sater_publish_validation_is_publish_status($previousStatus) && $postId > 0) {
        $original = get_post($postId);
        if ($original instanceof WP_Post) {
            $data['post_title'] = $original->post_title;
            $data['post_content'] = $original->post_content;
            $data['post_excerpt'] = $original->post_excerpt;
            $data['post_status'] = $original->post_status;
        }
    } else {
        $data['post_status'] = $previousStatus === 'pending' ? 'pending' : 'draft';
    }

    sater_publish_validation_store_notice($postId, $errors);

    return $data;
}

function sater_publish_validation_register_rest_gates(): void
{
    foreach (sater_publish_validation_post_types() as $postType) {
        add_filter("rest_pre_insert_{$postType}", 'sater_publish_validation_rest_pre_insert', 10, 2);
    }
}

/**
 * @param \stdClass        $preparedPost
 * @param \WP_REST_Request $request
 * @return \stdClass|\WP_Error
 */
function sater_publish_validation_rest_pre_insert($preparedPost, $request)
{
    $status = isset($preparedPost->post_status) ? (string) $preparedPost->post_status : '';
    if ($status === '' && $request instanceof WP_REST_Request) {
        $status = (string) $request->get_param('status');
    }

    if (!sater_publish_validation_is_publish_status($status)) {
        return $preparedPost;
    }

    $postId = isset($preparedPost->ID) ? (int) $preparedPost->ID : 0;
    if (sater_publish_validation_can_bypass($postId)) {
        return $preparedPost;
    }

    $snapshot = \Sater\PublishValidation\Snapshot::fromRest($preparedPost, $request);
    $errors = sater_publish_validation_errors(sater_publish_validation_check($snapshot));

    if ($errors === []) {
        return $preparedPost;
    }

    $messages = array_map(
        static fn ($error): string => $error->message,
        $errors
    );

    return new WP_Error(
        'sater_publish_validation',
        implode("\n", $messages),
        [
            'status' => 400,
            'violations' => array_map(
                static fn ($error): array => $error->toArray(),
                $errors
            ),
        ]
    );
}

function sater_publish_validation_acf_validate(): void
{
    if (!function_exists('acf_add_validation_error')) {
        return;
    }

    $postId = isset($_POST['post_ID']) ? (int) $_POST['post_ID'] : 0;
    $postType = isset($_POST['post_type']) ? sanitize_key((string) $_POST['post_type']) : '';

    if ($postType === '' && $postId > 0) {
        $postType = (string) get_post_type($postId);
    }

    if (!sater_publish_validation_is_supported_post_type($postType)) {
        return;
    }

    $status = isset($_POST['post_status']) ? sanitize_key((string) $_POST['post_status']) : '';
    if (isset($_POST['publish'])) {
        $status = 'publish';
    }

    if (!sater_publish_validation_is_publish_status($status)) {
        return;
    }

    if (sater_publish_validation_can_bypass($postId)) {
        return;
    }

    $snapshot = \Sater\PublishValidation\Snapshot::fromRequest($postId, $postType);
    $errors = sater_publish_validation_errors(sater_publish_validation_check($snapshot));

    foreach ($errors as $error) {
        acf_add_validation_error('', $error->message);
    }
}

function sater_publish_validation_ajax_check(): void
{
    if (!check_ajax_referer('sater_publish_validation', 'nonce', false)) {
        wp_send_json_error(['message' => __('Ogiltig session. Ladda om sidan och försök igen.', 'sater-publish-validation')], 403);
    }

    $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    if ($postId < 1 || !current_user_can('edit_post', $postId)) {
        wp_send_json_error(['message' => __('Du har inte behörighet att validera den här sidan.', 'sater-publish-validation')], 403);
    }

    $postType = (string) get_post_type($postId);
    if (!sater_publish_validation_is_supported_post_type($postType)) {
        wp_send_json_success([
            'canPublish' => true,
            'violations' => [],
        ]);
    }

    $snapshot = \Sater\PublishValidation\Snapshot::fromRequest($postId, $postType);
    $violations = sater_publish_validation_check($snapshot);
    $errors = sater_publish_validation_errors($violations);

    wp_send_json_success([
        'canPublish' => $errors === [],
        'violations' => array_map(
            static fn ($violation): array => $violation->toArray(),
            $violations
        ),
    ]);
}

function sater_publish_validation_enqueue_admin_assets(string $hook): void
{
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $postType = $screen && isset($screen->post_type) ? (string) $screen->post_type : '';
    if (!sater_publish_validation_is_supported_post_type($postType)) {
        return;
    }

    $handle = 'sater-publish-validation-admin';
    $baseUrl = plugin_dir_url(__FILE__);

    wp_enqueue_style(
        $handle,
        $baseUrl . 'assets/admin.css',
        [],
        '1.0.4'
    );

    wp_enqueue_script(
        $handle,
        $baseUrl . 'assets/admin.js',
        [],
        '1.0.4',
        true
    );

    wp_localize_script($handle, 'saterPublishValidation', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('sater_publish_validation'),
        'i18n' => [
            'title' => __('Sidan kan inte publiceras', 'sater-publish-validation'),
            'intro' => __('Åtgärda felen nedan innan du publicerar.', 'sater-publish-validation'),
            'warningsTitle' => __('Kontrollera innan publicering', 'sater-publish-validation'),
            'warningsHeading' => __('Varningar', 'sater-publish-validation'),
            'warningIntro' => __('Du kan publicera, men tänk på följande.', 'sater-publish-validation'),
            'close' => __('Stäng', 'sater-publish-validation'),
            'publish' => __('Publicera', 'sater-publish-validation'),
            'checking' => __('Kontrollerar sidan…', 'sater-publish-validation'),
            'requestError' => __('Kunde inte kontrollera sidan. Försök igen.', 'sater-publish-validation'),
        ],
    ]);
}

/**
 * @param array<int, \Sater\PublishValidation\Violation> $errors
 */
function sater_publish_validation_store_notice(int $postId, array $errors): void
{
    $userId = get_current_user_id();
    if ($userId < 1 || $errors === []) {
        return;
    }

    set_transient(
        SATER_PUBLISH_VALIDATION_NOTICE_KEY . '_' . $userId,
        [
            'postId' => $postId,
            'messages' => array_map(
                static fn ($error): string => $error->message,
                $errors
            ),
        ],
        120
    );
}

function sater_publish_validation_redirect_post_location(string $location): string
{
    $userId = get_current_user_id();
    if ($userId < 1) {
        return $location;
    }

    if (get_transient(SATER_PUBLISH_VALIDATION_NOTICE_KEY . '_' . $userId)) {
        $location = add_query_arg('sater_publish_blocked', '1', $location);
    }

    return $location;
}

function sater_publish_validation_admin_notice(): void
{
    $userId = get_current_user_id();
    if ($userId < 1) {
        return;
    }

    $payload = get_transient(SATER_PUBLISH_VALIDATION_NOTICE_KEY . '_' . $userId);
    if (!is_array($payload) || empty($payload['messages']) || !is_array($payload['messages'])) {
        return;
    }

    delete_transient(SATER_PUBLISH_VALIDATION_NOTICE_KEY . '_' . $userId);

    echo '<div class="notice notice-error is-dismissible"><p><strong>';
    echo esc_html__('Sidan kan inte publiceras', 'sater-publish-validation');
    echo '</strong></p><ul>';

    foreach ($payload['messages'] as $message) {
        if (!is_string($message) || $message === '') {
            continue;
        }
        echo '<li>' . esc_html($message) . '</li>';
    }

    echo '</ul></div>';
}
