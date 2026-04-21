<?php
/**
 * Advanced Permissions Forms view class.
 *
 * @since   3.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions\Views;

use GFAPI;

/**
 * The Forms View class.
 *
 * @since     3.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2023, CosmicGiant
 */
class Forms extends Base {

	/**
	 * Add hooks.
	 *
	 * @since 3.0
	 */
	public function add_hooks() {

		if ( $this->get_current_user()->is_immune() ) {
			return;
		}

		add_filter( 'gform_toolbar_menu', [ $this, 'filter_gform_toolbar_menu' ], 20, 2 );
		add_filter( 'gform_form_settings_menu', [ $this, 'filter_gform_form_settings_menu' ], 999, 2 );
		add_filter( 'gform_form_switcher_forms', [ $this, 'filter_gform_form_switcher_forms' ], 20, 2 );

		add_filter( 'gform_form_list_forms', [ $this, 'filter_gform_form_list_forms' ], 20, 2 );
		add_filter( 'gform_form_list_count', [ $this, 'filter_gform_form_list_count' ], 20, 1 );
		add_filter( 'gform_form_actions', [ $this, 'filter_gform_form_actions' ], 20, 2 );
		add_action( 'gform_form_list_column_is_active', [ $this, 'action_gform_form_list_column_is_active' ] );

		add_filter( 'gform_export_menu', [ $this, 'filter_gform_export_menu' ], 999 );

		add_filter( 'gform_form_summary', [ $this, 'filter_gform_form_summary' ], 20, 2 );

		add_filter( 'gform_block_form_forms', [ $this, 'filter_gform_block_form_forms' ], 20 );

		add_filter( 'gravityflow_status_filter', [ $this, 'filter_gravityflow_status_filter' ] );

	}





	// # FORM SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Modify the links which display in the Gravity Forms toolbar
	 *
	 * @since  3.0
	 *
	 * @param array $items   An array of toolbar items.
	 * @param int   $form_id The ID of the form for which the toolbar is being displayed.
	 *
	 * @return array
	 */
	public function filter_gform_toolbar_menu( $items, $form_id ) {

		return $this->filter_gform_form_actions( $items, $form_id );

	}

	/**
	 * Modify the form settings tabs.
	 *
	 * @since  3.0
	 *
	 * @param array $tabs    The settings tabs.
	 * @param int   $form_id The ID of the form for which the tabs are being displayed.
	 *
	 * @return array
	 */
	public function filter_gform_form_settings_menu( $tabs, $form_id ) {

		$user = $this->get_current_user();

		// Remove specific form settings tabs.
		foreach ( $tabs as $i => $tab ) {

			switch ( $tab['name'] ) {

				case 'confirmation':
					if ( ! $user->has_form_cap( 'gravityforms_edit_forms_confirmations', $form_id ) ) {
						unset( $tabs[ $i ] );
					}
					break;

				case 'personal-data':
				case 'settings':
					if ( ! $user->has_form_cap( 'gravityforms_edit_forms_settings', $form_id ) ) {
						unset( $tabs[ $i ] );
					}
					break;

				case 'notification':
					if ( ! $user->has_form_cap( 'gravityforms_edit_forms_notifications', $form_id ) ) {
						unset( $tabs[ $i ] );
					}
					break;

			}

		}

		return $tabs;

	}

	/**
	 * Remove forms from Form Switcher that user does not have access to.
	 *
	 * @since 3.0
	 *
	 * @param array $forms Collection of forms to display in Form Switcher.
	 *
	 * @return array
	 */
	public function filter_gform_form_switcher_forms( $forms = [] ) {

		return $this->remove_inaccessible_forms( $forms );

	}





	// # FORM LIST -----------------------------------------------------------------------------------------------------

	/**
	 * Remove forms from form list that user does not have any access to.
	 *
	 * @since 3.0
	 *
	 * @param array $forms The complete list of forms.
	 *
	 * @return array
	 */
	public function filter_gform_form_list_forms( $forms = [] ) {

		return $this->remove_inaccessible_forms( $forms );

	}

	/**
	 * Returns the form counts for the Forms List with permissions applied.
	 *
	 * @since 2.1
	 *
	 * @param array $form_count The form counts.
	 *
	 * @return array
	 */
	public function filter_gform_form_list_count( $form_count ) {

		// Get all forms.
		$forms = GFAPI::get_forms( null, null );

		// Filter out forms that user cannot access.
		$forms = array_filter(
			$forms,
			function( $form ) {
				return $this->get_current_user()->can_access_form( $form );
			}
		);

		// Return filtered counts.
		return [
			'total'    => count( array_filter( $forms, function( $f ) { return $f['is_trash'] == 0; } ) ),
			'active'   => count( array_filter( $forms, function( $f ) { return $f['is_active'] == 1 && $f['is_trash'] == 0; } ) ),
			'inactive' => count( array_filter( $forms, function( $f ) { return $f['is_active'] == 0 && $f['is_trash'] == 0; } ) ),
			'trash'    => count( array_filter( $forms, function( $f ) { return $f['is_trash'] == 1; } ) ),
		];

	}

