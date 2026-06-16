<?php
/**
 * Plugin Name: Säter Kontakter v2 display
 * Description: Visar jobbtitel före förvaltning/sektor på egna rader i Modularity Kontakter v2.
 * Version: 1.0.1
 * Author: Säter kommun
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SATER_CONTACTS_V2_VIEWS_DIR = __DIR__ . '/views';

add_filter('/Modularity/externalViewPath', 'sater_contacts_v2_external_view_paths', 10, 1);
add_action('wp_enqueue_scripts', 'sater_contacts_v2_enqueue_assets', 100);

function sater_contacts_v2_enqueue_assets(): void
{
    $cssPath = __DIR__ . '/assets/contacts.css';

    if (!file_exists($cssPath)) {
        return;
    }

    wp_enqueue_style(
        'sater-contacts-v2-display',
        plugin_dir_url(__FILE__) . 'assets/contacts.css',
        [],
        (string) filemtime($cssPath)
    );
}

/**
 * Prioritize Säter cards view; fall back to Modularity for component partials.
 *
 * @param array<string, string|array<int, string>> $paths
 * @return array<string, string|array<int, string>>
 */
function sater_contacts_v2_external_view_paths(array $paths): array
{
    if (!defined('MODULARITY_PATH')) {
        return $paths;
    }

    $modularityViews = MODULARITY_PATH . 'source/php/Module/Contacts/views';

    // Blade prepends paths in array order; the last entry is searched first.
    // Put Modularity first so Säter's cards.blade.php wins, with Modularity as fallback for components.*.
    $paths['mod-contacts'] = [
        $modularityViews,
        SATER_CONTACTS_V2_VIEWS_DIR,
    ];

    return $paths;
}
