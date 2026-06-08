<?php
/**
 * Plugin Name: Säter Kontakter v2 display
 * Description: Visar jobbtitel före förvaltning/sektor på egna rader i Modularity Kontakter v2.
 * Version: 1.0.0
 * Author: Säter kommun
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SATER_CONTACTS_V2_VIEWS_DIR = __DIR__ . '/views';

add_filter('/Modularity/externalViewPath', 'sater_contacts_v2_external_view_paths', 10, 1);

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

    $paths['mod-contacts'] = [
        SATER_CONTACTS_V2_VIEWS_DIR,
        $modularityViews,
    ];

    return $paths;
}