	/**
	 * Modify form actions which display below the form title in the Form List view.
	 *
	 * @since 3.0
	 *
	 * @param array $actions An associative array containing all of the default form actions.
	 * @param int   $form_id The ID of the form for which the toolbar is being displayed.
	 *
	 * @return array
	 */
	public function filter_gform_form_actions( $actions, $form_id ) {

		$user = $this->get_current_user();

		// Reduce Results to a single item where possible.
		foreach ( $actions as $i => $action ) {

			// If this is not the Results item, skip.
			if ( 'gf_form_toolbar_results' !== rgar( $action, 'menu_class' ) ) {
				continue;
			}

			// If Results menu item has more than one sub-menu item, skip.
			if ( rgar( $action, 'sub_menu_items' ) && count( $action['sub_menu_items'] ) > 1 ) {
				continue;
			}

			// If sub-menu item and main item are the same, remove sub-menu item.
			if ( rgar( $action, 'label' ) === rgars( $action, 'sub_menu_items/0/label' ) ) {
				$actions[ $i ]['url'] = $action['sub_menu_items'][0]['url'];
				unset( $actions[ $i ]['sub_menu_items'][0] );
				unset( $actions[ $i ]['onclick'] );
			}

		}

		foreach ( $actions as $i => $action ) {

			// Set action capabilities as array.
			if ( rgar( $action, 'capabilities' ) && ! is_array( $action['capabilities'] ) ) {
				$action['capabilities'] = [ $action['capabilities'] ];
			}

			// Handle Form Editor item.
			if ( 'edit' === $i && 'gf_form_toolbar_editor' === rgar( $action, 'menu_class' ) && ! $user->has_form_cap( 'gravityforms_edit_forms_fields', $form_id ) ) {
				unset( $actions[ $i ] );
				continue;
			}

			// Handle Form Settings item.
			if ( 'settings' === $i && 'gf_form_toolbar_settings' === rgar( $action, 'menu_class' ) ) {

				// Reset URL.
				if ( ! $user->has_form_cap( 'gravityforms_edit_forms_settings', $form_id ) ) {
					$actions[ $i ]['url'] = $action['url'] = '#';
				}

			}

			// If Preview Form is disabled, remove item.
			if ( 'preview' === $i ) {

				if ( ! $user->has_form_cap( 'gravityforms_preview_forms', $form_id ) ) {
					unset( $actions[ $i ] );
				}

				continue;

			}

			// If View Entries is disabled, remove item.
			if ( 'entries' === $i && 'gf_form_toolbar_entries' === rgar( $action, 'menu_class' ) ) {

				if ( $user->has_form_cap( 'gravityforms_view_entries', $form_id ) ) {
					continue;
				}

			}

			// Remove sub-menu actions.
			if ( rgar( $action, 'sub_menu_items' ) ) {

				// Loop through sub-menu actions, remove disabled items.
				foreach ( $action['sub_menu_items'] as $j => $sub_item ) {

					// Set capabilities as array.
					if ( ! is_array( $sub_item['capabilities'] ) ) {
						$actions[ $i ]['sub_menu_items'][ $j ] = $sub_item['capabilities'] = [ $sub_item['capabilities'] ];
					}

					// Set has cap flag.
					$has_cap = false;

					// Loop through capabilities, remove disabled items.
					foreach ( $sub_item['capabilities'] as $capability ) {
						if ( $user->has_form_cap( $capability, $form_id ) ) {
							$has_cap = true;
						}
					}

					if ( ! $has_cap ) {
						unset( $actions[ $i ]['sub_menu_items'][ $j ], $action['sub_menu_items'][ $j ] );
					}

				}

				// Reset array keys.
				$actions[ $i ]['sub_menu_items'] = $action['sub_menu_items'] = array_values( $action['sub_menu_items'] );

			}

			// Loop through action capabilities, remove item/URL.
			foreach ( $action['capabilities'] as $action_cap ) {

				// If user does not have capability, remove item/URL.
				if ( ! $user->has_form_cap( $action_cap, $form_id ) && ! rgar( $action, 'sub_menu_items' ) ) {

					// Remove action.
					unset( $actions[ $i ] );

					continue;

				} else if ( rgar( $action, 'sub_menu_items' ) && ! empty( $action['sub_menu_items'] ) ) {

					// Reset URL.
					$actions[ $i ]['url'] = '#';

					// Add dummy item for Form Settings item.
					if ( 'settings' === $i ) {

						// Prepare dummy item.
						$dummy_item = [
							'url'          => '#',
							'label'        => null,
							'menu_class'   => 'fgea_display_none',
							'capabilities' => 'gravityforms_edit_forms',
						];

						array_unshift( $actions[ $i ]['sub_menu_items'], $dummy_item );

					}

				}

			}

		}

		return $actions;

	}

