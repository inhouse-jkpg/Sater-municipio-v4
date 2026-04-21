<?php

/*
Plugin Name: Network SAML Options
Description: Adds SAML options to all sites in a multisite network via WP-CLI.
Version: 1.0.0
Author: Sebastian Thulin
*/

namespace WpmuPropagateMiniOrangeSamlSsoSettings;

use WP_CLI;

class WpmuPropagateMiniOrangeSamlSsoSettings
{

    /**
     * The option keys to propagate.
     * @var array
     */
    private $optionKeys = [
        'mo_saml_test_config_attrs',
        'MO_SAML_TEST_STATUS',
        'MO_SAML_TEST',
        'mo_saml_required_certificate',
        'MO_SAML_RESPONSE',
        'MO_SAML_REQUEST',
        'mo_saml_message',
        'mo_saml_add_sso_button_wp',
        'mo_saml_assertion_time_validity',
        'mo_saml_encoding_enabled',
        'saml_x509_certificate',
        'saml_issuer',
        'saml_login_url',
        'saml_identity_name',
        'mo_is_new_user',
        'widget_saml_login_widget',
        'mo_saml_keep_settings_on_deletion',
    ];

    /**
     * Initializes the class and registers the WP-CLI command.
     */
    public function __construct()
    {
        if (defined('WP_CLI') && WP_CLI) {
          WP_CLI::add_command('network-saml-options', [$this, 'update']);
        }
    }

    /**
     * Retrieves the main site ID.
     * 
     * @return int The main site ID.
     */
    private function getMainSiteId(): int
    {
        return get_main_site_id();
    }

    /**
     * Retrieves all sites in the network.
     * 
     * @return array An array of site objects.
     */
    private function getSites(): array
    {
        return get_sites([
            'fields' => 'ids',
            'number' => PHP_INT_MAX,
        ]);
    }

    /**
     * Retrieves SAML options from the source site.
     * 
     * @param int $sourceSiteId The source site ID.
     * @return array An associative array of options.
     */
    private function getSAMLSettings(int $sourceSiteId): array
    {
        switch_to_blog($sourceSiteId);
        $options = [];
        foreach ($this->optionKeys as $key) {
            $options[$key] = get_option($key);
        }
        restore_current_blog();
        return $options;
    }

    /**
     * Updates SAML options for all sites in the network.
     * 
     * ## OPTIONS
     *
     * [--source-site=<site_id>]
     * : Site ID to fetch the option values from. Defaults to the main site.
     *
     * ## EXAMPLES
     *
     *     wp network-saml-options update --source-site=1
     *
     * @param array $args WP-CLI positional arguments.
     * @param array $assocArgs WP-CLI associative arguments.
     */
    public function update($args, $assocArgs)
    {
        $sourceSiteId = isset($assocArgs['source-site']) ? intval($assocArgs['source-site']) : $this->getMainSiteId();
        $options = $this->getSAMLSettings($sourceSiteId);
        $sites = $this->getSites();

        WP_CLI::log("Updating SAML options for " . count($sites) . " sites...");

        foreach ($sites as $siteId) {
            WP_CLI::log("Updating site ID: $siteId...");
            switch_to_blog($siteId);
            foreach ($options as $key => $value) {
                if (!empty($value)) {
                    update_option($key, $value);
                }
            }
            restore_current_blog();
            WP_CLI::success("Updated options for site ID: $siteId");
        }

        WP_CLI::success("SAML options updated across all network sites.");
    }
}

new \WpmuPropagateMiniOrangeSamlSsoSettings\WpmuPropagateMiniOrangeSamlSsoSettings();