<?php
/**
 * Advanced Permissions Entry Permissions controller.
 *
 * @since   3.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions\Controllers;

/**
 * Entry Permissions controller.
 *
 * @since     3.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2023, CosmicGiant
 */
class Entry_Permissions extends Base {

	/**
	 * The REST API route base.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	protected $base = '/entry/';

	/**
	 * The data model name.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	protected $data_model = 'Entry_Permissions';

}
