<?php
/**
 * Plugin Name: Advanced Permissions for Gravity Forms
 * Plugin URI: http://cosmicgiant.com/plugins/advanced-permissions/
 * Description: Restrict Gravity Forms user access with granular, form-level permissions
 * Version: 3.1
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: CosmicGiant
 * Author URI: http://cosmicgiant.com
 * License: GPL-3.0+
 * Text Domain: forgravity_advancedpermissions
 * Domain Path: /languages
 *
 * ------------------------------------------------------------------------
 * Copyright 2019 ForGravity.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses.
 *
 * @package CosmicGiant\Advanced_Permissions
 **/

if ( ! defined( 'CG_EDD_STORE_URL' ) ) {
	define( 'CG_EDD_STORE_URL', 'https://cosmicgiant.com' );
}

define( 'CG_ADVANCEDPERMISSIONS_VERSION', '3.1' );
define( 'CG_ADVANCEDPERMISSIONS_EDD_ITEM_ID', 7416 );
define( 'CG_ADVANCEDPERMISSIONS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Initialize the autoloader.
require_once 'includes/autoload.php';
require_once 'includes/vendor/autoload.php';

// If Gravity Forms is loaded, bootstrap the Advanced Permissions Add-On.
add_action( 'gform_loaded', [ 'AdvancedPermissions_Bootstrap', 'load' ], 5 );

// Redirect main Entries page link.
add_action( 'gform_loaded', [ '\CosmicGiant\Advanced_Permissions\Advanced_Permissions', 'maybe_redirect_entries_page' ], 6 );

/**
 * Class AdvancedPermissions_Bootstrap
 *
 * Handles the loading of the Advanced Permissions Add-On and registers with the Add-On framework.
 */
class AdvancedPermissions_Bootstrap {

	/**
	 * If the Add-On Framework exists, Advanced Permissions Add-On is loaded.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		GFAddOn::register( '\CosmicGiant\Advanced_Permissions\Advanced_Permissions' );

	}

}

/**
 * Returns an instance of the Advanced_Permissions class
 *
 * @see    Advanced_Permissions::get_instance()
 *
 * @return CosmicGiant\Advanced_Permissions\Advanced_Permissions
 */
function advancedpermissions() {

	return CosmicGiant\Advanced_Permissions\Advanced_Permissions::get_instance();

}

/**
 * Returns an instance of the Advanced_Permissions class
 *
 * @deprecated Use advancedpermissions().
 *
 * @return CosmicGiant\Advanced_Permissions\Advanced_Permissions
 */
function fg_advancedpermissions() {

	return advancedpermissions();

}
