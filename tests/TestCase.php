<?php

define( 'DISCOVERY_ROOT', dirname(dirname(__FILE__)) );
set_include_path(DISCOVERY_ROOT . PATH_SEPARATOR . get_include_path());

error_reporting(E_ALL);
ini_set('display_errors', true);

require_once 'PHPUnit/Framework.php';

/**
 * A simple utils class for methods needed
 * during some of the tests
 */
abstract class Discovery_TestCase extends PHPUnit_Framework_TestCase {

	/** Directory containing test data files */
	protected $data_dir;

	public function __construct() {
		parent::__construct();

		$this->data_dir = DISCOVERY_ROOT . '/tests/data/';
	}
}

?>
