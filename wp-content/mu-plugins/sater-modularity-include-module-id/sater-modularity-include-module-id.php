<?php
/**
 * Plugin Name: Säter Modularity: Include module by ID
 * Description: Adds a Modularity module that renders another module by ID (safe, single ID).
 * Version: 1.0.0
 * Author: Municipio SE
 * License: MIT
 *
 * @category   WordPress
 * @package    Sater
 * @author     Municipio SE <dev@municipio.se>
 * @license    MIT https://opensource.org/licenses/MIT
 * @link       https://sater.se
 * @since      1.0.0
 * @phpVersion 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the custom module folder with Modularity.
 *
 * Modularity expects an array: [ <folderPath> => <ModuleClassName> ].
 */
add_filter(
    'Modularity/Modules',
    static function (array $modules): array {
        $modules[__DIR__ . '/modules/IncludeModuleId'] = 'IncludeModuleId';
        return $modules;
    }
);

/**
 * Tell Modularity where to find our view files.
 *
 * Without this, Modularity only scans its own Module/ directory and
 * throws "View [includemoduleid] not found" for external modules.
 */
add_filter(
    '/Modularity/externalViewPath',
    static function (array $paths): array {
        $viewDir = __DIR__ . '/modules/IncludeModuleId/views';
        // Must match the CPT Modularity registers (prefixSlug truncates to 20 chars).
        $canonical = \Modularity\ModuleManager::prefixSlug('includemoduleid');
        $paths[$canonical] = $viewDir;
        // Legacy or inconsistent DB: full-length post_type before truncation.
        $untruncated = 'mod-includemoduleid';
        if ($untruncated !== $canonical) {
            $paths[$untruncated] = $viewDir;
        }
        return $paths;
    }
);

require_once __DIR__ . '/acf-fields.php';

