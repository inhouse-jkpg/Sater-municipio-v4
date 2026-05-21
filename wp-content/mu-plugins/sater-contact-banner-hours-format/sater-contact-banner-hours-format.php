<?php
/**
 * Plugin Name: Sater Contact Banner Hours Format
 * Description: Use en-dash without spaces between opening hours in the contact banner.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('Modularity/Block/acf/contact-banner/Data', 'sater_format_contact_banner_hours', 10, 1);
add_filter('Modularity/Display/mod-contact-banner/viewData', 'sater_format_contact_banner_hours', 10, 1);

/**
 * @param array<string, mixed> $viewData
 * @return array<string, mixed>
 */
function sater_format_contact_banner_hours(array $viewData): array
{
    if (empty($viewData['openHours']) || !is_array($viewData['openHours'])) {
        return $viewData;
    }

    foreach ($viewData['openHours'] as $index => $line) {
        if (!is_string($line)) {
            continue;
        }

        $viewData['openHours'][$index] = sater_normalize_contact_banner_time_dashes($line);
    }

    return $viewData;
}

function sater_normalize_contact_banner_time_dashes(string $html): string
{
    // ContactBanner.php: "08.00 — 16.15" (em dash with spaces)
    $html = str_replace(' — ', '–', $html);

    // Weekdays field may still use "12.00 - 13.00" (hyphen with spaces)
    $html = preg_replace('/(\d{1,2}\.\d{2})\s+-\s+(\d{1,2}\.\d{2})/u', '$1–$2', $html) ?? $html;

    return $html;
}
