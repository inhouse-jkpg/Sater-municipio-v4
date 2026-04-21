<?php
/**
 * Advanced Permissions User class file.
 *
 * @since   2.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions;

use GFFormsModel;
use WP_User;

/**
 * Advanced Permissions User class file.
 * Extends functionality of WP_User.
 *
 * @since     2.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2021, CosmicGiant
 */
class User extends WP_User {

	/**
	 * Caches capabilities for forms.
	 *
	 * @since 2.0
	 *
	 * @var array
	 */
	private $form_caps = [];

	/**
	 * Determines if the User is immune from having permissions applied to them.
	 *
	 * @since 2.0
	 *
	 * @return false
	 */
	public function is_immune() {

		if ( is_multisite() ) {
			$super_admins = get_super_admins();
			if ( is_array( $super_admins ) && in_array( $this->user_login, $super_admins, true ) ) {
				return true;
			}
		}

		return in_array( 'administrator', $this->roles );

	}

	/**
	 * Determines if the User has access to a capability for a specific form.
	 *
	 * @since 2.0
	 *
	 * @param string $cap     Capability name.
	 * @param int    $form_id Form to check capability for.
	 *
	 * @return bool
	 */
	public function has_form_cap( $cap, $form_id ) {

		// Set return value to root capability access setting.
		$has_form_cap = $this->has_default_cap( $cap ) || $this->has_default_cap( 'gform_full_access' );

		$form_caps = $this->get_caps_for_form( $form_id );

		// Set return value to form capability access setting where available.
		if ( $form_caps ) {
			if ( isset( $form_caps[ $cap ] ) && ! is_null( $form_caps[ $cap ] ) ) {
				$has_form_cap = $form_caps[ $cap ];
			}

			// Check pseudo caps to and set $has_form_cap to true.
			if ( $cap === 'gravityforms_edit_forms' && ! $has_form_cap ) {

				foreach ( $form_caps as $cap => $state ) {

					if ( strstr( $cap, 'gravityforms_edit_forms' ) && $state ) {
						return true;
					}

				}

			}
		}

		return (bool) $has_form_cap;

	}





	// # ACCESS METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Check if the current user can see the Forms menu.
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function can_see_menu() {

		if ( ! empty( $this->form_caps ) ) {
			return true;
		}

		// If user is an administrator, return true.
		if ( $this->is_immune() ) {
			return true;
		}

		$can_see_menu = false;

		// Get all forms. We can't use GFAPI because it can only get active or inactive forms, but not both.
		$forms = GFFormsModel::get_forms();

		// Check caps for each form, the user can see the Forms menu if any cap is set to true.
		foreach ( $forms as $form ) {

			$form_id = rgobj( $form, 'id' );

			$caps = $this->get_caps_for_form( $form_id );

			if ( ! $caps ) {

				continue;

			}

			foreach ( $caps as $state ) {

				if ( $state === true ) {

					$can_see_menu = true;

					// Here we only break but not return, so we can get `form_caps` for all forms set.
					break;

				}

			}

		}

		return $can_see_menu;

	}

	/**
	 * Determine if user has any access to a specific form.
	 *
	 * @since 2.0
	 *
	 * @param int|array|object $form_id_or_object Form ID or object.
	 * @param bool             $check_core_only   Defines if only core capabilities should be checked.
	 *
	 * @return bool
	 */
	public function can_access_form( $form_id_or_object, $check_core_only = false ) {

		if ( ! is_numeric( $form_id_or_object ) ) {
			$form_id = is_object( $form_id_or_object ) ? $form_id_or_object->id : $form_id_or_object['id'];
		} else {
			$form_id = $form_id_or_object;
		}

		$has_access = false;

		// Get required capabilities.
		foreach ( advancedpermissions()->get_capability_groups() as $group_key => $group ) {

			if ( $check_core_only && $group_key === 'addons' ) {
				continue;
			}

			foreach ( $group['capabilities'] as $group_capability ) {

				if ( rgar( $group_capability, 'required' ) && $this->has_form_cap( $group_capability['capability'], $form_id ) ) {
					$has_access = true;
				}

			}

		}

		return $has_access;

	}

	/**
	 * Determine if user can access entries for a form.
	 *
	 * @since 2.0
	 *
	 * @param int|array|object $form_id_or_object Form ID or object.
	 *
	 * @return bool
	 */
	public function can_view_entries( $form_id_or_object ) {

		if ( ! is_numeric( $form_id_or_object ) ) {
			$form_id = is_object( $form_id_or_object ) ? $form_id_or_object->id : $form_id_or_object['id'];
		} else {
			$form_id = $form_id_or_object;
		}

		return $this->has_form_cap( 'gravityforms_view_entries', $form_id ) || $this->has_form_cap( 'gravityforms_edit_entries', $form_id );

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Returns whether the user has the specified capability.
	 * Adjusts the capability name for certain pseudo capabilities.
	 *
	 * @since 2.0
	 *
	 * @param string $cap Capability name.
	 *
	 * @return bool
	 */
	private function has_default_cap( $cap ) {

		advancedpermissions()->remove_filter( 'user_has_cap' );

		switch ( $cap ) {

			case 'gravityforms_edit_forms_fields':
			case 'gravityforms_edit_forms_confirmations':
			case 'gravityforms_edit_forms_notifications':
			case 'gravityforms_edit_forms_settings':
				$default_cap = $this->has_cap( 'gravityforms_edit_forms' );
				break;

			default:
				$default_cap = $this->has_cap( $cap );
				break;

		}

		advancedpermissions()->add_filter( 'user_has_cap' );

		return (bool) $default_cap;

	}

	/**
	 * Get form capabilities for the current user.
	 *
	 * @since 2.0
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return array|false
	 */
	private function get_caps_for_form( $form_id ) {

		if ( isset( $this->form_caps[ $form_id ] ) ) {
			return $this->form_caps[ $form_id ];
		}

		$this->form_caps[ $form_id ] = Models\Form_Permissions::get( $form_id )->get_capabilities();

		return $this->form_caps[ $form_id ];

	}

	/**
	 * Get all customized caps for the user.
	 *
	 * Regardless of the state, we set all caps to true so the user can access related GF pages.
	 * Their final access to the function depends on the form ID, is handled in filter_user_has_cap().
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	public function get_all_customized_caps() {

		$all_caps = [];

		foreach ( $this->form_caps as $form_caps ) {

			if ( ! $form_caps ) {
				continue;
			}

			foreach ( $form_caps as $cap => $state ) {

				// Skip if the cap is already set to true.
				if ( rgar( $all_caps, $cap ) || is_null( $state ) ) {
					continue;
				}

				// This cap affects the Add Form submenu and button creation, but not affecting other pages rendering,
				// so we respect it and don't force it to true.
				if ( $cap === 'gravityforms_create_form' && $state === false ) {
					continue;
				}

				// Add the default cap if pseudo caps available.
				if ( ! rgar( $all_caps, 'gravityforms_edit_forms' ) && strstr( $cap, 'gravityforms_edit_forms' ) ) {
					$all_caps['gravityforms_edit_forms'] = true;
				}

				// Set all capabilities to true regardless the state.
				$all_caps[ $cap ] = true;

			}

		}

		return $all_caps;

	}

}
