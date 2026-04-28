<?php
/**
 * Manages Redirects functionality.
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Redirects;

use SmartCrawl\MaxMind\GeoDB;
use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Controllers;
use SmartCrawl\String_Utils;
use SmartCrawl\Admin\Settings\Admin_Settings;

/**
 * Redirects Controller.
 */
class Controller extends Controllers\Controller {

	use Singleton;

	/**
	 * Redirects table.
	 *
	 * @var Database_Table
	 */
	private $redirects_table;

	/**
	 * Redirects utility.
	 *
	 * @var Utils
	 */
	private $utils;

	/**
	 * Should this module run?
	 *
	 * @return bool
	 */
	public function should_run() {
		$adv_tools_options = Settings::get_component_options( Settings::COMP_AUTOLINKS, array() );

		return ( ! isset( $adv_tools_options['disable-adv-tools'] ) || ! $adv_tools_options['disable-adv-tools'] ) &&
			Admin_Settings::is_tab_allowed( Settings::TAB_AUTOLINKS );
	}

	/**
	 * Initialization method.
	 *
	 * @return void
	 */
	protected function init() {
		$this->redirects_table = Database_Table::get();
		$this->utils           = Utils::get();

		add_action( 'wp', array( $this, 'intercept' ) );
		add_action( 'wp', array( $this, 'smartcrawl_page_redirect' ), 99, 1 );
		add_action( 'plugins_loaded', array( $this, 'maybe_create_table' ), - 10 );
		add_action( 'wds_plugin_update', array( $this, 'upgrade_table' ), 10, 2 );
		add_action( 'wp_ajax_wds_save_redirect', array( $this, 'save_redirect' ) );
		add_action( 'wp_ajax_wds_delete_redirect', array( $this, 'delete_redirect' ) );
		add_action( 'wp_ajax_wds_bulk_update_redirects', array( $this, 'bulk_update_redirects' ) );
		add_action( 'wp_ajax_wds_import_redirects_from_csv', array( $this, 'import_redirects_from_csv' ) );
		add_action( 'wp_ajax_wds_export_csv', array( $this, 'export_csv' ) );
		add_action( 'admin_notices', array( $this, 'display_upgrade_notice' ) );

		$opts = Settings::get_options();
		if ( ! empty( $opts['redirect-attachments'] ) ) {
			add_action( 'template_redirect', array( $this, 'redirect_attachments' ) );
		}
	}

	/**
	 * Displays upgrade notice.
	 *
	 * @return void
	 */
	public function display_upgrade_notice() {
		$key                  = 'wds_redirect_upgrade_217';
		$redirects_admin_url  = Admin_Settings::admin_url( Settings::TAB_AUTOLINKS ) . '&tab=tab_url_redirection';
		$dismissed_messages   = get_user_meta( get_current_user_id(), 'wds_dismissed_messages', true );
		$is_message_dismissed = \smartcrawl_get_array_value( $dismissed_messages, $key ) === true;
		$is_version_218       = version_compare( SMARTCRAWL_VERSION, '2.18.0', '=' );
		if (
			$is_message_dismissed ||
			! $is_version_218 ||
			! current_user_can( 'manage_options' )
		) {
			return;
		}
		?>
		<div
			class="notice-info notice is-dismissible wds-native-dismissible-notice"
			data-message-key="<?php echo esc_attr( $key ); ?>"
		>
			<p>
				<strong><?php esc_html_e( 'SmartCrawl URL redirects have been upgraded', 'wds' ); ?></strong>
			</p>
			<p style="margin-bottom: 15px;">
				<?php esc_html_e( "We've changed how URL redirects are stored, and your existing redirects have been upgraded accordingly. Please check your existing redirects to ensure they work as expected.", 'wds' ); ?>
			</p>
			<a
				href="<?php echo esc_attr( $redirects_admin_url ); ?>"
				class="button button-primary"
			>
				<?php esc_html_e( 'Go to Redirects', 'wds' ); ?>
			</a>
			<a href="#" class="wds-native-dismiss"><?php esc_html_e( 'Dismiss', 'wds' ); ?></a>
			<p></p>
		</div>
		<?php
	}

	/**
	 * Handles to create database table if it's not existing.
	 *
	 * @return void
	 */
	public function maybe_create_table() {
		$db_table = Database_Table::get();
		if ( ! $db_table->table_exists() ) {
			$db_table->create_table();
		}
	}

