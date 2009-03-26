<?php

require_once 'Discovery/HTTP/Adaptor.php';
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Response.php';

class Discovery_HTTP_Zend implements Discovery_HTTP_Adaptor {

	public function fetch($request) {
		$zend = new Zend_Http_Client();

		// map configuration options
		$zend_config = array(
			'maxredirects' => $request['redirection'],
			'useragent' => $request['user-agent'],
			'timeout' => $request['timeout'],
			'httpversion' => $request['httpversion'],
			'keepalive' => false,
			'storeresponse' => true,
		);
		$zend->setConfig( $zend_config );

		// setup request
		$zend->setUri( $request['uri'] );
		$zend->setMethod( strtoupper($request['method']) );
		$zend->setHeaders( $request['headers'] );
		$zend->setRawData( $request['body'] );
		if (!empty($request['cookies'])) {
			foreach ($request['cookies'] as $name => $value) {
				$zend->setCookie($name, $value);
			}
		}

		$zend_response = $zend->request();

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
