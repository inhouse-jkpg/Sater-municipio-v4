<?php
/**
 * Advanced Permissions Base view class.
 *
 * @since   3.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions\Views;

use GFAPI;

/**
 * The base View class.
 *
 * @since     3.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2023, CosmicGiant
 */
abstract class Base {

	/**
	 * The current Form object.
	 *
	 * @since 3.0
	 *
	 * @var array|false
	 */
	protected $form;

	/**
	 * The current Form ID.
	 *
	 * @since 3.0
	 *
	 * @var int
	 */
	protected $form_id;

	/**
	 * Add hooks.
	 *
	 * @since 3.0
	 */
	abstract public function add_hooks();





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Returns the current Form object.
	 *
	 * @since 3.0
	 *
	 * @return array|false
	 */
	protected function get_current_form() {

		if ( isset( $this->form ) ) {
			return $this->form;
		}

		if ( ! $form_id = $this->get_form_id() ) { // phpcs:ignore
			return false;
		}

		$this->form = GFAPI::get_form( $form_id );

		return $this->form;

	}

	/**
	 * Returns the current form ID.
	 *
	 * @since 3.0
	 *
	 * @return false|int Form ID or false if not found.
	 */
	protected function get_form_id() {

		if ( isset( $this->form_id ) ) {
			return $this->form_id;
		}

		$this->form_id = rgget( 'id' ) ? (int) $_GET['id'] : false; // phpcs:ignore

		return $this->form_id;

	}

}