	/**
	 * Upgrades pre v3.8.0 redirection table to latest structure.
	 *
	 * @param string       $new_version New version.
	 * @param string|false $old_version Old version.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function upgrade_table( $new_version, $old_version ) {
		if ( ! $old_version || version_compare( $old_version, '3.8.0', '<' ) ) {
			Database_Table::get()->create_table();
		}
	}

	/**
	 * Retrieves current path.
	 *
	 * @return string
	 */
	private function get_current_path() {
		return $this->utils->source_to_path( $this->get_current_url() );
	}

	/**
	 * Retrieves current url.
	 *
	 * @return string|false
	 */
	private function get_current_url() {
		if ( ! isset( $_SERVER['HTTP_HOST'] ) ) {
			return false;
		}

		$protocol = is_ssl() ? 'https:' : 'http:';
		$domain   = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
		$path     = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( rawurldecode( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), PHP_URL_PATH ) : '';

		return esc_url_raw( $protocol . '//' . $domain . $path );
	}

	/**
	 * Intercepts the page and redirects if needs be.
	 */
	public function intercept() {
		$redirect = $this->find_plain_redirect( $this->get_current_path() );

		if ( $redirect ) {
			if ( $this->utils->is_post_type( $redirect->get_options() ) ) {
				$destination = get_permalink( $redirect->get_destination() );
			} else {
				$destination = $redirect->get_absolute_destination();
			}
		} else {
			$redirect = $this->find_regex_redirect( $this->get_current_url() );
			if ( ! $redirect ) {
				return false;
			}
			$destination = $this->find_regex_destination( $redirect );
		}

		$type = $redirect->get_type();

		if ( GeoDB::get()->get_license() ) {
			$geo_rules = $redirect->get_rules();

			if ( ! empty( $geo_rules ) ) {
				$country = GeoDB::get()->get_country_by_ip();

				$rule = $this->find_geo_destination( $geo_rules, $country );

				if ( ! empty( $rule['url'] ) ) {
					$destination = $rule['url'];
				}
			}
		}

		if ( $destination && $type && Utils::get()->get_full_url( $this->get_current_path() ) !== Utils::get()->get_full_url( $destination ) ) {
			// We're here, so redirect.
			nocache_headers();

			wp_redirect( // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				$this->to_safe_redirection( $destination, $type ),
				$type
			);
			die;
		}
	}

	/**
	 * Finds plain redirect from path.
	 *
	 * @param string $path Path to be used for the search.
	 * @return false|Item
	 */
	private function find_plain_redirect( $path ) {
		$redirects = $this->redirects_table->get_redirects_by_path( $path );
		if ( empty( $redirects ) ) {
			return false;
		}

		$redirect = $this->find_match( $redirects );
		if ( ! $redirect ) {
			return false;
		}

		return $redirect;
	}

	/**
	 * Finds regex redirect from source.
	 *
	 * @param string $source Source.
	 *
	 * @return Item
	 */
	private function find_regex_redirect( $source ) {
		$redirects = $this->redirects_table->get_redirects_by_source_regex( $source );
		if ( ! empty( $redirects ) && is_array( $redirects ) ) {
			// We need to weed out partial matches and look for an exact match.
			foreach ( $redirects as $redirect ) {
				$pattern = $redirect->get_source();
				if ( ! String_Utils::starts_with( $pattern, '^' ) ) {
					$pattern = "^{$pattern}";
				}
				if ( ! String_Utils::ends_with( $pattern, '$' ) ) {
					$pattern = "{$pattern}$";
				}
				$pattern = str_replace( '~', '\~', $pattern );
				if ( preg_match( "~$pattern~", $source ) ) {
					return $redirect;
				}
			}
		}

		return null;
	}

	/**
	 * Finds regex destination from redirect.
	 *
	 * @param Item $redirect Redirect item.
	 */
	private function find_regex_destination( $redirect ) {
		$pattern = str_replace( '~', '\~', $redirect->get_source() );

		return preg_replace(
			"~$pattern~",
			$redirect->get_absolute_destination(),
			$this->get_current_url()
		);
	}

