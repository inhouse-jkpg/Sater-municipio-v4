<?php

/*
 * Plugin Name: Mediaflow
 * Plugin URI: https://support.mediaflow.com/hur-installerar-jag-ert-plugin-for-wordpress
 * Description: A Mediaflow integration plugin for WordPress.
 * Author: Mediaflow
 * Author URI: https://mediaflow.com/
 * Version: 2.0.22
 * Requires PHP: 7.4
 * Requires at least: 5.8
 */

declare(strict_types=1);

defined('ABSPATH') || exit();

// Include WordPress functions to upload files.
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// Import plugin classes.
require __DIR__ . '/src/PingUsageController.php';
require __DIR__ . '/src/UploadFilesController.php';

// Register the submenu page item.
function mediaflow_register_menu_page(): void
{
    add_submenu_page(
        'options-general.php',
        'Mediaflow',
        'Mediaflow',
        'manage_options',
        'mediaflow',
        'mediaflow_settings_page'
    );
}

// Render the settings page.
function mediaflow_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    require __DIR__ . '/resources/views/settings.php';
}

// Register the settings, each setting has its own callback function which prints the field.
function mediaflow_register_settings(): void
{
    register_setting('mediaflow', 'mediaflow');

    add_settings_section(
        'mediaflow_settings',
        '',
        '__return_null',
        'mediaflow'
    );

    add_settings_field(
        'mediaflow_client_id',
        'Client ID',
        'mediaflow_client_id',
        'mediaflow',
        'mediaflow_settings'
    );

    add_settings_field(
        'mediaflow_client_secret',
        'Client secret',
        'mediaflow_client_secret',
        'mediaflow',
        'mediaflow_settings'
    );

    add_settings_field(
        'mediaflow_refresh_token',
        'Refresh token (ServerKey)',
        'mediaflow_refresh_token',
        'mediaflow',
        'mediaflow_settings'
    );

    add_settings_field(
        'mediaflow_force_alt_text',
        'Force alternative text',
        'mediaflow_force_alt_text',
        'mediaflow',
        'mediaflow_settings'
    );
}

// Print input settings field.
function mediaflow_input_field(string $option, string $description): void
{
    $value = mediaflow_has_env() ? '' : mediaflow_get_option($option);

    printf(
        '<input name="mediaflow[%s]" class="large-text" type="text" value="%s" %s/>',
        $option,
        $value,
        mediaflow_has_env() ? 'disabled' : ''
    );

    printf('<p class="description">%s</p>', $description);
}

// Register the client id settings field.
function mediaflow_client_id(): void
{
    mediaflow_input_field('client_id', 'The client ID provided by Mediaflow.');
}

// Register the client secret settings field.
function mediaflow_client_secret(): void
{
    mediaflow_input_field(
        'client_secret',
        'The client secret provided by Mediaflow.'
    );
}

// Register the refresh token settings field.
function mediaflow_refresh_token(): void
{
    mediaflow_input_field(
        'refresh_token',
        'The refresh token provided by Mediaflow.'
    );
}

// Register the force alt settings field.
function mediaflow_force_alt_text(): void
{
    printf(
        '<fieldset><label><input name="mediaflow[force_alt_text]" type="checkbox" value="1" %s %s />Force user\'s to provide an alt text on images in Mediaflow file selector.</label></fieldset>',
        mediaflow_get_option('force_alt_text') ? 'checked' : '',
        mediaflow_has_env() ? 'disabled' : ''
    );
}

// Get the settings page url.
function mediaflow_get_settings_url(): string
{
    return esc_url(
        add_query_arg('page', 'mediaflow', get_admin_url() . 'admin.php')
    );
}

// Add settings page link to the plugin list.
function mediaflow_settings_link(array $links): array
{
    $url = mediaflow_get_settings_url();

    $links[] = sprintf('<a href="%s">%s</a>', $url, __('Settings'));

    return $links;
}

// This function checks if the plugin has been configured with environment variables.
function mediaflow_has_env(): bool
{
    $keys = [
        'MEDIAFLOW_CLIENT_ID',
        'MEDIAFLOW_CLIENT_SECRET',
        'MEDIAFLOW_FORCE_ALT_TEXT',
        'MEDIAFLOW_REFRESH_TOKEN',
    ];

    foreach ($keys as $key) {
        if (array_key_exists($key, $_ENV)) {
            return true;
        }
    }

    return false;
}

// This function retrieves the settings from the environment variables.
function mediaflow_get_env(string $key)
{
    $variable =
        [
            'client_id' => 'MEDIAFLOW_CLIENT_ID',
            'client_secret' => 'MEDIAFLOW_CLIENT_SECRET',
            'force_alt_text' => 'MEDIAFLOW_FORCE_ALT_TEXT',
            'refresh_token' => 'MEDIAFLOW_REFRESH_TOKEN',
        ][$key] ?? null;

    return $_ENV[$variable] ?? null;
}

