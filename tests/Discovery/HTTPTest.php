<?php

require_once dirname(dirname(__FILE__)) . '/TestCase.php';
require_once 'Discovery/Context.php';
 
class HTTPTest extends Discovery_TestCase {

	public function testWP() {
		@include_once 'WP_Http.php';
		if ( !class_exists('WP_Http') ) {
			error_log('Unable to find WP_Http');
			return;
		}

		require_once 'Discovery/HTTP/WP_Http.php';
		$wp_http = new Discovery_HTTP_WP();
		$context = new Discovery_Context('http://www.google.com/', $wp_http);

		$response = $context->fetch( array('uri' => $context->uri) );

		$this->assertNotNull($response);
		$this->assertEquals(200, $response['response']['code']);
		$this->assertTrue(sizeof($response['headers']) > 0);
	}
	
	public function testZend() {
		@include_once 'Zend/Http/Client.php';
		if ( !class_exists('Zend_Http_Client') ) {
			error_log('Unable to find Zend_Http_Client');
			return;
		}

		require_once 'Discovery/HTTP/Zend.php';
		$zend = new Discovery_HTTP_Zend();
		$context = new Discovery_Context('http://www.google.com/', $zend);

		$response = $context->fetch( array('uri' => $context->uri) );

		$this->assertNotNull($response);
		$this->assertEquals(200, $response['response']['code']);
		$this->assertTrue(sizeof($response['headers']) > 0);
	}

	public function testPEAR() {
		@include_once 'HTTP/Request2.php';
		if ( !class_exists('HTTP_Request2') ) {
			error_log('Unable to find HTTP_Request2');
			return;
		}

		require_once 'Discovery/HTTP/Pear.php';
		$pear = new Discovery_HTTP_Pear();
		$context = new Discovery_Context('http://www.google.com/', $pear);

		$response = $context->fetch( array('uri' => $context->uri) );

		$this->assertNotNull($response);
		$this->assertEquals(200, $response['response']['code']);
		$this->assertTrue(sizeof($response['headers']) > 0);
	}
}


?>
