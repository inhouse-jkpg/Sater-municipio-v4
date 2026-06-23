# Network SAML Options

This WordPress plugin adds SAML (Security Assertion Markup Language) options to all sites in a multisite network via WP-CLI.  It allows you to easily propagate SAML configuration from a single source site to all other sites in your network, ensuring consistent settings across your multisite installation. This is particularly useful when using a plugin like miniOrange SAML SSO.

## Description

The Network SAML Options plugin simplifies the management of SAML settings in a WordPress multisite environment.  Instead of configuring SAML options individually for each site, you can configure them once on a designated source site (usually the main site) and then use the WP-CLI command to propagate those settings to all other sites in the network.  This saves significant time and effort, especially when dealing with a large number of sites.

## Installation

1. Upload the plugin files to the `/wp-content/mu-plugins/` directory, or install the plugin through the WordPress plugins screen.

## Usage

This plugin is designed to be used with WP-CLI.  After activating the plugin, you can use the following command:

wp network-saml-options update [--source-site=&lt;site_id&gt;]

### Options

*   `--source-site=&lt;site_id&gt;`: (Optional) The ID of the site to fetch the SAML option values from. If not specified, the main site (ID 1) will be used as the source.

### Examples

*   Update SAML options for all sites, using the main site as the source:

wp network-saml-options update

*   Update SAML options for all sites, using site ID 5 as the source:

wp network-saml-options update --source-site=5

## Plugin Functionality

The plugin works by:

1.  Identifying the configured SAML option keys (defined in the `$optionKeys` array within the plugin).  These are the specific options you want to propagate.
2.  Retrieving the values of these options from the specified source site.
3.  Iterating through all sites in the network.
4.  For each site, switching to that site's context.
5.  Updating the SAML options on the current site with the values from the source site.
6.  Restoring the original site context.

## Supported SAML Options

The plugin propagates the following SAML options (these are the defaults, you can modify the `$optionKeys` array in the plugin file if needed):

*   `mo_saml_test_config_attrs`
*   `MO_SAML_TEST_STATUS`
*   `MO_SAML_TEST`
*   `mo_saml_required_certificate`
*   `MO_SAML_RESPONSE`
*   `MO_SAML_REQUEST`
*   `mo_saml_message`
*   `mo_saml_add_sso_button_wp`
*   `mo_saml_assertion_time_validity`
*   `mo_saml_encoding_enabled`
*   `saml_x509_certificate`
*   `saml_issuer`
*   `saml_login_url`
*   `saml_identity_name`
*   `mo_is_new_user`
*   `widget_saml_login_widget`
*   `mo_saml_keep_settings_on_deletion`

## Important Notes

*   This plugin requires WP-CLI to function.
*   It's crucial to back up your database before running the `wp network-saml-options update` command, as changes will be made to all sites in your network.
*   Ensure that the SAML plugin you're using (e.g., miniOrange SAML SSO) is compatible with this propagation method.  It relies on the standard WordPress `get_option()` and `update_option()` functions.
*   If you need to propagate additional SAML options, you'll need to add their corresponding keys to the `$optionKeys` array in the plugin file.

## Author

Sebastian Thulin

## Version

1.0.0