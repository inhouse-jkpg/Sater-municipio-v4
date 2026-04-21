<?php
/**
 * This class is responsible for handling of all the Directory explorer related feature.
 *
 * @since 4.13
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Directory explorer class.
 */
class Explorer extends Singleton {

	/**
	 * Stores the instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Dummy constructor.
	 */
	public function __construct() {}

	/**
	 * Creates the single instance of this class.
	 *
	 * @return Explorer
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new Explorer();
		}

		return self::$instance;
	}

	/**
	 * AJAX Handler:: Returns the JSON string for file explorer.
	 *
	 * @return string
	 */
	public function json_file_explorer() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You are not authorized to perform this action.', 'snapshot' ) ) );
		}

		if ( ! wp_verify_nonce( $_REQUEST['_nonce'], 'snapshot-file-explorer' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'We are unable to proceed. Please refresh the page and try again!', 'snapshot' ) ) );
		}

		$path = sanitize_text_field( $_REQUEST['spath'] );
		$root = Fs::get_root_path();

		if ( false === strpos( $path, $root ) ) {
			$path = untrailingslashit( $root ) . $path;
		}

		if ( ! realpath( $path ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'The path you\'ve provided is invalid.', 'snapshot' ) ) );
		}

		wp_send_json_success( array( 'tree' => self::tree( $path, 'ajax' ) ) );
	}

	/**
	 * Get all the exclusions.
	 *
	 * @return array
	 */
	public static function get_all_exclusions() {
		$user_exclusions = get_site_option( 'snapshot_global_exclusions', array() );
		$exclusions      = new \WPMUDEV\Snapshot4\Model\Blacklist( $user_exclusions );

		$dirs     = $exclusions->get_directories();
		$ex_files = $exclusions->get_files();

		return array_merge( $dirs, $ex_files );
	}

	public static function get_root_details() {
		$root = Fs::get_root_path();

		return array(
			'name' => untrailingslashit( basename( $root ) ),
			'path' => '/',
			'size' => filesize( $root ),
			'type' => 'dir',
		);
	}

	/**
	 * Build the tree structure.
	 *
	 * @param string $path
	 * @param string $type
	 *
	 * @return string
	 */
	public static function tree( $path, $type = 'root' ) {
		$files = Fs::sort( Fs::list( $path ) );
		if ( count( $files ) <= 0 ) {
			return '';
		}

		$excluded = self::get_all_exclusions();
		$template = new Template();
		ob_start();
			// Renders the tree structure template.
			$template->render(
				'elements/tree',
				array(
					'files'    => $files,
					'excluded' => $excluded,
					'type'     => $type,
				)
			);
		return ob_get_clean();
	}
}