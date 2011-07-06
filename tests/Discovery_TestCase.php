<?php

define( 'DISCOVERY_ROOT', dirname(dirname(__FILE__)) );
set_include_path(get_include_path().PATH_SEPARATOR.DISCOVERY_ROOT . '/src');

error_reporting(E_ALL);
ini_set('display_errors', true);

//require_once 'PHPUnit/Framework.php';
@include_once dirname(__FILE__) . '/config.php';

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
