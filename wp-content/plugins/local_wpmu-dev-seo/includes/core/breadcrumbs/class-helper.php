<?php
/**
 * Breadcrumbs helper functionality.
 *
 * @since   3.5.2
 * @package SmartCrawl
 */

namespace SmartCrawl\Breadcrumbs;

use SmartCrawl\Settings;

/**
 * Breadcrumbs helper class.
 */
class Helper {

	/**
	 * Check if breadcrumb module is active.
	 *
	 * @since 3.5.0
	 * @since 3.5.2 Moved and renamed.
	 *
	 * @return bool
	 */
	public static function is_active() {
		return Settings::get_setting( Settings::COMP_BREADCRUMBS );
	}

	/**
	 * Get a single breadcrumb option value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default_value Default value.
	 *
	 * @return mixed
	 *
	 * @since 3.5.2 Moved to helper.
	 *
	 * @since 3.5.0
	 */
	public static function get_option( $key, $default_value = false ) {
		$options = Settings::get_component_options( Settings::COMP_BREADCRUMBS );

		$option = isset( $options[ $key ] ) ? $options[ $key ] : $default_value;

		/**
		 * Filter to modify breadcrumbs option value.
		 *
		 * @since 3.5.0
		 *
		 * @param mixed  $option  Option value.
		 * @param string $key     Setting key.
		 * @param mixed  $default_value Default value.
		 */
		return apply_filters( 'smartcrawl_breadcrumbs_get_option', $option, $key, $default_value );
	}

	/**
	 * Get separator element.
	 *
	 * If a custom separator is entered, use it.
	 *
	 * @since 3.5.0
	 * @since 3.5.2 Moved to helper.
	 *
	 * @return string
	 */
	public static function get_separator() {
		// When a custom separator is set.
		$custom_separator = self::get_option( 'custom_sep' );
		if ( ! empty( $custom_separator ) ) {
			$separator = wp_strip_all_tags( $custom_separator );
		} else {
			$separator = self::get_option( 'separator', 'greater-than' );
			$separator = \smartcrawl_get_separators( $separator );
			$separator = is_array( $separator ) ? '>' : $separator;
		}

		/**
		 * Filter to modify breadcrumbs separator.
		 *
		 * @since 3.5.0
		 *
		 * @param string $separator Separator.
		 */
		return apply_filters( 'smartcrawl_breadcrumbs_get_separator', $separator );
	}
}