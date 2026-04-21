<?php
/**
 * Advanced Permissions Base object model.
 *
 * @since   3.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions\Models;

/**
 * Base object model.
 *
 * @since     3.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2023, CosmicGiant
 */
abstract class Base {

	/**
	 * Form ID object belongs to.
	 *
	 * @since 3.0
	 * @var   int
	 */
	public $form_id;

	/**
	 * Rules for object.
	 *
	 * @since 3.0
	 * @var   array
	 */
	protected $rules = [];

	/**
	 * Delete object.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function delete() {

		return $this->delete_object();

	}

	/**
	 * Get object.
	 *
	 * @since 3.0
	 *
	 * @param int $form_id Form ID to get data for.
	 *
	 * @return static
	 */
	public static function get( $form_id ) {

		return static::get_object( $form_id );

	}

	/**
	 * Returns the rules.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_rules() {

		return $this->rules;

	}

	/**
	 * Set the rules.
	 *
	 * @since 3.0
	 *
	 * @param array $rules Rules to set to the object.
	 *
	 * @return static
	 */
	public function set_rules( $rules = [] ) {

		$this->rules = $rules;

		return $this;

	}

	/**
	 * Update object.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function update() {

		// Flatten users.
		foreach ( $this->rules as $i => $rule ) {

			if ( $rule['targetType'] !== 'user' ) {
				continue;
			}

			$this->rules[ $i ]['targets'] = array_map(
				function( $target ) {
					return is_array( $target ) ? $target['value'] : $target;
				},
				$rule['targets']
			);

		}

		return $this->update_object();

	}





	// # CRUD ----------------------------------------------------------------------------------------------------------

	/**
	 * Delete object.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	protected function delete_object() {

		return delete_option( $this->get_option_key() );

	}

	/**
	 * Get object.
	 *
	 * @since 3.0
	 *
	 * @param int $form_id Form ID to get data for.
	 *
	 * @return static
	 */
	protected static function get_object( $form_id ) {

		$object          = new static();
		$object->form_id = $form_id;

		$rules = get_option( $object->get_option_key(), '[]' );
		$object->set_rules( json_decode( $rules, true ) );

		return $object;

	}

	/**
	 * Update object.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	protected function update_object() {

		// Get existing rules.
		$existing_rules = get_option( $this->get_option_key(), '[]' );
		$new_rules      = wp_json_encode( $this->rules );

		// If rules are unchanged, return.
		if ( $existing_rules === $new_rules ) {
			return true;
		}

		return update_option( $this->get_option_key(), $new_rules );

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Returns the option key where the data is stored.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	abstract protected function get_option_key();

}
