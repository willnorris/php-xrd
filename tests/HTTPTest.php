<?php

require_once dirname(__FILE__) . '/TestCase.php';
 
class HTTPTest extends Discovery_TestCase {

	public function testHTTP() {

		$http = new WP_Http();

		$options = array(
			'uri' => 'http://www.google.com/',
			'method' => 'GET',
		);

		$response = $http->get($options['uri'], $options);

		print_r($response);
	}
}


?>
