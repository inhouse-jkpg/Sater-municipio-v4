<?php
/**
 * Background updates.
 *
 * @since   1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */

namespace CosmicGiant\Plugin_Skeleton\Traits;

/**
 * Background updates.
 *
 * @since   1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */
trait Background_Updates {

	/**
	 * Determines if automatic updating should be processed.
	 *
	 * @since  Unknown
	 *
	 * @param bool   $update Whether or not to update.
	 * @param object $item   The update offer object.
	 *
	 * @return bool
	 */
	public function maybe_auto_update( $update, $item ) {

		// If this is not our plugin, exit.
		if ( ! isset( $item->slug ) || is_null( $update ) ) {
			return $update;
		}

		if ( $this->is_auto_update_disabled( $update ) ) {
			$this->log_debug( __METHOD__ . '() - Aborting; auto updates disabled.' );
			return false;
		}

		return $this->can_auto_update_to_version( $item->new_version );

	}

	/**
	 * Determines if the new version can be auto-updated to.
	 *
	 * @since Unknown
	 *
	 * @param string $new_version     Version provided from Updater.
	 * @param string $current_version Current version of plugin.
	 *
	 * @return bool
	 */
	public function can_auto_update_to_version( $new_version, $current_version = false ) {

		if ( ! $current_version ) {
			$current_version = $this->_version;
		}

		$current_major = implode( '.', array_slice( preg_split( '/[.-]/', $current_version ), 0, 1 ) );
		$new_major     = implode( '.', array_slice( preg_split( '/[.-]/', $new_version ), 0, 1 ) );

		return $current_major == $new_major;

	}

	/**
	 * Determine if automatic updates are disabled.
	 *
	 * @since  1.0
	 *
	 * @param bool|null $enabled Indicates if auto updates are enabled.
	 *
	 * @return bool
	 */
	public function is_auto_update_disabled( $enabled = null ) {

		global $wp_version;

		if ( is_null( $enabled ) || version_compare( $wp_version, '5.5', '<' ) ) {
			$enabled = $this->get_plugin_setting( 'background_updates' );
		}

		$this->log_debug( __METHOD__ . ' - $enabled: ' . var_export( $enabled, true ) );

		return ! $enabled;

	}

}
