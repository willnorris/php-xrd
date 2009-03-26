<?php


class Discovery_Yadis {

	/**
	 * Discovery_Yadis_Method implementations to use for discovery.
	 */
	public $discovery_methods;


	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->discovery_methods = array();

		$this->register_discovery_method('Discovery_Yadis_Content_Negotiation');
		$this->register_discovery_method('Discovery_Yadis_Location_Header');
		$this->register_discovery_method('Discovery_Yadis_HTML_Meta');
	}


	/**
	 * Register a discovery method to be used.
	 *
	 * @param string class name of XRDS_Discovery_Method sub-class.
	 */
	public function register_discovery_method($class) {
		$this->discovery_methods[] = $class;
	}


	/**
	 * Discover a XRDS document for the specified identifier.
	 *
	 * @param string $uri URI to perform discovery on
	 * @return XRDS object
	 */
	public function discover($uri) {

		// allow this method to be called statically
		if (!isset($this)) {
			$yadis = new self();
			return $yadis->discover($uri);
		}

		$context = new Discovery_Context($uri);

		foreach($this->discovery_methods as $class) {
			$xrds = call_user_func(array($class, 'discover'), $context);
			if ($xrds) break;
		}

		return $xrds;
	}


	public static function fetch_xrds_url($uri, $context) {
		$request = array( 'uri' => $uri );
		$response = $context->fetch($request);
		$status_digit = floor( $response['response']['code'] / 100 );

		if ($status_digit == 2 || $status_digit == 3) {
			return XRDS::loadXML($response['body']);
		}
	}

}

?>
