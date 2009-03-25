<?php

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
	 * HTTP responses.  Array keys are the signature of the request
	 * object *before* sending the request.  Signatures are calculated as:
	 *
	 *     md5( serialize( $request ) )
	 *
	 * @var array associative array of response objects
	 */
	public $responses;


	/**
	 * Constructor.
	 */
	public function __construct($uri) {
		$this->uri = $uri;
		$this->responses = array();
	}

	public function fetch($request) {
		$signature = md5(serialize($request));

		if ( !array_key_exists($signature, $this->responses) ) {
			$responses[$signature] = $request->send();
		}

		return $responses[$signature];
	}

}


?>
