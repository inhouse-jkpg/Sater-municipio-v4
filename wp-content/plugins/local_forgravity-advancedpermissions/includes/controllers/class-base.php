<?php
/**
 * Advanced Permissions Base controller class.
 *
 * @since   3.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions\Controllers;

use GFAPI;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * The base controller class.
 *
 * @since     3.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2023, CosmicGiant
 */
abstract class Base {

	/**
	 * The REST API namespace.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	private $namespace = 'permissions';

	/**
	 * The REST API route base.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	protected $base;

	/**
	 * The data model name.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	protected $data_model;

	/**
	 * Get the REST API namespace.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_namespace() {

		return $this->namespace;

	}

	/**
	 * Get the REST API route base.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_base() {

		return $this->base;

	}

	/**
	 * Add hooks.
	 *
	 * @since 3.0
	 */
	public function add_hooks() {

		add_filter( 'rest_api_init', [ $this, 'register_routes' ] );

	}

	/**
	 * Register custom routes.
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, $this->base . '(?P<form_id>[\d]+|default)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_object' ],
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			],
		] );

		register_rest_route( $this->namespace, $this->base . '(?P<form_id>[\d]+|default)', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_object' ],
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			],
		] );

	}

	/**
	 * Get permissions object via REST API.
	 *
	 * @since  3.0
	 *
	 * @param WP_REST_Request $request Request data.
	 *
	 * @return WP_REST_Response
	 */
	public function get_object( $request ) {

		$form = $this->get_form( $request );

		// If form does not exist, return.
		if ( ! is_array( $form ) ) {

			// Prepare response payload.
			$payload = [
				'success' => false,
				'message' => esc_html__( 'Form could not be found.', 'forgravity_advancedpermissions' ),
			];

			return new WP_REST_Response( $payload, 404 );

		}

		// Get rules.
		$permissions = call_user_func( [
			'\CosmicGiant\Advanced_Permissions\Models\\' . $this->data_model,
			'get',
		], $form['id'] );
		$rules       = $permissions->get_rules();

		// Convert user targets to include display name.
		foreach ( $rules as $i => $rule ) {

			if ( $rule['targetType'] !== 'user' ) {
				continue;
			}

			$users     = get_users( [ 'include' => $rule['targets'] ] );
			$user_keys = wp_list_pluck( $users, 'ID' );

			$targets = [];
			foreach ( $rule['targets'] as $target ) {

				$user_index = array_search( $target, $user_keys, true );

				if ( $user_index === false ) {
					continue;
				}

				// @var \WP_User $user User.
				$user = $users[ $user_index ];

				$targets[] = [
					'label' => $user->display_name,
					'value' => $user->ID,
				];

			}

			$rules[ $i ]['targets'] = $targets;

		}

		// Prepare response payload.
		$payload = [
			'success' => true,
			'rules'   => $rules,
		];

		return new WP_REST_Response( $payload );

	}

	/**
	 * Update permissions via REST API.
	 *
	 * @since  3.0
	 *
	 * @param WP_REST_Request $request Request data.
	 *
	 * @return WP_REST_Response
	 */
	public function update_object( $request ) {

		// Get form, permissions.
		$form  = $this->get_form( $request );
		$rules = $request->get_param( 'rules' );

		// If form does not exist, return.
		if ( ! is_array( $form ) ) {

			// Prepare response payload.
			$payload = [
				'success' => false,
				'message' => esc_html__( 'Form could not be found.', 'forgravity_advancedpermissions' ),
			];

			return new WP_REST_Response( $payload, 404 );

		}

		$permissions = call_user_func( [ '\CosmicGiant\Advanced_Permissions\Models\\' . $this->data_model, 'get' ], $form['id'] );
		$updated     = $permissions->set_rules( $rules )->update();

		// Prepare response payload.
		$payload = [
			'success' => $updated,
			'message' => $updated ? '' : esc_html__( 'Unable to update permissions.', 'forgravity_advancedpermissions' ),
		];

		return new WP_REST_Response( $payload, $updated ? 200 : 400 );

	}

	/**
	 * Returns the form object for the provided request.
	 *
	 * @since 3.0
	 *
	 * @param WP_REST_Request $request Request data.
	 *
	 * @return array|false
	 */
	private function get_form( $request ) {

		$form_id = $request->get_param( 'form_id' );

		return $form_id === 'default' ? [ 'id' => 'default' ] : GFAPI::get_form( $form_id );

	}

}