	/**
	 * Finds Geolocation destination from redirect.
	 *
	 * @param array  $rules Geolocation rules.
	 * @param string $country Country ISO code.
	 *
	 * @return array|false
	 */
	private function find_geo_destination( $rules, $country ) {
		foreach ( $rules as $rule ) {
			if ( ! property_exists( $rule, 'indicate' ) && in_array( $country, $rule->countries, true ) ) {
				return (array) $rule;
			} elseif ( property_exists( $rule, 'indicate' ) && ! in_array( $country, $rule->countries, true ) ) {
				return (array) $rule;
			}
		}

		return false;
	}

	/**
	 * Retrieves query vars from url.
	 *
	 * @param string $url Url to be used.
	 * @return array
	 */
	private function get_url_query_vars( $url ) {
		parse_str(
			wp_parse_url( $url, PHP_URL_QUERY ) || '',
			$query_vars
		);

		return $query_vars;
	}

	/**
	 * Finds match from redirects.
	 *
	 * @param Item[] $redirects Redirect items.
	 *
	 * @return Item|null
	 */
	public function find_match( $redirects ) {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return null;
		}

		$current_query_vars = $this->get_url_query_vars( rawurldecode( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );

		foreach ( $redirects as $redirect ) {
			$redirect_query_vars = $this->get_url_query_vars( $redirect->get_source() );
			if ( \smartcrawl_arrays_same( $redirect_query_vars, $current_query_vars ) ) {
				return $redirect;
			}
		}

		return null;
	}

	/**
	 * Converts the redirection to a safe one
	 *
	 * @param string $destination Raw URL.
	 * @param int    $type        Type.
	 *
	 * @return string Safe redirection URL
	 */
	private function to_safe_redirection( $destination, $type ) {
		$fallback = apply_filters( 'wp_safe_redirect_fallback', home_url(), $type );
		$filter   = $this->allowed_hosts_filter( $destination );

		add_filter( 'allowed_redirect_hosts', $filter );

		$destination = wp_sanitize_redirect( $destination );
		$destination = wp_validate_redirect( $destination, $fallback );

		remove_filter( 'allowed_redirect_hosts', $filter );

		return $destination;
	}

	/**
	 * Redirects attachments to parent post
	 *
	 * If we can't determine parent post type,
	 * we at least throw the noindex header.
	 *
	 * Respects the `redirect-attachment-images_only` sub-option,
	 *
	 * @return void
	 */
	public function redirect_attachments() {
		if ( ! is_attachment() ) {
			return;
		}

		$opts = Settings::get_options();
		if ( ! empty( $opts['redirect-attachments-images_only'] ) ) {
			$type = get_post_mime_type();
			if ( ! preg_match( '/^image\//', $type ) ) {
				return;
			}
		}

		// Get attachment URL.
		$url = wp_get_attachment_url( get_queried_object_id() );

		if ( ! empty( $url ) ) {
			wp_safe_redirect( $url, 301 );
			die;
		}

		// No URL found, let's noindex.
		header( 'X-Robots-Tag: noindex', true );
	}

	/**
	 * Performs page redirect
	 */
	public function smartcrawl_page_redirect() {
		global $post;

		// Fix redirection on archive pages - do not redirect if not singular.
		// Fixes: https://app.asana.com/0/46496453944769/505196129561557/f.
		if ( ! is_singular() || empty( $post->ID ) ) {
			return false;
		}

		if ( ! apply_filters( 'wds_process_redirect', true ) ) {
			return false;
		} // Allow optional filtering out.

		$redirect = \smartcrawl_get_value( 'redirect', $post->ID );
		if ( $post && $redirect ) {
			wp_redirect( // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				$this->sanitize_post_redirect( $redirect ),
				301
			);
			exit;
		}

		return true;
	}

	/**
	 * Sanitizses post redirect.
	 *
	 * @param string $destination Redirect destination.
	 *
	 * @return mixed|string
	 */
	private function sanitize_post_redirect( $destination ) {
		$filter = $this->allowed_hosts_filter( $destination );

		add_filter( 'allowed_redirect_hosts', $filter );

		$destination = wp_sanitize_redirect( $destination );
		$destination = wp_validate_redirect( $destination, home_url() );

		remove_filter( 'allowed_redirect_hosts', $filter );

		return $destination;
	}