// Helper function to get the settings from the database.
function mediaflow_get_option(string $key)
{
    if ($value = mediaflow_get_env($key)) {
        return $value;
    }

    $options = get_option('mediaflow');

    return isset($options[$key]) ? esc_attr($options[$key]) : null;
}

// Get the plugin version number.
function mediaflow_get_plugin_version(): string
{
    return get_plugin_data(__FILE__)['Version'];
}

// Get the locale set in WordPress.
function mediaflow_get_locale(): string
{
    return get_locale() === 'sv_SE' ? 'sv_SE' : 'en_US';
}

// Get an access token from Mediaflow API.
function mediaflow_get_access_token(): ?string
{
    // Check if the access token is already stored in the transient.
    if ($token = get_transient('mediaflow_access_token')) {
        return $token;
    }

    $clientId = mediaflow_get_option('client_id');
    $clientSecret = mediaflow_get_option('client_secret');
    $refreshToken = mediaflow_get_option('refresh_token');

    // Validate the client ID, client secret and refresh token from the setting.
    if (!$clientId || !$clientSecret || !$refreshToken) {
        return null;
    }

    $query = http_build_query([
        'grant_type' => 'refresh_token',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'refresh_token' => $refreshToken,
    ]);

    $response = wp_remote_get(
        "https://customerapi.mediaflowpro.com/1/oauth2/token?$query"
    );

    if (isset($response['body'])) {
        $response = json_decode($response['body'], true);

        if (!isset($response['access_token'])) {
            return null;
        }

        // Store the access token in the transient for 1 hour.
        set_transient(
            'mediaflow_access_token',
            $response['access_token'],
            HOUR_IN_SECONDS
        );

        return $response['access_token'];
    }

    return null;
}

// Delete the access token when the settings are updated.
function mediaflow_delete_access_token()
{
    delete_transient('mediaflow_access_token');
}

// Register and enqueue the scripts and styles.
function mediaflow_enqueue_scripts(): void
{
    $version = mediaflow_get_plugin_version();

    wp_register_script(
        'mediaflow-file-selector',
        'https://mfstatic.com/js/fileselector.min.js',
        [],
        $version,
        true
    );

    wp_enqueue_script(
        'mediaflow',
        plugin_dir_url(__FILE__) . 'resources/js/media-library.js',
        ['mediaflow-file-selector', 'media-views'],
        $version,
        true
    );

    wp_register_style(
        'mediaflow-file-selector',
        'https://mfstatic.com/css/fileselector.min.css',
        [],
        $version
    );

    wp_enqueue_style(
        'mediaflow',
        plugin_dir_url(__FILE__) . 'resources/css/media-library.css',
        ['mediaflow-file-selector'],
        $version
    );

    // Add constants to the window object.
    wp_localize_script('mediaflow', 'mediaflow', [
        'ACCESS_TOKEN' => mediaflow_get_access_token(),
        'FORCE_ALT_TEXT' =>
        mediaflow_get_option('force_alt_text') === '1' ? 'true' : 'false', // WP can't handle booleans in wp_localize_script :(
        'LOCALE' => mediaflow_get_locale(),
        'POST_ID' => get_post()->ID ?? null,
        'REST_API_URL' => get_rest_url(null, 'mediaflow/'),
        'SETTINGS_URL' => mediaflow_get_settings_url(),
        'USER' => wp_get_current_user()->data->display_name,
        'WP_NONCE' => wp_create_nonce('wp_rest'),
    ]);
}

// Register API endpoint.
function mediaflow_register_api_endpoints(): void
{
    $routes = [
        'files' => Mediaflow\UploadFilesController::class,
        'usages' => Mediaflow\PingUsageController::class,
    ];

    foreach ($routes as $route => $controller) {
        register_rest_route('mediaflow', $route, [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => new $controller(),
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);
    }
}

// Register Gutenberg block types.
function mediaflow_register_blocks()
{
    register_block_type(__DIR__ . '/build/video');
}

// Initialize the plugin when it is activated.
function mediaflow_plugin_bootstrap()
{
    add_action('admin_init', 'mediaflow_register_settings');
    add_action('admin_menu', 'mediaflow_register_menu_page');
    add_action('admin_enqueue_scripts', 'mediaflow_enqueue_scripts');
    add_action('rest_api_init', 'mediaflow_register_api_endpoints');
    add_filter(
        'plugin_action_links_mediaflow/mediaflow.php',
        'mediaflow_settings_link'
    );
    add_action('init', 'mediaflow_register_blocks');
    add_action('update_option_mediaflow', 'mediaflow_delete_access_token');
}

// Load the plugin.
add_action('plugins_loaded', 'mediaflow_plugin_bootstrap');

// Clear the permalinks after the post type has been registered.
function mediaflow_plugin_activate()
{
    flush_rewrite_rules();
}

// Activate the plugin.
register_activation_hook(__FILE__, 'mediaflow_plugin_activate');
