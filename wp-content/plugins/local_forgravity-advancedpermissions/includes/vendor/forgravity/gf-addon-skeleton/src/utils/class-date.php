<?php
/**
 * Date Class
 *
 * @since 1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */

namespace CosmicGiant\Plugin_Skeleton\Utils;

use DateTimeZone;
use WP_Error;

defined( 'ABSPATH' ) || die();

/**
 * Date Class.
 *
 * @since 1.0
 *
 * @package CosmicGiant\Plugin_Skeleton
 */
class Date {

	const FORMAT_DATE = 'Y-m-d';

	const FORMAT_TIME = 'H:i:s';

	const FORMAT_DATETIME = 'Y-m-d H:i:s';

	const FORMAT_DATETIME_NO_SECONDS = 'Y-m-d H:i';

	/**
	 * The datetime format.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public $format;

	/**
	 * The datetime string, can also be in the UNIX timestamp format.
	 *
	 * @since 1.0
	 *
	 * @var string|int
	 */
	public $datetime;

	/**
	 * The timezone.
	 *
	 * @since 1.0
	 *
	 * @var DateTimeZone
	 */
	public $timezone;

	/**
	 * Initialize Date class.
	 *
	 * @since 1.0
	 *
	 * @param string       $format   PHP date format.
	 * @param int|string   $datetime Unix timestamp or DateTime string.
	 * @param DateTimeZone $timezone Timezone.
	 */
	public function __construct( $format = null, $datetime = null, $timezone = null ) {

		$this->set_format( $format );
		$this->set_datetime( $datetime );
		$this->set_timezone( $timezone );

	}

	/**
	 * Format the defined timestamp.
	 *
	 * Included logic forked from wp_date().
	 *
	 * @since 1.0
	 *
	 * @param string $format PHP date format.
	 *
	 * @return string|false
	 */
	public function format( $format = null ) {

		global $wp_locale, $wp_version;

		if ( $format ) {
			$this->set_format( $format );
		}

		if ( $this->is_unix_timestamp( $this->datetime ) ) {
			if ( $this->format === 'U' ) {
				// If the format is U (timestamp), we skip the following process to convert the time based on timezone.
				// This is due to how PHP 5 and 7 handle DateTime::format( 'U' ) differently. PHP 5 will return the timestamp in the given timezone; while PHP 7 will ignore that.
				return $this->datetime;
			}

			$datetime = date_create( '@' . $this->datetime );
			$datetime->setTimezone( $this->timezone );
		} else {
			$datetime = date_create( $this->datetime, $this->timezone );

			if ( ! $datetime ) {
				return false;
			}
		}

		if ( empty( $wp_locale->month ) || empty( $wp_locale->weekday ) ) {
			return $datetime->format( $this->format );
		}

		// We need to unpack shorthand `r` format because it has parts that might be localized.
		$format = preg_replace( '/(?<!\\\\)r/', DATE_RFC2822, $this->format );

		$new_format    = '';
		$format_length = strlen( $format );
		$month         = $wp_locale->get_month( $datetime->format( 'm' ) );
		$weekday       = $wp_locale->get_weekday( $datetime->format( 'w' ) );

		for ( $i = 0; $i < $format_length; $i ++ ) {
			switch ( $format[ $i ] ) {
				case 'D':
					$new_format .= addcslashes( $wp_locale->get_weekday_abbrev( $weekday ), '\\A..Za..z' );
					break;
				case 'F':
					$new_format .= addcslashes( $month, '\\A..Za..z' );
					break;
				case 'l':
					$new_format .= addcslashes( $weekday, '\\A..Za..z' );
					break;
				case 'M':
					$new_format .= addcslashes( $wp_locale->get_month_abbrev( $month ), '\\A..Za..z' );
					break;
				case 'a':
					$new_format .= addcslashes( $wp_locale->get_meridiem( $datetime->format( 'a' ) ), '\\A..Za..z' );
					break;
				case 'A':
					$new_format .= addcslashes( $wp_locale->get_meridiem( $datetime->format( 'A' ) ), '\\A..Za..z' );
					break;
				case '\\':
					$new_format .= $format[ $i ];

					// If character follows a slash, we add it without translating.
					if ( $i < $format_length ) {
						$new_format .= $format[ ++$i ];
					}
					break;
				default:
					$new_format .= $format[ $i ];
					break;
			}
		}

		$date = $datetime->format( $new_format );

		if ( version_compare( $wp_version, '5.4.0', '<' ) ) {
			$date = wp_maybe_decline_date( $date );
		} else {
			$date = wp_maybe_decline_date( $date, $format );
		}

		return $date;

	}





