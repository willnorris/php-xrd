<?php

require_once 'Discovery/HTTP/Adaptor.php';

/**
 * The Discovery Context contains all of the information about the resolution of a specific URI.
 */
class Discovery_Context {

	/** 
	 * URI for which metadata is being discovered. 
	 *
	 * @var string
	 */
	public $uri;


	/**
	 * HTTP Client used for making requests
	 *
	 * @var Discovery_HTTP_Adaptor object
	 */
	protected $http;


	/** 
	 * HTTP responses.  Array keys are the signature of the request
	 * object *before* sending the request.  Signatures are calculated as:
	 *
	 *     md5( serialize( $request ) )
	 *
	 * @var array associative array of response objects
	 */
	protected $responses;


	/**
	 * Constructor.
	 */
	public function __construct($uri, Discovery_HTTP_Adaptor $http = null) {
		$this->uri = $uri;
		$this->responses = array();

		if ( $http == null ) $http = $this->httpAdaptor();
		$this->http = $http;
	}


	public function fetch($request) {
		$defaults = array(
			'method' => 'GET',
			'timeout' => 5,
			'redirection' => 5,
			'httpversion' => '1.0',
			'user-agent' => 'WP_Http',
			'blocking' => true,
			'headers' => array(),
			'cookies' => array(),
			'body' => null,
			'compress' => false,
			'decompress' => true,
			'sslverify' => true
		);

		$request = array_merge($defaults, $request);

		$signature = md5(serialize($request));

		if ( !array_key_exists($signature, $this->responses) ) {
			$responses[$signature] = $this->http->fetch($request);
		}

		return $responses[$signature];
	}


	/**
	 * Get appropriate HTTP adaptor based on what libraries are available.
	 *
	 * @return Discovery_HTTP_Adaptor
	 */
	protected function httpAdaptor() {
		// WP_Http
		if ( class_exists('WP_Http') ) {
			require_once 'Discovery/HTTP/WP_Http.php';
			return new Discovery_HTTP_WP();
		}

		// PEAR HTTP_Request2
		@include_once 'HTTP/Request2.php';
		if ( class_exists('HTTP_Request2') ) {
			require_once 'Discovery/HTTP/Pear.php';
			return new Discovery_HTTP_Pear();
		}

		// Zend_HTTP
		@include_once 'Zend/HTTP.php';
		if ( class_exists('Zend_HTTP') ) {
			require_once 'Discovery/HTTP/Zend.php';
			return new Discovery_HTTP_Zend();
		}
	}
}


?>