	/**
	 * Ajax handler to save redirect.
	 *
	 * @return void
	 */
	public function save_redirect() {
		$data = $this->get_request_data();

		if ( empty( $data ) ) {
			wp_send_json_error( __( 'There is no data to proceed with your request.', 'wds' ) );
		}

		$id          = intval( \smartcrawl_get_array_value( $data, 'id' ) );
		$source      = \smartcrawl_get_array_value( $data, 'source' );
		$destination = \smartcrawl_get_array_value( $data, 'destination' );
		$type        = \smartcrawl_get_array_value( $data, 'type' );
		$title       = \smartcrawl_get_array_value( $data, 'title' );
		$options     = \smartcrawl_get_array_value( $data, 'options' );
		$rules       = \smartcrawl_get_array_value( $data, 'rules' );

		if ( empty( $source ) || ( empty( $destination ) && empty( $rules ) ) ) {
			wp_send_json_error( __( 'The redirect from/to URL is empty.', 'wds' ) );
		}

		$redirect_item = $this->utils->create_redirect_item( $source, $destination, $type, $title, $options );

		if ( $redirect_item->is_regex() && $this->is_source_regex_invalid( $source ) ) {
			wp_send_json_error( array( 'message' => 'Invalid regex source.' ) );
		}

		if ( $id ) {
			$redirect_item->set_id( $id );
		}

		if ( ! empty( $rules ) ) {
			$redirect_item->set_rules( $rules );
		}

		$table = Database_Table::get();
		$saved = $table->save_redirect( $redirect_item );

		if ( $saved ) {
			$redirect_item->set_id( $saved );

			$result = $redirect_item->deflate();

			if ( $this->utils->is_post_type( $result['options'] ) ) {
				$post_url = get_permalink( $result['destination'] );

				if ( $post_url ) {
					$result['permalink'] = $post_url;
				} else {
					wp_send_json_error( array( 'message' => 'Unable to retrieve the post/page URL from the specified ID.' ) );
				}
			}

			wp_send_json_success( $result );
		}

		wp_send_json_error();
	}

	/**
	 * Retrieves redirects from database table.
	 *
	 * @return array|false
	 */
	public function get_redirects() {
		$redirects = Database_Table::get()->get_deflated_redirects();

		if ( ! $redirects ) {
			return false;
		}

		if ( ! $this->utils ) {
			$this->utils = Utils::get();
		}

		foreach ( $redirects as $key => $redirect ) {
			if ( $this->utils->is_post_type( $redirect['options'] ) ) {
				$post_url = get_permalink( $redirect['destination'] );

				if ( $post_url ) {
					$redirect['permalink'] = $post_url;
				}

				$redirects[ $key ] = $redirect;
			}
		}

		return $redirects;
	}

	/**
	 * Checks if a source regex is invalid.
	 *
	 * @param string $source Regex source.
	 *
	 * @return bool
	 */
	private function is_source_regex_invalid( $source ) {
		$with_escaped_delimiter = str_replace( '~', '\~', $source );

		return @preg_match( "~$with_escaped_delimiter~", null ) === false; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Ajax handler to delete a redirect.
	 *
	 * @return void
	 */
	public function delete_redirect() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error();
		}

		$ids     = \smartcrawl_get_array_value( $data, 'ids' );
		$table   = Database_Table::get();
		$deleted = $table->delete_redirects( $ids );
		if ( $deleted ) {
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Ajax handler to bulk update redirects.
	 *
	 * @return void
	 */
	public function bulk_update_redirects() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error();
		}

		$ids       = \smartcrawl_get_array_value( $data, 'ids' );
		$table     = Database_Table::get();
		$redirects = $table->get_redirects( $ids );
		if ( ! $redirects ) {
			wp_send_json_error();
		}

		$destination = sanitize_text_field( \smartcrawl_get_array_value( $data, 'destination' ) );
		$type        = intval( \smartcrawl_get_array_value( $data, 'type' ) );
		$response    = array();
		foreach ( $ids as $id ) {
			$redirect = \smartcrawl_get_array_value( $redirects, $id );
			if ( ! $redirect ) {
				wp_send_json_error();
			}

			$redirect->set_destination( $destination );
			$redirect->set_type( $type );

			$options = $redirect->get_options();

			if ( ! empty( $options ) ) {
				$options = array_filter(
					$options,
					function( $opt ) {
						return 'page' !== $opt && 'post' !== $opt;
					}
				);

				$redirect->set_options(
					empty( $options ) || ! is_array( $options )
					? array()
					: \smartcrawl_clean( $options )
				);
			}

			$redirect->set_rules( array() );

			$response[ $id ] = $redirect->deflate();
		}

