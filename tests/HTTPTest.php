<?php

require_once dirname(__FILE__) . '/TestCase.php';
require_once 'HTTP/HTTP.php';
 
class HTTPTest extends Discovery_TestCase {

	public function testHTTP() {

		$http = new WP_Http();

		$options = array(
			'uri' => 'http://openxrd.org/',
			'method' => 'GET',
		);

		$response = $http->get($options['uri'], $options);

		print_r($response);
	}
}


?>
