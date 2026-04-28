<?php
/**
 * Advanced Permissions Form Permissions controller.
 *
 * @since   3.0
 * @package CosmicGiant\Advanced_Permissions
 */

namespace CosmicGiant\Advanced_Permissions\Controllers;

/**
 * Form Permissions controller.
 *
 * @since     3.0
 * @package   CosmicGiant\Advanced_Permissions
 * @author    CosmicGiant
 * @copyright Copyright (c) 2023, CosmicGiant
 */
class Form_Permissions extends Base {

	/**
	 * The REST API route base.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	protected $base = '/form/';

	/**
	 * The data model name.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	protected $data_model = 'Form_Permissions';

}
