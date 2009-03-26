<?php

require_once 'Discovery/HTTP/Adaptor.php';
require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Response.php';

class Discovery_HTTP_Pear implements Discovery_HTTP_Adaptor {

	public function fetch($request) {
		$pear = new HTTP_Request2();

		// map configuration options
		$pear_config = array(
			'connect_timeout' => $request['timeout'],
			'protocol_version' => $request['httpversion'],
			//'proxy_host' - Proxy server host (string)
			//'proxy_port' - Proxy server port (integer)
			//'proxy_user' - Proxy auth username (string)
			//'proxy_password' - Proxy auth password (string)
			//'proxy_auth_scheme' - Proxy auth scheme, one of HTTP_Request2::AUTH_* constants (string)
			'ssl_verify_peer' => $request['sslverify'],
			'ssl_verify_host' => $request['sslverify'],
			//'ssl_cafile' - Cerificate Authority file to verify the peer with (use with 'ssl_verify_peer') (string)
			//'ssl_capath' - Directory holding multiple Certificate Authority files (string)
			//'ssl_local_cert' - Name of a file containing local cerificate (string)
			//'ssl_passphrase' - Passphrase with which local certificate was encoded (string)
		);
		$pear->setConfig($pear_config);

		// setup request
		$pear->setUrl( $request['uri'] );
		$pear->setMethod( $request['method'] );
		$pear->setHeader( $request['headers'] );
		$pear->setBody( $request['body'] );
		if (!empty($request['cookies'])) {
			foreach ($request['cookies'] as $name => $value) {
				$pear->addCookie($name, $value);
			}
		}

		$pear_response = $pear->send();

		// convert http response
		$response = array(
			'response' => array(
				'code' => $pear_response->getStatus(),
				'message' => $pear_response->getReasonPhrase(),
			),
			'headers' => $pear_response->getHeader(),
			'cookies' => $pear_response->getCookies(),
			'body' => $pear_response->getBody(),
		);

		return $response;
	}

}

?>
