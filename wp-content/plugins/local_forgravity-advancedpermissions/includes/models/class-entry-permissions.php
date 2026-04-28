<?php
/**
 * Advanced Permissions Entry Permissions object model.
 *
 * @since   3.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions\Models;

use CosmicGiant\Advanced_Permissions\User;

/**
 * Entry Permissions object model.
 *
 * @since     3.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2023, CosmicGiant
 */
class Entry_Permissions extends Base {

	/**
	 * Returns the final matching rule for the provided user.
	 * Defaults to current user.
	 *
	 * @since 3.0
	 *
	 * @param User $user User to get rule for.
	 *
	 * @return null|array|false Returns null if user is immune or no rules exist, array for matching rule, false if no rules match.
	 */
	public function get_rule_for_user( $user = false ) {

		if ( ! $user ) {
			$user = advancedpermissions()->get_current_user();
		}

		$rules = $this->get_rules();

		if ( $user->is_immune() || empty( $rules ) ) {
			return null;
		}

		$found_rule = false;

		// Loop through rules, find last matching rule.
		foreach ( $rules as $rule ) {

			// If no targets were selected, skip rule.
			if ( ! rgar( $rule, 'targets' ) ) {
				continue;
			}

			switch ( $rule['targetType'] ) {

				case 'role':
					foreach ( $user->roles as $user_role ) {
						if ( in_array( $user_role, $rule['targets'] ) ) {
							$found_rule = $rule;
							break;
						}
					}
					break;

				case 'user':
					if ( in_array( $user->ID, $rule['targets'] ) ) {
						$found_rule = $rule;
					}
					break;

			}

		}

		return $found_rule;

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Returns the option key where the Form Permissions are stored.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function get_option_key() {

		return sprintf( 'advancedpermissions_entry_%d', $this->form_id );

	}

}
