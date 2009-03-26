<?php

require_once 'Discovery/HTTP/Adaptor.php';
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Response.php';

class Discovery_HTTP_Zend implements Discovery_HTTP_Adaptor {

	private $zend;

	public function __construct() {
		$this->zend = new Zend_Http_Client();
	}

	public function fetch($request) {
		$this->zend->resetParameters();

		// map configuration options
		$zend_config = array(
			'maxredirects' => $request['redirection'],
			'useragent' => $request['user-agent'],
			'timeout' => $request['timeout'],
			'httpversion' => $request['httpversion'],
			'keepalive' => false,
			'storeresponse' => true,
		);
		$this->zend->setConfig( $zend_config );

		// setup request
		$this->zend->setUri( $request['uri'] );
		$this->zend->setMethod( strtoupper($request['method']) );
		$this->zend->setHeaders( $request['headers'] );
		$this->zend->setRawData( $request['body'] );
		if (!empty($request['cookies'])) {
			foreach ($request['cookies'] as $name => $value) {
				$this->zend->setCookie($name, $value);
			}
		}

		$zend_response = $this->zend->request();

		// convert http response
		$response = array(
			'response' => array(
				'code' => $zend_response->getStatus(),
				'message' => $zend_response->getMessage(),
			),
			'headers' => $zend_response->getHeaders(),
			'body' => $zend_response->getBody(),
		);

		// convert all response headers to lowercase
		$header_keys = array_map('strtolower', array_keys($response['headers']));
		$header_values = array_values($response['headers']);
		$response['headers'] = array_combine($header_keys, $header_values);

		return $response;
	}

}

?>