	/**
	 * Remove active toggle from form if form settings capability is disabled.
	 *
	 * @since 3.0
	 *
	 * @param object $form The Form object.
	 */
	public function action_gform_form_list_column_is_active( $form ) {

		if ( ! $this->get_current_user()->has_form_cap( 'gravityforms_edit_forms_settings', $form->id ) || rgget( 'filter' ) === 'trash' ) {
			return;
		}

		ob_start();
		$table = new \GF_Form_List_Table();
		$table->_column_is_active( $form, '', '', '' );
		$column = ob_get_contents();
		ob_end_clean();

		print preg_replace( '/<td[^>]*>(.*?)<\/td>/s', '$1', $column );

	}





	// # IMPORT / EXPORT -----------------------------------------------------------------------------------------------

	/**
	 * Remove "Export Forms" tab if user does not have access.
	 *
	 * @since 2.1
	 *
	 * @param array $tabs Import/Export tabs.
	 *
	 * @return array
	 */
	public function filter_gform_export_menu( $tabs ) {

		if ( $this->get_current_user()->is_immune() ) {
			return $tabs;
		}

		foreach ( $tabs as $i => $tab ) {

			if ( $tab['name'] !== 'export_form' ) {
				continue;
			}

			advancedpermissions()->remove_filter( 'user_has_cap' );
			$can_export_forms = current_user_can( 'gravityforms_edit_forms' );
			advancedpermissions()->add_filter( 'user_has_cap' );

			if ( ! $can_export_forms ) {
				unset( $tabs[ $i ] );
			}

		}

		return $tabs;

	}





	// # DASHBOARD -----------------------------------------------------------------------------------------------------

	/**
	 * Remove forms from Dashboard widget that user does not have access to.
	 *
	 * @since 3.0
	 *
	 * @param array $forms Collection of forms to display in Dashboard widget.
	 *
	 * @return array
	 */
	public function filter_gform_form_summary( $forms = [] ) {

		return $this->remove_inaccessible_forms( $forms );

	}





	// # BLOCK EDITOR --------------------------------------------------------------------------------------------------

	/**
	 * Remove forms from Gutenberg Block that user does not have access to.
	 *
	 * @since 3.0
	 *
	 * @param array $forms Collection of forms to display in Gutenberg block.
	 *
	 * @return array
	 */
	public function filter_gform_block_form_forms( $forms = [] ) {

		$forms = $this->remove_inaccessible_forms( $forms );

		return array_values( $forms );

	}





	// # GRAVITY FLOW --------------------------------------------------------------------------------------------------

	/**
	 * Exclude forms from Gravity Flow Status filter.
	 *
	 * @since 1.1
	 *
	 * @param array $args Filter constraints.
	 *
	 * @return array
	 */
	public function filter_gravityflow_status_filter( $args = [] ) {

		// Get current user.
		$user = $this->get_current_user();

		// Force form IDs to array.
		if ( ! is_array( $args['form_id'] ) ) {
			$args['form_id'] = ( $args['form_id'] === 0 ) ? [] : [ $args['form_id'] ];
		}

		// Loop through forms, include forms with access.
		foreach ( GFAPI::get_forms() as $form ) {

			// Get Gravity Flow capability.
			$gflow_cap = gravity_flow()->get_capabilities( 'form_settings' );

			// If Gravity Flow capability is not disabled, include it.
			if ( $user->has_form_cap( $gflow_cap, $form['id'] ) ) {
				$args['form_id'][] = $form['id'];
				continue;
			}

		}

		return $args;

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Remove forms from array that user does not have any access to.
	 *
	 * @since 3.0
	 *
	 * @param array $forms The complete list of forms.
	 *
	 * @return array
	 */
	private function remove_inaccessible_forms( $forms = [] ) {

		// If no forms exist, return.
		if ( empty( $forms ) ) {
			return $forms;
		}

		// Loop through forms, remove forms with no permissions.
		foreach ( $forms as $index => $form ) {

			// If user does not have access to form, remove from list.
			if ( ! $this->get_current_user()->can_access_form( $form ) ) {
				unset( $forms[ $index ] );
			}

		}

		return $forms;

	}

	/**
	 * Helper method to return the current user.
	 *
	 * @since 3.0
	 *
	 * @return \CosmicGiant\Advanced_Permissions\User|null
	 */
	private function get_current_user() {

		return advancedpermissions()->get_current_user();

	}

}