		$is_updated = $table->update_redirects( $redirects );
		if ( false === $is_updated ) {
			wp_send_json_error();
		}

		wp_send_json_success( $response );
	}

	/**
	 * Ajax handler to import redirects from CSV.
	 *
	 * @return void
	 */
	public function import_redirects_from_csv() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error();
		}

		$file_size = \smartcrawl_get_array_value( $_FILES, array( 'file', 'size' ) );
		if ( $file_size > 1000000 ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Please select a file under 1MB.', 'wds' ),
				)
			);
		}

		$file_name = \smartcrawl_get_array_value( $_FILES, array( 'file', 'tmp_name' ) );
		$file_type = \smartcrawl_get_array_value( $_FILES, array( 'file', 'type' ) );
		if ( ! in_array( $file_type, \smartcrawl_csv_mime_types(), true ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Only CSV files are supported.', 'wds' ),
				)
			);
		}

		$file = fopen( $file_name, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		if ( ! $file ) {
			wp_send_json_error();
		}

		$errors    = false;
		$redirects = array();

		while ( $redirect_data = fgetcsv( $file ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			list( $source, $destination, $type, $regex, $title ) = $redirect_data;
			if ( empty( $source ) || empty( $destination ) ) {
				continue;
			}

			$options       = empty( $regex )
				? array()
				: array( 'regex' );
			$redirect_item = $this->utils->create_redirect_item( $source, $destination, $type, $title, $options );
			if ( $redirect_item->is_regex() && $this->is_source_regex_invalid( $source ) ) {
				$errors = true;
			} else {
				$redirects[] = $redirect_item;
			}
		}

		fclose( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		if ( $errors ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Some entries have invalid values. Please try again!', 'wds' ),
				)
			);
		}

		if ( empty( $redirects ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'No valid redirects were found, please check your file.', 'wds' ),
				)
			);
		}

		$inserted = $this->redirects_table->insert_redirects( $redirects );
		if ( ! $inserted ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'An error occurred while inserting CSV data into the database.', 'wds' ),
				)
			);
		}
		wp_send_json_success(
			array(
				'count'     => $inserted,
				'redirects' => $this->redirects_table->get_deflated_redirects(),
			)
		);
	}

	/**
	 * Ajax handler to export CSV.
	 *
	 * @return void
	 */
	public function export_csv() {
		ob_start();
		$redirects = $this->redirects_table->get_redirects();
		if ( ! $redirects ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Please save some redirects first.', 'wds' ),
				)
			);
		}

		$stdout = fopen( 'php://output', 'w' );
		foreach ( $redirects as $redirect ) {
			$regex = is_array( $redirect->get_options() )
				? array_search( 'regex', $redirect->get_options(), true ) === false ? 0 : 1
				: 0;

			fputcsv(
				$stdout,
				array(
					$redirect->get_source(),
					$redirect->get_destination(),
					$redirect->get_type(),
					$regex,
					$redirect->get_title(),
				)
			);
		}

		wp_send_json_success(
			array(
				'content' => ob_get_clean(),
			)
		);
	}

	/**
	 * Retrieves HTTP Request data.
	 *
	 * @return array|mixed
	 */
	private function get_request_data() {
		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wds_nonce'] ) ), 'wds-redirects-nonce' ) ? stripslashes_deep( $_POST ) : array();
	}

	/**
	 * Retrieves only allowed hosts.
	 *
	 * @param string $destination Destination.
	 *
	 * @return Closure
	 */
	private function allowed_hosts_filter( $destination ) {
		return function ( $allowed_hosts ) use ( $destination ) {
			$host = \smartcrawl_get_array_value(
				wp_parse_url( $destination ),
				'host'
			);
			if ( empty( $host ) || ! is_array( $allowed_hosts ) ) {
				return $allowed_hosts;
			}

			return array_unique(
				array_merge(
					$allowed_hosts,
					array( $host )
				)
			);
		};
	}
}