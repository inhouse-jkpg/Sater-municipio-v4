<?php
/**
 * Class for removing inaccessible forms from the admin bar.
 *
 * @since 2.0
 *
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions;

use WP_Admin_Bar;

/**
 * Advanced Permissions Admin Bar class file.
 * Filters out forms user does not have access to from Admin Bar.
 *
 * @since     2.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2021, CosmicGiant
 */
class Admin_Bar {

	/**
	 * Regex pattern for detecting a Gravity Forms menu node.
	 *
	 * @since 2.0
	 * @var   string
	 */
	const NODE_ID_PATTERN = '/^gform-form-(?P<form_id>[0-9]*)-?(?P<action>edit|entries|settings|preview)?/';

	/**
	 * Removes forms from Admin Bar that user does not have access to.
	 *
	 * @since 2.0
	 */
	public static function filter_admin_bar() {

		/**
		 * Admin Bar global.
		 *
		 * @var WP_Admin_Bar $wp_admin_bar
		 */
		global $wp_admin_bar;

		// If user is immune from Advanced Permissions, exit.
		if ( ! ( $user = advancedpermissions()->get_current_user() ) || $user->is_immune() ) {
			return;
		}

		// Loop through Gravity Forms nodes, remove node if no access.
		foreach ( self::get_nodes() as $node ) {

			$form_id = self::get_node_form_id( $node );
			$action  = self::get_node_action( $node );

			// If this is a root node for a form, check if user has core capabilities.
			if ( ! $action ) {

				// If user does not have access to core capabilities for form, remove node.
				if ( ! $user->can_access_form( $form_id, true ) ) {
					$wp_admin_bar->remove_node( $node->id );
				}

				continue;

			}

			$caps_to_check = self::get_action_capabilities( $action );
			$has_cap       = false;

			if ( $caps_to_check ) {
				foreach ( $caps_to_check as $cap ) {
					if ( $user->has_form_cap( $cap, $form_id ) ) {
						$has_cap = true;
					}
				}
			}

			if ( ! $has_cap ) {
				$wp_admin_bar->remove_node( $node->id );
			}

		}

	}

	/**
	 * Returns all Gravity Forms form specific menu nodes.
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	private static function get_nodes() {

		/**
		 * Admin Bar global.
		 *
		 * @var WP_Admin_Bar $wp_admin_bar
		 */
		global $wp_admin_bar;

		$nodes = [];

		foreach ( $wp_admin_bar->get_nodes() as $node ) {

			preg_match( self::NODE_ID_PATTERN, $node->id, $matches );

			if ( empty( $matches ) || empty( $matches['form_id'] ) ) {
				continue;
			}

			$nodes[] = $node;

		}

		return $nodes;

	}

	/**
	 * Returns the Gravity Forms form ID for a menu node.
	 *
	 * @since 2.0
	 *
	 * @param object $node Admin Bar menu node.
	 *
	 * @return int|false
	 */
	private static function get_node_form_id( $node ) {

		preg_match( self::NODE_ID_PATTERN, $node->id, $matches );

		return isset( $matches['form_id'] ) ? (int) $matches['form_id'] : false;

	}

	/**
	 * Returns the Gravity Forms action for a menu node.
	 *
	 * @since 2.0
	 *
	 * @param object $node Admin Bar menu node.
	 *
	 * @return string|false
	 */
	private static function get_node_action( $node ) {

		preg_match( self::NODE_ID_PATTERN, $node->id, $matches );

		return rgar( $matches, 'action', false );

	}

	/**
	 * Returns the capabilities Gravity Forms checks for when adding a child node.
	 *
	 * @since 2.0
	 *
	 * @param string $action Action to return capabilities for.
	 *
	 * @return array|false
	 */
	private static function get_action_capabilities( $action ) {

		$actions_capabilities = [
			'edit'     => [ 'gravityforms_edit_forms' ],
			'entries'  => [ 'gravityforms_view_entries' ],
			'settings' => [ 'gravityforms_edit_forms' ],
			'preview'  => [
				'gravityforms_edit_forms',
				'gravityforms_create_form',
				'gravityforms_preview_forms',
			],
		];

		return rgar( $actions_capabilities, $action, false );

	}

}
