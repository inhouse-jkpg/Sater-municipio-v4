<?php
/**
 * Advanced Permissions class instance.
 *
 * @since   1.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions;

use CosmicGiant\Plugin_Skeleton\GF_Addon\Abstracts\Addon as GFAddOn;
use GFCommon;
use GFForms;
use GFFormsModel;
use GFFormDisplay;
use GFFormSettings;
use GFAPI;
use Ramsey\Uuid\Uuid;
use WP_User;

GFForms::include_addon_framework();

/**
 * Advanced Permissions for Gravity Forms.
 *
 * @since     1.0
 * @author    CosmicGiant
 * @copyright Copyright (c) 2018, Travis Lopes
 */
class Advanced_Permissions extends GFAddOn {

	const ROLE_FORM_CREATOR = 'advancedpermissions-form-creator';

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    Advanced_Permissions $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of Advanced Permissions for Gravity Forms.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from advancedpermissions.php
	 */
	protected $_version = CG_ADVANCEDPERMISSIONS_VERSION;

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'forgravity-advancedpermissions';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'forgravity-advancedpermissions/advancedpermissions.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = CG_ADVANCEDPERMISSIONS_PLUGIN_BASENAME;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'https://forgravity.com/plugins/advanced-permissions/';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Advanced Permissions for Gravity Forms';

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Advanced Permissions';

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string $_capabilities_plugin_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_plugin_page = 'forgravity_advancedpermissions';

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'forgravity_advancedpermissions';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'forgravity_advancedpermissions_uninstall';

	/**
	 * Defines the capabilities needed for Advanced Permissions.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string[] $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = [ 'forgravity_advancedpermissions', 'forgravity_advancedpermissions_uninstall' ];

	/**
	 * The current user.
	 *
	 * @since 2.0
	 *
	 * @var User|null $current_user The User object.
	 */
	protected static $current_user;

	/**
	 * The REST API instances.
	 *
	 * @since 3.0
	 *
	 * @var Controllers\Base[]
	 */
	private $rest_api = [];

	/**
	 * Classes responsible for view handling.
	 *
	 * @since 3.0
	 *
	 * @var Views\Base[]
	 */
	private $views = [];

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 *
	 * @return Advanced_Permissions
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			$instance = new self();

			$instance->rest_api['form']  = new Controllers\Form_Permissions();
			$instance->rest_api['entry'] = new Controllers\Entry_Permissions();
			$instance->rest_api['users'] = new Controllers\Users();

			$instance->views['entries'] = new Views\Entries();
			$instance->views['forms']   = new Views\Forms();

