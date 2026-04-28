<?php
/**
 * Advanced Permissions Form Permissions object model.
 *
 * @since   3.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions\Models;

use CosmicGiant\Advanced_Permissions\Advanced_Permissions;
use CosmicGiant\Advanced_Permissions\User;
use GFAPI;

/**
 * Form Permissions object model.
 *
 * @since     3.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2023, CosmicGiant
 */
class Form_Permissions extends Base {

	/**
	 * Get user's capabilities for form.
	 *
	 * @since  3.0
	 *
	 * @param array|bool $allcaps An array of all the user's capabilities.
	 * @param User       $user    User to get rule for.
	 *
	 * @return array|false
	 */
	public function get_capabilities( $allcaps = false, $user = false ) {

		if ( ! $user ) {
			$user = advancedpermissions()->get_current_user();
		}

		$form  = GFAPI::get_form( $this->form_id );
		$rules = $this->get_rules();

		// If user is an administrator or no rules exist, return.
		if ( $user->is_immune() || empty( $rules ) ) {
			return false;
		}

		// If all capabilities are not defined, get from user.
		if ( ! $allcaps ) {
			$allcaps = $user->allcaps;
		}

		// Get available capabilities.
		$all_caps = $this->get_all_capabilities();

		// Initialize capabilities.
		$capabilities = [];

		// Loop through all capabilities, set non-existent capabilities to null.
		foreach ( $all_caps as $cap ) {
			$capabilities[ $cap ] = rgar( $allcaps, $cap ) ? $allcaps[ $cap ] : null;
		}

		// Initialize matched rule flag.
		$matched_rule = false;

		// Get current user roles.
		$user_roles = $user->roles;
		if ( (int) rgar( $form, 'createdBy' ) === $user->ID ) {
			$user_roles[] = Advanced_Permissions::ROLE_FORM_CREATOR;
		}

		// Initialize an empty ruleset caps array.
		$ruleset_caps = [];

		// Loop through ruleset.
		foreach ( $rules as $rule ) {

			// Get operator.
			$operator = rgar( $rule, 'operator' ) ? $rule['operator'] : 'is';

			// If no targets were selected, skip rule.
			if ( ! rgar( $rule, 'targets' ) ) {
				continue;
			}

			// If role rule type, match user to role.
			if ( 'role' === $rule['targetType'] ) {

				// Define found user role flag.
				$found_role = false;

				// Loop through roles, search for user role.
				foreach ( $user_roles as $user_role ) {
					if (
						( $operator === 'is' && in_array( $user_role, $rule['targets'] ) ) // IS.
						||
						( $operator === 'isnot' && ! in_array( $user_role, $rule['targets'] ) ) // IS NOT.
					) {
						$found_role   = true;
						$matched_rule = true;
						// As soon as we find a matching role, we can break out of the loop.
						break;
					}
				}

				// If user does not have matching role, skip.
				if ( ! $found_role ) {
					continue;
				}

			} elseif ( 'user' === $rule['targetType'] ) {

				// If user is not in the list of targets, skip.
				if (
					( $operator === 'is' && in_array( $user->ID, $rule['targets'] ) ) // IS.
					||
					( $operator === 'isnot' && ! in_array( $user->ID, $rule['targets'] ) )  // IS NOT.
				) {
					$matched_rule = true;
				} else {
					continue;
				}

			}

			if ( ! $matched_rule ) {
				continue;
			}

			// Build the ruleset caps array.
			foreach ( $rule['capabilities'] as $capability => $state ) {

				// Some addons may use `gravityforms_edit_forms` as their cap but `gravityforms_edit_forms` should be determined by our pseudo caps.
				if ( $capability === 'gravityforms_edit_forms' ) {
					continue;
				}

				// Skip a null state cap if it was granted in other rules.
				if ( rgar( $ruleset_caps, $capability ) && is_null( $state ) ) {
					continue;
				}

				// Add the default cap if pseudo caps available.
				if ( strstr( $capability, 'gravityforms_edit_forms' ) ) {
					$ruleset_caps['gravityforms_edit_forms'] = true;
				}

				$ruleset_caps[ $capability ] = $state;

			}

		}

		// Update the capabilities array with the ruleset caps.
		foreach ( $ruleset_caps as $capability => $state ) {

			// If the cap was granted in other forms or plugins like Members, and now in this form it's set to null, we'll reset it to null. Otherwise, just skip it.
			if ( ! rgar( $capabilities, $capability ) && is_null( $state ) ) {
				continue;
			}

			$capabilities[ $capability ] = $state;

		}

		return $capabilities;

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Get all available Gravity Forms capabilities.
	 *
	 * @since  3.0
	 *
	 * @return array
	 */
	private function get_all_capabilities() {

		$caps = [];

		// Get capability groups.
		$groups = advancedpermissions()->get_capability_groups();

		// Loop through groups, merge capabilities.
		foreach ( $groups as $group ) {

			// Get group capabilities.
			$group_caps = wp_list_pluck( $group['capabilities'], 'capability' );
			$caps       = array_merge( $caps, $group_caps );

		}

		return $caps;

	}

	/**
	 * Returns the option key where the Form Permissions are stored.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function get_option_key() {

		return sprintf( 'forgravity-advancedpermissions_ruleset_%d', $this->form_id );

	}

}