	// # SETTERS -------------------------------------------------------------------------------------------------------

	/**
	 * Output PHP date format.
	 *
	 * @since 1.0
	 *
	 * @param string $format PHP date format.
	 */
	public function set_format( $format = null ) {

		if ( $format === 'timestamp' || ! $format ) {
			$format = 'U';
		}

		$this->format = $format;

	}

	/**
	 * Set the datetime string.
	 *
	 * @since 1.0
	 *
	 * @param string|int $datetime The datetime string, can also be in the UNIX timestamp format.
	 *
	 * @return void
	 */
	public function set_datetime( $datetime = '' ) {

		if ( empty( $datetime ) ) {
			$this->datetime = time();
			return;
		}

		if ( $this->is_unix_timestamp( $datetime ) ) {
			$this->datetime = $datetime;
			return;
		}

		if ( stripos( $datetime, 'last' ) === 0 ) {
			$without_last = trim( str_ireplace( 'last', '', $datetime ) );

			if ( is_numeric( $without_last[0] ) ) {
				$this->datetime = '-' . $without_last;
				return;
			}
		}

		if ( stripos( $datetime, 'ago' ) === false && ctype_digit( substr( $datetime, 0, 1 ) ) && strtotime( '-' . $datetime ) > 0 ) {
			$this->datetime = '-' . $datetime;
			return;
		}

		$this->datetime = $datetime;

	}

	/**
	 * Set the output timezone.
	 *
	 * @since 1.0
	 *
	 * @param DateTimeZone $timezone Timezone.
	 *
	 * @return void|WP_Error
	 */
	public function set_timezone( $timezone = null ) {

		if ( $timezone && ! is_a( $timezone, 'DateTimeZone' ) ) {
			return new WP_Error( 'invalid_timezone', 'Timezone must be a DateTimeZone instance.' );
		}

		if ( ! $timezone ) {
			if ( function_exists( '\wp_timezone' ) ) {
				$timezone = wp_timezone();
			} else {
				$timezone = new DateTimeZone( $this->wp_timezone_string() );
			}
		}

		$this->timezone = $timezone;

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Determines if a provided string is a unix timestamp.
	 *
	 * @since 1.0
	 *
	 * @param int|string $string Potential unix timestamp.
	 *
	 * @return bool
	 */
	public static function is_unix_timestamp( $string ) {

		return ( is_numeric( $string ) && (int) $string == $string )
			&& ( $string <= PHP_INT_MAX )
			&& ( $string >= ~PHP_INT_MAX );

	}

	/**
	 * Clone of wp_timezone_string() for sites running WordPress <5.3.0.
	 *
	 * Retrieves the timezone from site settings as a string.
	 *
	 * Uses the `timezone_string` option to get a proper timezone if available,
	 * otherwise falls back to an offset.
	 *
	 * @since 1.0
	 *
	 * @return string PHP timezone string or a ±HH:MM offset.
	 */
	private function wp_timezone_string() {

		$timezone_string = get_option( 'timezone_string' );

		if ( $timezone_string ) {
			return $timezone_string;
		}

		$offset  = (float) get_option( 'gmt_offset' );
		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );

		$sign     = ( $offset < 0 ) ? '-' : '+';
		$abs_hour = abs( $hours );
		$abs_mins = abs( $minutes * 60 );

		return sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

	}





	// # HELPER METHODS FOR COMPARISON ---------------------------------------------------------------------------------

	/**
	 * Check if the current Date object is expired.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function is_expired() {

		return ! $this->greater_than();

	}

	/**
	 * Compare the current Date object with another datetime string.
	 *
	 * @since 1.0
	 *
	 * @param int|string   $datetime The datetime string or timestamp.
	 * @param DateTimeZone $timezone The DateTimeZone object.
	 *
	 * @return bool
	 */
	private function greater_than( $datetime = null, $timezone = null ) {

		if ( ! $datetime ) {
			$datetime = ( new self( self::FORMAT_DATETIME, wp_date( self::FORMAT_DATETIME ) ) )->format( 'U' );
		}

		if ( ! $timezone ) {
			$timezone = $this->timezone;
		}

		if ( ! $this->is_unix_timestamp( $datetime ) ) {
			$datetime = ( new self( null, $datetime, $timezone ) )->format();
		}

		return $this->format( 'U' ) > $datetime;

	}

}
