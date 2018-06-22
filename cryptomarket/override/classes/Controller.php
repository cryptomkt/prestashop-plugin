<?php
/*
 * Override the main Controller abstract class to require Composer's autoload file
 * Makes Composer packages available to your entire PrestaShop install
 */
abstract class Controller extends ControllerCore
{
	public function __construct()
	{
		// Load Composer vendor packages
		require _PS_ROOT_DIR_ . '/vendor/autoload.php';

		parent::__construct();
	}
}

?>