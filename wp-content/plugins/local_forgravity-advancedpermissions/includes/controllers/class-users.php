<?php
/**
 * Advanced Permissions Users controller.
 *
 * @since   3.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions\Controllers;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_REST_Users_Controller;

/**
 * Users controller.
 *
 * @since     3.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2023, CosmicGiant
 */
class Users extends Base {

	/**
	 * The REST API route base.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	protected $base = '/users';

	/**
	 * Add hooks.
	 *
	 * @since 3.0
	 */
	public function add_hooks() {

		parent::add_hooks();

		add_filter( 'rest_api_init', [ $this, 'register_routes' ] );
		add_filter( 'rest_user_query', [ $this, 'filter_rest_user_query' ], 10, 2 );

	}

	/**
	 * Register custom routes.
	 *
	 * @since 3.0
	 */
	public function register_routes() {

		// Register a new route to search for users.
		// We don't use the default wp/v2/users route to bypass the WordFence security check.
		$user_controller = new WP_REST_Users_Controller();

		register_rest_route(
			$this->get_namespace(),
			$this->base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $user_controller, 'get_items' ],
					'permission_callback' => [ $user_controller, 'get_items_permissions_check' ],
					'args'                => $user_controller->get_collection_params(),
				],
				'schema' => [ $user_controller, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->get_namespace(),
			$this->base . '/me',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_current_user' ],
					'permission_callback' => '__return_true',
				],
			]
		);

	}

	/**
	 * Exclude roles from user list query.
	 *
	 * @since  3.0
	 *
	 * @param array           $prepared_args Array of arguments for WP_User_Query.
	 * @param WP_REST_Request $request       The current request.
	 *
	 * @return array
	 */
	public function filter_rest_user_query( $prepared_args, $request ) {

		// If no excluded roles are defined, return.
		if ( ! $request->get_param( 'exclude_roles' ) ) {
			return $prepared_args;
		}

		// Get excluded roles.
		$excluded_roles = $request->get_param( 'exclude_roles' );

		// Convert to array.
		$excluded_roles = explode( ',', $excluded_roles );

		// Add to arguments.
		$prepared_args['role__not_in'] = $excluded_roles;
		unset( $prepared_args['has_published_posts'] );

		return $prepared_args;

	}

	/**
	 * Retrieves the current user.
	 *
	 * @since3.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_current_user( $request ) {

		$current_user = fg_advancedpermissions()->get_current_user();

		if ( $current_user->ID === 0 ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You are not currently logged in.' ),
				array( 'status' => 401 )
			);
		}

		return new WP_REST_Response(
			[
				'ID'           => $current_user->ID,
				'display_name' => $current_user->display_name,
				'roles'        => $current_user->roles,
			]
		);

	}

}