			self::$_instance = $instance;
		}

		return self::$_instance;

	}

	/**
	 * Register needed actions.
	 *
	 * @since  1.0
	 */
	public function pre_init() {

		parent::pre_init();

		if ( defined( 'REST_REQUEST' ) || ( isset( $_SERVER['REQUEST_URI'] ) && strpos( wp_unslash( $_SERVER['REQUEST_URI'] ), rest_get_url_prefix() ) !== false ) ) {
			return;
		}

		// Fix Form Settings page not appearing when Members is active.
		add_filter( 'user_has_cap', [ $this, 'filter_user_has_cap_members' ], 10, 3 );

		if ( ! $this->get_current_user()->is_immune() ) {

			// Prevent unallowed form list actions.
			add_action( 'admin_init', [ $this, 'maybe_apply_form_list_permissions' ] );

			$this->add_filter( 'user_has_cap' );

		}

		// Import/Export.
		add_filter( 'gform_export_form', [ $this, 'filter_gform_export_form' ] );
		add_action( 'gform_forms_post_import', [ $this, 'action_gform_forms_post_import' ] );
		add_filter( 'gform_export_entries_forms', [ $this, 'filter_gform_export_entries_forms' ] );

		// Duplication.
		add_action( 'gform_post_form_duplicated', [ $this, 'action_gform_post_form_duplicated' ], 10, 2 );

	}

	/**
	 * Register needed actions.
	 *
	 * @since  1.0
	 */
	public function init() {

		parent::init();

		// Register REST API hooks.
		foreach ( $this->rest_api as $rest_controller ) {
			$rest_controller->add_hooks();
		}

		// Register view hooks.
		foreach ( $this->views as $view_class ) {
			$view_class->add_hooks();
		}

		// Track the form creator.
		add_action( 'gform_after_save_form', [ $this, 'action_gform_after_save_form' ], 10, 2 );

		// Delete rules when deleting form.
		add_action( 'gform_after_delete_form', [ $this, 'action_gform_after_delete_form' ] );

		add_action( 'wp_before_admin_bar_render', [ '\CosmicGiant\Advanced_Permissions\Admin_Bar', 'filter_admin_bar' ], 11 );

		add_action( 'admin_menu', [ $this, 'action_admin_menu' ], 11 );

		add_filter( 'gform_rule_source_value', [ $this, 'filter_gform_rule_source_value' ], 10, 5 );
		add_filter( 'gform_is_value_match', [ $this, 'filter_gform_is_value_match' ], 10, 6 );

	}

	/**
	 * Register needed actions.
	 *
	 * @since  1.0
	 */
	public function init_admin() {

		parent::init_admin();

		// Load GFSettings if not already loaded (fix for Gravity Forms 2.5).
		if ( GFForms::get_page() && ! class_exists( 'GFSettings' ) ) {
			require_once( GFCommon::get_base_path() . '/settings.php' );
		}

	}

	/**
	 * Define minimum requirements needed.
	 *
	 * @since 3.1
	 *
	 * @return array
	 */
	public function minimum_requirements() {

		return [ 'php' => [ 'version' => '7.0' ] ];

	}

	/**
	 * Enqueue needed scripts.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function scripts() {

		// Get minification string.
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$scripts = [
			[
				'handle'  => 'gform_settings_tabs',
				'src'     => GFCommon::get_base_url() . "/includes/settings/js/tabs{$min}.js",
				'version' => GFForms::$version,
			],
			[
				'handle'   => 'advancedpermissions-form_permissions',
				'deps'     => [ 'gform_settings_tabs', 'wp-api-fetch', 'wp-data', 'wp-element', 'wp-i18n' ],
				'src'      => $this->get_base_url() . "/dist/js/form-permissions{$min}.js",
				'version'  => $min ? filemtime( $this->get_base_path() . '/dist/js/form-permissions.js' ) : $this->get_version(),
				'enqueue'  => [
					[
						'admin_page' => [ 'form_settings' ],
						'tab'        => $this->get_slug(),
					],
					function () {
						return $this->is_plugin_page() && $this->get_current_subview() === 'defaults';
					},
				],
				'callback' => [ $this, 'localize_form_settings_scripts' ],
			],
			[
				'handle'   => 'advancedpermissions-entry_permissions',
				'deps'     => [ 'gform_form_admin', 'gform_settings_tabs', 'wp-api-fetch', 'wp-data', 'wp-element', 'wp-i18n' ],
				'src'      => $this->get_base_url() . "/dist/js/entry-permissions{$min}.js",
				'version'  => $min ? filemtime( $this->get_base_path() . '/dist/js/entry-permissions.js' ) : $this->get_version(),
				'enqueue'  => [
					[
						'admin_page' => [ 'form_settings' ],
						'tab'        => $this->get_slug(),
					],
				],
				'callback' => [ $this, 'localize_form_settings_scripts' ],
			],
			[
				'handle'   => 'advancedpermissions-conditional_logic_form_editor',
				'deps'     => [ 'jquery', 'wp-i18n' ],
				'src'      => $this->get_base_url() . "/dist/js/conditional-logic/form-editor{$min}.js",
				'version'  => $min ? filemtime( $this->get_base_path() . '/dist/js/conditional-logic/form-editor.js' ) : $this->get_version(),
				'enqueue'  => [ [ 'admin_page' => [ 'form_editor' ] ] ],
				'callback' => [ $this, 'localize_conditional_logic_admin_scripts' ],
			],
			[
				'handle'   => 'advancedpermissions-conditional_logic_form_settings',
				'deps'     => [ 'jquery', 'wp-i18n' ],
				'src'      => $this->get_base_url() . "/dist/js/conditional-logic/form-settings{$min}.js",
				'version'  => $min ? filemtime( $this->get_base_path() . '/dist/js/conditional-logic/form-settings.js' ) : $this->get_version(),
				'enqueue'  => [ [ 'admin_page' => [ 'form_settings' ] ] ],
				'callback' => [ $this, 'localize_conditional_logic_admin_scripts' ],
			],
			[
				'handle'   => 'advancedpermissions-conditional_logic_frontend',
				'deps'     => [ 'jquery', 'wp-i18n' ],
				'src'      => $this->get_base_url() . "/dist/js/conditional-logic/frontend{$min}.js",
				'version'  => $min ? filemtime( $this->get_base_path() . '/dist/js/conditional-logic/frontend.js' ) : $this->get_version(),
				'callback' => [ $this, 'localize_conditional_logic_frontend_scripts' ],
				'enqueue'  => [
					function ( $form ) {
						return class_exists( 'GFFormDisplay' ) && GFFormDisplay::has_conditional_logic( $form );
					},
				],
			],
		];

		return array_merge( parent::scripts(), $scripts );

	}

	/**
	 * Localize form settings scripts.
	 *
	 * @since  1.0
	 */
	public function localize_form_settings_scripts() {

		global $wp_scripts;

		$scripts_to_localize = [ 'advancedpermissions-form_permissions', 'advancedpermissions-entry_permissions' ];

		foreach ( $scripts_to_localize as $script ) {
			if ( $wp_scripts->get_data( $script, 'data' ) ) {
				return;
			}
		}

		wp_localize_script(
			$scripts_to_localize[0],
			'permissions',
			[
				'endpoints' => [
					'entry' => 'permissions/entry',
					'form'  => 'permissions/form',
					'users' => 'permissions/users',
				],
				'formId'    => rgget( 'id' ) ? (int) $_GET['id'] : 'default', // phpcs:ignore
				'groups'    => array_values( $this->get_capability_groups() ),
				'roles'     => $this->get_roles_as_options(),
			]
		);

	}

	/**
	 * Localize form settings scripts.
	 *
	 * @since  3.0
	 */
	public function localize_conditional_logic_admin_scripts() {

		$scripts_to_localize = [ 'advancedpermissions-conditional_logic_form_editor', 'advancedpermissions-conditional_logic_form_settings' ];

		foreach ( $scripts_to_localize as $script ) {
			wp_localize_script(
				$script,
				'permissions',
				[ 'roles' => $this->get_roles_as_options( true ) ]
			);
		}

	}

	/**
	 * Localize form settings scripts.
	 *
	 * @since  3.0
	 */
	public function localize_conditional_logic_frontend_scripts() {

		wp_localize_script(
			'advancedpermissions-conditional_logic_frontend',
			'permissions',
			[
				'endpoints' => [
					'user' => get_rest_url( get_current_blog_id(), 'permissions/users/me' ),
				],
			]
		);

	}

	/**
	 * Enqueue needed stylesheets.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function styles() {

		// Get minification string.
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$styles = [
			[
				'handle'  => 'advancedpermissions-common',
				'src'     => $this->get_base_url() . "/dist/css/common{$min}.css",
				'version' => $min ? filemtime( $this->get_base_path() . '/dist/css/common.css' ) : $this->get_version(),
				'enqueue' => [],
			],
			[
				'handle'  => 'advancedpermissions-entry_permissions',
				'src'     => $this->get_base_url() . "/dist/css/entry-permissions{$min}.css",
				'deps'    => [ 'advancedpermissions-common' ],
				'version' => $min ? filemtime( $this->get_base_path() . '/dist/css/entry-permissions.css' ) : $this->get_version(),
				'enqueue' => [ [ 'admin_page' => [ 'form_settings' ] ] ],
			],
			[
				'handle'  => 'advancedpermissions-form_permissions',
				'src'     => $this->get_base_url() . "/dist/css/form-permissions{$min}.css",
				'deps'    => [ 'advancedpermissions-common' ],
				'version' => $min ? filemtime( $this->get_base_path() . '/dist/css/form-permissions.css' ) : $this->get_version(),
				'enqueue' => [
					[ 'admin_page' => [ 'form_settings' ] ],
					function () {
						return $this->is_plugin_page() && $this->get_current_subview() === 'defaults';
					},
				],
			],
			[
				'handle'  => 'advancedpermissions-admin',
				'src'     => $this->get_base_url() . "/dist/css/admin{$min}.css",
				'version' => $min ? filemtime( $this->get_base_path() . '/dist/css/admin.css' ) : $this->get_version(),
				'enqueue' => [
					[
						'admin_page' => [
							'form_editor',
							'form_list',
							'form_settings',
							'entry_list',
							'entry_view',
							'entry_edit',
							'plugin_page',
							'results',
						],
					],
				],
			],
			[
				'handle'  => 'forgravity_dashicons',
				'src'     => $this->get_base_url() . '/dist/css/dashicons.css',
				'version' => $this->get_version(),
				'enqueue' => [ [ 'query' => 'page=roles&action=edit' ] ],
			],
		];

		return array_merge( parent::styles(), $styles );

	}





	// # FORM SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Render the form settings page.
	 *
	 * @since  1.0
	 *
	 * @param array $form The current Form object.
	 */
	public function form_settings( $form ) {

		// If current user is not an administrator, return.
		if ( ! $this->get_current_user()->is_immune() ) {
			return;
		}

		?>

		<div class="gform_settings_form">

			<input type="hidden" name="gform_settings_tab" />

			<nav class="gform-settings-tabs__navigation" role="tablist">
				<a href="#" role="tab" aria-selected="false" id="gform-settings-tab-form-permissions" data-tab="form-permissions" class="active">
					<?php esc_html_e( 'Form Permissions', 'forgravity_advancedpermissions' ); ?>
				</a>
				<a href="#" role="tab" aria-selected="true" id="gform-settings-tab-entry-permissions" data-tab="entry-permissions">
					<?php esc_html_e( 'Entry Permissions', 'forgravity_advancedpermissions' ); ?>
				</a>
			</nav>

			<div id="form-permissions" class="gform-settings-tabs__container active" role="tabpanel" aria-hidden="true" data-tab="form-permissions" aria-labelledby="gform-settings-tab-form-permissions"></div>
			<div id="entry-permissions" class="gform-settings-tabs__container" role="tabpanel" aria-hidden="false" data-tab="entry-permissions" aria-labelledby="gform-settings-tab-entry-permissions"></div>

		</div>

		<?php

		printf(
			'<script type="text/javascript">var form = %1$s, entry_meta = %3$s; %2$s</script>',
			wp_json_encode( $form ),
			GFFormSettings::output_field_scripts( false ), // phpcs:ignore
			wp_json_encode( GFFormsModel::get_entry_meta( $form['id'] ) )
		);

	}

	/**
	 * Add the form settings tab.
	 *
	 * @since  1.0
	 *
	 * @param array $tabs    The settings tabs.
	 * @param int   $form_id The ID of the form being accessed.
	 *
	 * @return array
	 */
	public function add_form_settings_menu( $tabs, $form_id ) {

		if ( ! $this->get_current_user()->is_immune() ) {
			return $tabs;
		}

		// Add form settings tab.
		$tabs[] = [
			'name'  => $this->get_slug(),
			'label' => esc_html__( 'Permissions', 'forgravity_advancedpermissions' ),
			'icon'  => $this->get_menu_icon(),
		];

		return $tabs;

	}





	// # APPLY PERMISSIONS ---------------------------------------------------------------------------------------------

	/**
	 * Dynamically filter a user’s capabilities to apply permissions rules.
	 *
	 * @since  1.0
	 *
	 * @param array $allcaps An array of all the user's capabilities.
	 * @param array $cap     Actual capabilities for meta capability.
	 * @param array $args    Parameters passed to has_cap(), typically object ID.
	 *
	 * @return array
	 */
	public function filter_user_has_cap( $allcaps, $cap, $args ) {

		// Store all the default WP caps to $default_caps.
		$default_caps = $allcaps;

		// If the requested cap is not gravityforms related, return.
		if ( ! strstr( $args[0], 'gform_' ) && ! strstr( $args[0], 'gravity' ) ) {
			return $default_caps;
		}

		$user = $this->get_current_user();
		if ( ! $user->exists() || $user->is_immune() ) {
			return $default_caps;
		}

		// Get form ID.
		$form_id = false;
		if ( rgget( 'id' ) ) {
			$form_id = rgget( 'id' );
		} else if ( rgpost( 'id' ) && 'delete-gf_entry' !== rgpost( 'action' ) ) {
			$form_id = rgpost( 'id' );
		} else if ( 'delete-gf_entry' === rgpost( 'action' ) ) {
			$entry   = GFAPI::get_entry( rgpost( 'entry' ) );
			$form_id = $entry['form_id'];
		} else if ( 'gf_process_export' === rgpost( 'action' ) && rgpost( 'export_form' ) ) {
			$form_id = rgpost( 'export_form' );
		} else if ( rgpost( 'action' ) && in_array( rgpost( 'action' ), [ 'rg_update_form_active', 'rg_update_notification_active', 'rg_update_confirmation_active' ] ) ) {
			$form_id = rgpost( 'form_id' );
		}

		// Collect all customized caps.
		if ( ! GFCommon::is_preview() && $user->can_see_menu() ) {
			$allcaps = array_merge( $allcaps, $user->get_all_customized_caps() );

			// Add the gravityforms_edit_forms cap if it's not available, so we can generate submenus.
			if ( ! rgar( $allcaps, 'gravityforms_edit_forms' ) && rgget( 'subview' ) !== 'export_form' ) {
				$allcaps['gravityforms_edit_forms'] = true;
			}
		}

		// If form ID is not set, return.
		if ( ! $form_id && ( ! is_admin() || ! GFCommon::is_preview() ) ) {
			return $allcaps;
		}

		// Get capabilities for form.
		$permissions = Models\Form_Permissions::get( $form_id );
		$caps        = $permissions->get_capabilities( $allcaps, $user );

		// If no capabilities are set, return the default WP capabilities, but not $allcaps that has been modified by AP.
		if ( $caps === false ) {
			return $default_caps;
		}

		// Remove full access capability and merge together rules.
		unset( $allcaps['gform_full_access'] );

		// Update $allcaps with the form caps.
		// @todo doing this makes some of the GF menus cannot be generated in the detail view (Results page etc.), need to refactor the way we generate GF menus.
		$allcaps = array_merge( $allcaps, $caps );

		// Unset other capabilities for preview page.
		if ( GFCommon::is_preview() && false === rgar( $caps, 'gravityforms_preview_forms' ) ) {
			$allcaps['gravityforms_edit_forms'] = $allcaps['gravityforms_create_form'] = false;
		}

		// Disable form editor capability.
		if ( $this->is_form_editor() && ! rgar( $caps, 'gravityforms_edit_forms_fields' ) ) {
			$allcaps['gravityforms_edit_forms'] = false;
		}

		// Disable core form settings capabilities.
		if ( $this->is_form_settings() ) {

			if (
				( $this->is_form_settings( 'confirmation' ) && ! rgar( $caps, 'gravityforms_edit_forms_confirmations' ) ) || // Confirmations.
				( $this->is_form_settings( 'notification' ) && ! rgar( $caps, 'gravityforms_edit_forms_notifications' ) ) || // Notifications.
				( $this->is_form_settings( 'personal-data' ) && ! rgar( $caps, 'gravityforms_edit_forms_settings' ) ) || // Personal Data.
				( $this->is_form_settings( 'settings' ) && ! rgar( $caps, 'gravityforms_edit_forms_settings' ) )    // Settings.
			) {
				$allcaps['gravityforms_edit_forms'] = false;
			}

		}

		// Disable edit form capability when changing form active status.
		if ( 'rg_update_form_active' === rgpost( 'action' ) && false === rgar( $caps, 'gravityforms_edit_forms_settings' ) ) {
			$allcaps['gravityforms_edit_forms'] = false;
		}

		// Disable edit form capability when changing notification active status.
		if ( 'rg_update_notification_active' === rgpost( 'action' ) && false === rgar( $caps, 'gravityforms_edit_forms_notifications' ) ) {
			$allcaps['gravityforms_edit_forms'] = false;
		}

		// Disable edit form capability when changing confirmation active status.
		if ( 'rg_update_confirmation_active' === rgpost( 'action' ) && false === rgar( $caps, 'gravityforms_edit_forms_confirmations' ) ) {
			$allcaps['gravityforms_edit_forms'] = false;
		}

		// Enable form settings capability for Add-On.
		if ( $this->is_form_settings() ) {

			// Loop through Add-Ons, check if form settings page for Add-On.
			foreach ( $this->get_available_addons() as $addon ) {

				// If Add-On capability is not enabled, skip.
				if ( true !== rgar( $caps, $addon['capability'] ) ) {
					continue;
				}

				// If this is the form settings page.
				if ( $this->is_form_settings( $addon['slug'] ) ) {
					$allcaps['gravityforms_edit_forms'] = true;
				}

			}

		}

		// Add gravityforms_view_entries cap when it's a result page.
		if ( $this->is_addon_results_page() ) {
			$addon = str_replace( 'gf_results_gravityforms', '', rgget( 'view' ) );

			if ( rgar( $allcaps, "gravityforms_{$addon}_results" ) ) {
				$allcaps['gravityforms_view_entries'] = true;
			}

		}

		// Add gravityforms_edit_forms cap on the Entries page, so we can still have the Forms submenu rendered.
		if ( rgget( 'page' ) == 'gf_entries' && rgar( $caps, 'gravityforms_view_entries' ) && ! rgar( $caps, 'gravityforms_edit_forms' ) ) {
			$allcaps['gravityforms_edit_forms'] = true;
		}

		// Return the filtered $allcaps.
		return $allcaps;

	}

	/**
	 * Apply permissions against form list actions.
	 *
	 * @since  1.0
	 */
	public function maybe_apply_form_list_permissions() {

		$user = $this->get_current_user();

		// If this is not the form list or an action is not being submitted, exit.
		if ( ! $this->is_form_list() || ! rgpost( 'gforms_update_forms' ) ) {
			return;
		}

		// Get single, bulk actions.
		$single_action = rgpost( 'single_action' );
		$bulk_action   = '-1' === rgpost( 'action2' ) ? rgpost( 'action' ) : rgpost( 'action2' );

		// Handle single action.
		if ( $single_action ) {

			$form_id = rgpost( 'single_action_argument' );

			switch ( $single_action ) {

				case 'delete':
				case 'restore':
				case 'trash':
					$cap_check = 'gravityforms_delete_forms';
					break;

				case 'duplicate':
					$cap_check = 'gravityforms_create_form';
					break;

			}

			// If user does not have capability, remove action from POST.
			if ( isset( $cap_check ) && ! $user->has_form_cap( $cap_check, $form_id ) ) {
				unset( $_POST['single_action'], $_POST['single_action_argument'] ); // phpcs:ignore
			}

		} else if ( '-1' !== $bulk_action ) {

			// Get form IDs.
			$form_ids = (array) rgpost( 'form' );
			$form_ids = array_map( 'absint', $form_ids );

			// Loop through forms, check for capabilities.
			foreach ( $form_ids as $i => $form_id ) {

				switch ( $bulk_action ) {

					case 'delete':
					case 'restore':
					case 'trash':
						$cap_check = 'gravityforms_delete_forms';
						break;

					case 'delete_entries':
						$cap_check = 'gravityforms_delete_entries';
						break;

					case 'duplicate':
						$cap_check = 'gravityforms_create_form';
						break;

				}

				// If user does not have capability, remove action from POST.
				if ( isset( $cap_check ) && ! $user->has_form_cap( $cap_check, $form_id ) ) {
					unset( $_POST['form'][ $i ], $_REQUEST['form'][ $i ] ); // phpcs:ignore
				}

			}

			// If no forms have actions set, reset bulk action parameters.
			if ( empty( $_POST['form'] ) ) { // phpcs:ignore
				$_POST['action'] = $_POST['action2'] = $_REQUEST['action'] = $_REQUEST['action2'] = '-1'; // phpcs:ignore
			}

		}

	}

	/**
	 * Redirects the main Entries page link to the first accessible form.
	 *
	 * @since 1.2
	 */
	public static function maybe_redirect_entries_page() {

		if ( ! defined( 'IS_ADMIN' ) || ! IS_ADMIN ) {
			return;
		}

		// Administrators are exempt from Advanced Permissions. Exit.
		if ( advancedpermissions()->get_current_user()->is_immune() ) {
			return;
		}

		// If this is not the main Entries page, exit.
		if ( rgget( 'page' ) !== 'gf_entries' || isset( $_GET['id'] ) ) {
			return;
		}

		// If Advanced Permissions is not available, exit.
		if ( ! function_exists( 'fg_advancedpermissions' ) || ! advancedpermissions() ) {
			return;
		}

		// Get all forms in alphabetical order.
		$forms = GFFormsModel::get_forms( null, 'title' );
		foreach ( $forms as $i => $form ) {

			$form_caps = Models\Form_Permissions::get( $form->id )->get_capabilities();

			if ( $form_caps ) {
				// If form has defined capabilities, check for view entries capability.
				if ( advancedpermissions()->get_current_user()->can_view_entries( $form->id ) ) {
					if ( $i === 0 ) {
						continue;
					} else {
						wp_safe_redirect( admin_url( 'admin.php?page=gf_entries&id=' . absint( $form->id ) ) );
						die();
					}
				}
			}

		}

	}

	/**
	 * Remove Gravity Forms menu pages user should not have access to.
	 *
	 * @since 2.1
	 */
	public function action_admin_menu() {

		if ( $this->get_current_user()->is_immune() ) {
			return;
		}

		$can_export_entries = false;

		$this->remove_filter( 'user_has_cap' );
		$can_export_forms = current_user_can( 'gravityforms_edit_forms' );
		$can_import_forms = current_user_can( 'gravityforms_create_form' );
		$this->add_filter( 'user_has_cap' );

		foreach ( GFAPI::get_forms( null ) as $form ) {
			if ( $this->get_current_user()->has_form_cap( 'gravityforms_export_entries', $form['id'] ) ) {
				$can_export_entries = true;
				break;
			}
		}

		if ( $can_export_entries || $can_export_forms || $can_import_forms ) {
			return;
		}

		$parent_menu = GFForms::get_parent_menu( apply_filters( 'gform_addon_navigation', [] ) );
		remove_submenu_page( $parent_menu['name'], 'gf_export' );

	}





	// # FORM PERMISSIONS DATA METHODS ---------------------------------------------------------------------------------

	/**
	 * Get form ruleset.
	 *
	 * @since     1.0
	 *
	 * @deprecated 3.0 Use Models\Form_Permissions::get().
	 *
	 * @param int|array $form_id Form ID or Form object.
	 *
	 * @return array
	 */
	public function get_ruleset( $form_id ) {

		$permissions = Models\Form_Permissions::get( $form_id );

		return $permissions->get_rules();

	}

	/**
	 * Get user's capabilities for form.
	 *
	 * @since      1.0
	 *
	 * @deprecated 3.0 Use Models\Form_Permissions::get_capabilities().
	 *
	 * @param int|bool   $form_id Form ID to get capabilities for.
	 * @param array|bool $allcaps An array of all the user's capabilities.
	 *
	 * @return array|false
	 */
	public function get_capabilities_for_form( $form_id = false, $allcaps = false ) {

		$permissions = Models\Form_Permissions::get( $form_id );

		return $permissions->get_capabilities( $allcaps );

	}





	// # DEFAULT PERMISSIONS -------------------------------------------------------------------------------------------

	/**
	 * Get plugin page subviews.
	 *
	 * @since  3.0
	 *
	 * @return array
	 */
	protected function get_subviews() {

		$subviews = parent::get_subviews();

		$subviews[] = [
			'name'     => 'defaults',
			'icon'     => $this->get_menu_icon(),
			'label'    => esc_html__( 'Default Permissions', 'forgravity_advancedpermissions' ),
			'callback' => [ $this, 'default_settings_page' ],
		];

		return $subviews;

	}

	/**
	 * Render the form settings page.
	 *
	 * @since  3.0
	 */
	public function default_settings_page() {

		$icon_path       = $this->get_base_url() . '/dist/images/form-permissions/default.svg';
		$warning_title   = esc_html__( 'Default Form Permissions', 'forgravity_advancedpermissions' );
		$warning_message = esc_html__( 'Form Permissions created below will be automatically added on every new form created.', 'forgravity_advancedpermissions' );
		$link_url        = 'https://cosmicgiant.com/documentation/advanced-permissions/default-rulesets/';
		$link_text       = esc_html__( 'Learn more.', 'forgravity_advancedpermissions' );

		// phpcs:disable
		echo <<<HTML
		<div class="form-permissions__default">
			<span class="form-permissions__default-icon">
				<img src="{$icon_path}" alt="{$warning_title}" width="22" />
			</span>
			<p>
				<strong>{$warning_title}</strong><br />
				{$warning_message}
			</p>
			<a href="{$link_url}" target="_blank">{$link_text}</a>
		</div>
		<div id="form-permissions" class="gform-settings-tabs__container active"></div>
HTML;
		// phpcs:enable

	}

	/**
	 * Add default permissions and the user ID of the user who created the form to the Form object.
	 *
	 * @since 1.2
	 *
	 * @param array $form   Current form.
	 * @param bool  $is_new If form is being created.
	 */
	public function action_gform_after_save_form( $form, $is_new ) {

		if ( ! $is_new || ! ( $form = GFAPI::get_form( rgar( $form, 'id' ) ) ) ) {
			return;
		}

		// Set Form Creator.
		$form['createdBy'] = get_current_user_id();

		// Add default Form Permissions.
		$defaults = Models\Form_Permissions::get( 'default' )->get_rules();
		if ( ! empty( $defaults ) ) {
			$permissions = Models\Form_Permissions::get( $form['id'] );
			$permissions->set_rules( $defaults )->update();
		}

		GFAPI::update_form( $form );

	}





	// # CONDITIONAL LOGIC ---------------------------------------------------------------------------------------------

	/**
	 * Returns the current user roles for the custom Conditional Logic fields.
	 *
	 * @since 3.0
	 *
	 * @param int|string $source_value The value of the rule's configured field ID, entry meta, or custom property.
	 * @param array      $rule         The conditional logic rule that is being evaluated.
	 * @param array      $form         The current form meta.
	 * @param array      $logic        All details required to evaluate an objects conditional logic.
	 * @param array      $entry        The current entry object (if available).
	 *
	 * @return int|string|string[]
	 */
	public function filter_gform_rule_source_value( $source_value, $rule, $form, $logic, $entry ) {

		if ( ! $rule || rgar( $rule, 'fieldId' ) !== 'advancedpermissions-role' ) {
			return $source_value;
		}

		if ( ! empty( $entry ) ) {
			$user = new User( rgar( $entry, 'created_by', 0 ) );
		} else {
			$user = $this->get_current_user();
		}

		return $user->roles;

	}

	/**
	 * Determines if the current user role matches the custom Conditional Logic fields rule.
	 *
	 * @since 3.0
	 *
	 * @param bool         $is_match     Does the target field’s value match with the rule value.
	 * @param string|array $field_value  The field value to use with the comparison.
	 * @param string       $target_value The value from the conditional logic rule to use with the comparison.
	 * @param string       $operation    The conditional logic rule operator.
	 * @param \GF_Field    $source_field The field object for the source of the field value.
	 * @param array        $rule         The current rule object.
	 *
	 * @return bool
	 */
	public function filter_gform_is_value_match( $is_match, $field_value, $target_value, $operation, $source_field, $rule ) {

		if ( ! $rule || rgar( $rule, 'fieldId' ) !== 'advancedpermissions-role' ) {
			return $is_match;
		}

		if ( empty( $field_value ) ) {
			return false;
		}

		switch ( $rule['operator'] ) {

			case 'is':
				return in_array( $rule['value'], $field_value );
			case 'isnot':
				return ! in_array( $rule['value'], $field_value );
			default:
				return false;

		}

	}





	// # IMPORT / EXPORT -----------------------------------------------------------------------------------------------

	/**
	 * Add permission rules to form object before export.
	 *
	 * @since  1.0
	 *
	 * @param array $form The form to be exported.
	 *
	 * @return array
	 */
	public function filter_gform_export_form( $form ) {

		$form_permissions  = Models\Form_Permissions::get( $form['id'] )->get_rules();
		$entry_permissions = Models\Entry_Permissions::get( $form['id'] )->get_rules();

		// If no ruleset is defined, return.
		if ( empty( $form_permissions ) && empty( $entry_permissions ) ) {
			return $form;
		}

		// Add permissions to form object.
		$form['advancedpermissions'] = [
			'form'  => $form_permissions,
			'entry' => $entry_permissions,
		];

		return $form;

	}

	/**
	 * Imports the permissions rules for the newly imported forms.
	 *
	 * @since  1.0
	 *
	 * @param array $forms The imported forms.
	 */
	public function action_gform_forms_post_import( $forms ) {

		// Loop through forms, import permissions.
		foreach ( $forms as $form ) {

			// If no ruleset is defined, continue.
			if ( ! rgars( $form, 'feeds/forgravity-advancedpermissions' ) && ! rgar( $form, 'advancedpermissions' ) ) {
				continue;
			}

			// Handle legacy imports.
			if ( rgars( $form, 'feeds/forgravity-advancedpermissions' ) ) {

				// Save Form Permissions.
				$form_permissions = Models\Form_Permissions::get( $form['id'] );
				$form_permissions->set_rules( $form['feeds']['forgravity-advancedpermissions'] )->update();

				// Remove ruleset from form object.
				unset( $form['feeds']['forgravity-advancedpermissions'] );

				// Remove feeds object if no other feeds are defined.
				if ( empty( $form['feeds'] ) ) {
					unset( $form['feeds'] );
				}

			} else {

				// Save Form Permissions.
				$form_permissions = Models\Form_Permissions::get( $form['id'] );
				$form_permissions->set_rules( $form['advancedpermissions']['form'] )->update();

				// Save Entry Permissions.
				$entry_permissions = Models\Entry_Permissions::get( $form['id'] );
				$entry_permissions->set_rules( $form['advancedpermissions']['entry'] )->update();

				// Remove permissions from form object.
				unset( $form['advancedpermissions'] );

			}

			// Save form.
			GFAPI::update_form( $form );

		}

	}

	/**
	 * Removes forms from Export Entries list that user does not have access to.
	 *
	 * @since 1.1
	 *
	 * @param array $forms The complete list of forms.
	 *
	 * @return array
	 */
	public function filter_gform_export_entries_forms( $forms = [] ) {

		// If no forms exist, return.
		if ( empty( $forms ) ) {
			return $forms;
		}

		// Loop through forms, remove forms with Export Entries capability disabled.
		foreach ( $forms as $index => $form ) {

			// If Export Entries capability is disabled, remove form.
			if ( ! $this->get_current_user()->has_form_cap( 'gravityforms_export_entries', $form->id ) ) {
				unset( $forms[ $index ] );
				continue;
			}

		}

		return $forms;

	}

	/**
	 * Copies existing ruleset to new form on duplication.
	 *
	 * @since 2.0
	 *
	 * @param int $form_id     The original form's ID.
	 * @param int $new_form_id The ID of the new, duplicated form.
	 */
	public function action_gform_post_form_duplicated( $form_id, $new_form_id ) {

		// Get existing permissions.
		$form_permissions  = Models\Form_Permissions::get( $form_id )->get_rules();
		$entry_permissions = Models\Entry_Permissions::get( $form_id )->get_rules();

		if ( empty( $form_permissions ) && empty( $entry_permissions ) ) {
			return;
		}

		if ( $form_permissions ) {

			$new_form_permissions = Models\Form_Permissions::get( $new_form_id );
			$new_form_permissions->set_rules( $form_permissions );

			if ( ! $new_form_permissions->update() ) {
				$this->log_error( __METHOD__ . "(): Unable to duplicate Form Permissions on form #{ $form_id ] to #{ $new_form_id }" );
			}

		}

		if ( $entry_permissions ) {

			$new_entry_permissions = Models\Form_Permissions::get( $new_form_id );
			$new_entry_permissions->set_rules( $entry_permissions );

			if ( ! $new_entry_permissions->update() ) {
				$this->log_error( __METHOD__ . "(): Unable to duplicate Entry Permissions on form #{ $form_id ] to #{ $new_form_id }" );
			}

		}

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Add a new filter.
	 *
	 * @since 2.0
	 *
	 * @param string $filter Hook name.
	 */
	public function add_filter( $filter ) {

		switch ( $filter ) {

			case 'user_has_cap':
				add_filter( 'user_has_cap', [ $this, 'filter_user_has_cap' ], 999, 3 );
				return;

		}

	}

	/**
	 * Remove an already registered filter.
	 *
	 * @since 2.0
	 *
	 * @param string $filter Hook name.
	 */
	public function remove_filter( $filter ) {

		switch ( $filter ) {

			case 'user_has_cap':
				remove_filter( 'user_has_cap', [ $this, 'filter_user_has_cap' ], 999 );
				return;

		}

	}

	/**
	 * Get available Add-Ons that have a form settings capability defined.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function get_available_addons() {

		// Initialize Add-Ons array.
		$addons = [];

		// Get registered Add-Ons.
		$registered_addons = GFAddOn::get_registered_addons();

		// If no registered Add-Ons are available, return.
		if ( empty( $registered_addons ) ) {
			return $addons;
		}

		/**
		 * Loop through registered Add-Ons.
		 *
		 * @var GFAddOn $registered_addon
		 */
		foreach ( $registered_addons as $registered_addon ) {

			// If we cannot get the Add-On instance, skip.
			if ( ! method_exists( $registered_addon, 'get_instance' ) ) {
				continue;
			}

			// Get Add-On instance.
			$registered_addon = call_user_func( [ $registered_addon, 'get_instance' ] );

			// If this Add-On supports results, add additional capability.
			if ( method_exists( $registered_addon, 'get_results_page_config' ) && $results_config = $registered_addon->get_results_page_config() ) {
				$addons[ $registered_addon->get_slug() . '_results' ] = [
					'slug'       => $registered_addon->get_slug() . '_results',
					'name'       => $results_config['title'],
					'capability' => $results_config['capabilities'][0],
				];
			}

			// If Add-On does not have a form settings capability, skip.
			if ( empty( $registered_addon->get_capabilities( 'form_settings' ) ) ) {
				continue;
			}

			// Add Add-On to array.
			$addons[ $registered_addon->get_slug() ] = [
				'slug'       => $registered_addon->get_slug(),
				'name'       => $registered_addon->get_short_title(),
				'capability' => $registered_addon->get_capabilities( 'form_settings' ),
			];

		}

		return $addons;

	}

	/**
	 * Get available capabilities as groups.
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function get_capability_groups() {

		// Prepare base capability groups.
		$groups = [
			'form'        => [
				'name'         => 'form',
				'label'        => esc_html__( 'Form Permissions', 'forgravity_advancedpermissions' ),
				'capabilities' => [
					[
						'label'      => esc_html__( 'Preview Form', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_preview_forms',
						'required'   => true,

					],
					[
						'label'      => esc_html__( 'Edit Form Fields', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_edit_forms_fields',
						'required'   => true,

					],
					[
						'label'      => esc_html__( 'Edit Form Settings', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_edit_forms_settings',
						'required'   => true,

					],
					[
						'label'      => esc_html__( 'Edit Notifications', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_edit_forms_notifications',
						'required'   => true,

					],
					[
						'label'      => esc_html__( 'Edit Confirmations', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_edit_forms_confirmations',
						'required'   => true,

					],
					[
						'label'      => esc_html__( 'Delete Form', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_delete_forms',
						'required'   => true,

					],
					[
						'label'      => esc_html__( 'Duplicate Form', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_create_form',
						'required'   => true,
					],
				],
			],
			'entry'       => [
				'name'         => 'entry',
				'label'        => esc_html__( 'Entry Permissions', 'forgravity_advancedpermissions' ),
				'capabilities' => [
					[
						'label'      => esc_html__( 'View Entries', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_view_entries',
						'required'   => true,

					],
					[
						'label'      => esc_html__( 'Edit Entries', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_edit_entries',
						'required'   => true,
					],
					[
						'label'      => esc_html__( 'Delete Entries', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_delete_entries',
						'required'   => true,
					],
					[
						'label'      => esc_html__( 'Export Entries', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_export_entries',
						'required'   => false,
					],
				],
			],
			'entry-notes' => [
				'name'         => 'notes',
				'label'        => esc_html__( 'Entry Notes Permissions', 'forgravity_advancedpermissions' ),
				'capabilities' => [
					[
						'label'      => esc_html__( 'View Notes', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_view_entry_notes',
						'required'   => false,
					],
					[
						'label'      => esc_html__( 'Edit Notes', 'forgravity_advancedpermissions' ),
						'capability' => 'gravityforms_edit_entry_notes',
						'required'   => false,
					],
				],
			],
		];

		// Get defined Add-On capabilities.
		$addon_capabilities = $this->get_available_addons();

		// If no Add-On capabilities exist, return.
		if ( empty( $addon_capabilities ) ) {
			return $groups;
		}

		// Get core capabilities.
		$core_capabilities = [];
		foreach ( $groups as $group ) {

			// Get group capabilities.
			$group_caps = wp_list_pluck( $group['capabilities'], 'capability' );

			// Push to capabilities array.
			$core_capabilities = array_merge( $core_capabilities, $group_caps );

		}

		// Initialize Add-On capabilities array.
		$addon_caps = [];

		// Loop through Add-On capabilities.
		foreach ( $addon_capabilities as $addon_capability ) {

			if ( in_array( $addon_capability['capability'], $core_capabilities ) ) {
				continue;
			}

			$addon_caps[] = [
				'label'      => esc_html( $addon_capability['name'] ),
				'capability' => $addon_capability['capability'],
				'required'   => true,
			];

		}

		$groups['addons'] = [
			'name'         => 'addons',
			'label'        => esc_html__( 'Add-On Permissions', 'forgravity_advancedpermissions' ),
			'capabilities' => $addon_caps,
		];

		return $groups;

	}

	/**
	 * Wrapper for wp_get_current_user() to provide proper type hinting.
	 *
	 * @since 2.0
	 *
	 * @return User
	 */
	public function get_current_user() {

		$user = wp_get_current_user();

		if ( $user instanceof WP_User && $user->ID && ( ! isset( self::$current_user ) || self::$current_user->ID !== $user->ID ) ) {

			self::$current_user = new User( $user );

		}

		// If $current_user is null, return a default User object (user id is 0 by default).
		return self::$current_user ? self::$current_user : new User();

	}

	/**
	 * Get WordPress roles as options.
	 *
	 * @since    1.0
	 * @since    3.0 Added include administrator parameter.
	 *
	 * @param bool $include_administrator Include Administrator role.
	 *
	 * @return array
	 */
	private function get_roles_as_options( $include_administrator = false ) {

		$form = $this->get_current_form();

		// Initialize return array.
		$roles = [
			[
				'label'   => esc_html__( 'WordPress Roles', 'forgravity_advancedpermissions' ),
				'options' => [],
			],
		];

		// Get available roles.
		$editable_roles = array_reverse( get_editable_roles() );

		// Loop through roles.
		foreach ( $editable_roles as $role => $details ) {

			// If this is the administrator role, skip.
			if ( 'administrator' === $role && ! $include_administrator ) {
				continue;
			}

			// Translate role name.
			$name = translate_user_role( $details['name'] );

			// Add option.
			$roles[0]['options'][] = [
				'label' => esc_html( $name ),
				'value' => esc_html( $role ),
			];

		}

		// If the form creator is not saved to the form, return.
		if ( ! rgar( $form, 'createdBy' ) && $form ) {
			return $roles;
		}

		// If form creator is immune, return.
		if ( $form && ( new User( $form['createdBy'] ) )->is_immune() ) {
			return $roles;
		}

		$roles[] = [
			'label'   => esc_html__( 'Form Roles', 'forgravity_advancedpermissions' ),
			'options' => [
				[
					'label' => esc_html__( 'Form Creator', 'forgravity_advancedpermissions' ),
					'value' => self::ROLE_FORM_CREATOR,
				],
			],
		];

		return $roles;

	}

	/**
	 * Delete ruleset when deleting form.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id Form ID being deleted.
	 */
	public function action_gform_after_delete_form( $form_id ) {

		Models\Entry_Permissions::get( $form_id )->delete();
		Models\Form_Permissions::get( $form_id )->delete();

	}

	/**
	 * Return if it's a addon (Quiz or Survey) results page.
	 *
	 * @since 1.2
	 *
	 * @return bool
	 */
	private function is_addon_results_page() {

		if ( rgget( 'page' ) == 'gf_entries' && strpos( rgget( 'view' ), 'gf_results_' ) !== false ) {
			return true;
		}

		return false;

	}





	// # UPGRADE ROUTINES ----------------------------------------------------------------------------------------------

	/**
	 * Upgrade routines.
	 *
	 * @since  1.0.6
	 *
	 * @param string $previous_version Previously installed version number.
	 */
	public function upgrade( $previous_version ) {

		if ( empty( $previous_version ) ) {
			return;
		}

		// Run auto update upgrade.
		if ( version_compare( $previous_version, '1.1.4', '<' ) ) {

			$settings = $this->get_plugin_settings();
			if ( $settings['background_updates'] ) {
				$this->update_wp_auto_updates( true );
			}

		}

		// Run rule ID upgrade.
		if ( version_compare( $previous_version, '3.0-dev-1', '<' ) ) {
			$this->upgrade_rules();
		}

	}

	/**
	 * Upgrade rules to include a unique ID.
	 *
	 * @since 3.0
	 */
	private function upgrade_rules() {

		$forms = GFAPI::get_forms( null, null );

		foreach ( $forms as $form ) {

			$form_permissions = Models\Form_Permissions::get( $form['id'] );
			$rules            = $form_permissions->get_rules();

			if ( empty( $rules ) ) {
				continue;
			}

			foreach ( $rules as $r => $rule ) {

				if ( isset( $rule['id'] ) ) {
					continue;
				}

				$rules[ $r ]['id']   = Uuid::uuid4();
				$rules[ $r ]['name'] = sprintf( '%1$s Rule %2$s', $form['title'], $r + 1 );

			}

			$form_permissions->set_rules( $rules )->update();

		}

	}





	// # MEMBERS INTEGRATION -------------------------------------------------------------------------------------------

	/**
	 * Forces the "gform_full_access" capability when accessing the Form Settings page as an administrator while Members is active.
	 *
	 * @since  1.4
	 *
	 * @param array $allcaps An array of all the user's capabilities.
	 * @param array $cap     Actual capabilities for meta capability.
	 * @param array $args    Parameters passed to has_cap(), typically object ID.
	 *
	 * @return array
	 */
	public function filter_user_has_cap_members( $allcaps, $cap, $args ) {

		if ( ! $this->get_current_user()->is_immune() || rgar( $allcaps, 'gform_full_access' ) === true ) {
			return $allcaps;
		}

		if ( ! class_exists( '\Members_Plugin' ) ) {
			return $allcaps;
		}

		if ( ! $this->is_form_settings( $this->_slug ) ) {
			return $allcaps;
		}

		$allcaps['gform_full_access'] = true;

		return $allcaps;

	}

	/**
	 * Get the Members capabilities prefix.
	 *
	 * @since  3.0
	 *
	 * @return string
	 */
	protected function get_members_cap_prefix() {

		return 'forgravity_advancedpermissions';

	}





	// # IMPLEMENTATION ------------------------------------------------------------------------------------------------

	/**
	 * Return the store url constant name.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function store_url() {

		return 'CG_EDD_STORE_URL';

	}

	/**
	 * Get the full path and filename of the plugin bootstrap file.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function get_plugin_file() {

		return trailingslashit( dirname( __DIR__ ) ) . 'advancedpermissions.php';

	}

	/**
	 * Get the includes folder path.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_includes_path() {

		return __DIR__;

	}

	/**
	 * Return the plugin basename constant name.
	 *
	 * @since 3.1
	 *
	 * @return string
	 */
	protected function base_name() {

		return 'CG_ADVANCEDPERMISSIONS_PLUGIN_BASENAME';

	}

	/**
	 * Return the license key constant name.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function license_key() {

		return 'CG_ADVANCEDPERMISSIONS_LICENSE_KEY';

	}

	/**
	 * Return the addon version constant name.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function addon_version() {

		return 'CG_ADVANCEDPERMISSIONS_VERSION';

	}

	/**
	 * Return the EDD item id constant name.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function edd_item_id() {

		return 'CG_ADVANCEDPERMISSIONS_EDD_ITEM_ID';

	}

}
