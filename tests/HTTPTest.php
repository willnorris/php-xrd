<?php

require_once dirname(__FILE__) . '/TestCase.php';
require_once 'HTTP/HTTP.php';
 
class HTTPTest extends Discovery_TestCase {

	public function testHTTP() {

		$http = new WP_Http();

		$response = $http->get('http://openxrd.org/');

		print_r($response);
	}
}


?>
