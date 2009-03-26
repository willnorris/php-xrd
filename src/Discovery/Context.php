<?php

require_once 'Discovery/HTTP/Adapter.php';

/**
 * The Discovery Context contains all of the information about the resolution of a specific URI.
 *
 * @package Discovery
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
	 * @var Discovery_HTTP_Adapter object
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
	public function __construct($uri, Discovery_HTTP_Adapter $http) {
		$this->uri = $uri;
		$this->http = $http;
		$this->responses = array();
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

}

?>
