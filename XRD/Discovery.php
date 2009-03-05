<?php

/**
 * Present flow
 *
 * 1. Content Negotiation
 *    - send request with "accept: application/xrds+xml"
 *    - if response is XRDS, finish
 * 2. HTTP Response Header
 *    - look for X-XRDS-Location response header
 *    - if header exists, retrieve and finish
 * 3. HTML Meta Location
 *    - look for X-XRDS-Location in HTML <head>
 *    - if link exists, retrieve and finish
 * 4. (Extension) OpenID 2 Link Location
 *    - look for openid2.provider in HTML <head>
 *    - if link exists, build XRDS and finish
 * 5. (Extension) OpenID 1 Link Location
 *    - look for openid.server in HTML <head>
 *    - if link exists, build XRDS and finish
 *
 *
 * Potential future flow
 *
 * 1. DNS
 *    - look for XRD DNS TXT record
 *    - if record exists, retreive and finish
 * 2. Site Meta
 *    - attempt to retrieve /site-meta
 *    - if successful, transform identifier accordingly
 *    - retrieve XRD and finish
 * 3. Link Header
 *    - HEAD resource and look for Link response header
 *    - if header exists, retrieve and finish
 *
 */
class XRDS_Discovery {

	/**
	 * XRDS_Discovery_Method implementations to use for discovery.
	 */
	public $discovery_methods;


	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->discovery_methods = array();

		$this->register_discovery_method('XRDS_Discovery_Content_Negotiation');
		$this->register_discovery_method('XRDS_Discovery_Location_Header');
		$this->register_discovery_method('XRDS_Discovery_HTML_Meta');
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

		$context = new XRDS_Discovery_Context($uri);

		foreach($this->discovery_methods as $class) {
			$xrds = call_user_func(array($class, 'init'), $context);
			$context->init();
			$xrds = call_user_func(array($class, 'discover'), $context);
			if ($xrds) break;
		}

		//print_r($context);
		return $xrds;
	}


	public static function fetch($url, $headers=null) {

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		if (!empty($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$content = curl_exec($ch);
		curl_close($ch);

		return $content;
	}

	public static function fetch_xrds_url($url, $headers=null) {
		$content = self::fetch($url, $headers);
		$xrds = XRDS::loadXML($content);
		return $xrds;
	}

}


/**
 * The XRDS Discovery Context contains all of the information about the resolution of a specific URI.
 */
class XRDS_Discovery_Context {

	/** URI for which metadata is being discovered. */
	public $uri;

	/** Content of the resolved URI. */
	public $content;

	/** HTTP headers returned when resolving the URI. */
	public $response_headers;

	/** Has this Discovery Context been initialized. */
	private $initialized;


	/**
	 * Constructor.
	 */
	public function __construct($uri) {
		$this->uri = $uri;
		$this->initialized = false;
	}


	/**
	 * Initialize the Discovery Context.
	 *
	 * @param array $headers HTTP request headers to use when resolving the URI for this context.
	 * @param boolean $force should we force re-initialization if this context has already been initialized
	 */
	public function init($headers = array(), $force = false) {
		if ($this->initialized && !$force) return;

		// TODO should we be using a fetcher object?
		// TODO only http URLs need be initialized in this manner
		$ch = curl_init($this->uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'xrds-simple/php');
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'header'));

		if (!empty($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$this->content = curl_exec($ch);
		curl_close($ch);

		$this->initialized = true;
	}


	/**
	 * Call-back method for curl to record the HTTP response headers.
	 */
	public function header($ch, $header) {
		if (strpos($header, ':') !== false) {
			list($name, $value) = split(':', $header, 2);
			$name = trim($name);
			$value = trim($value);

			if ($name && $value) {
				$this->response_headers[strtolower($name)] = $value;
			}
		}

		return strlen($header);
	}
}


/**
 * A XRDS Discovery Methods implements one way of discovering the metadata for a URI
 */
abstract class XRDS_Discovery_Method {

	public function init(XRDS_Discovery_Context &$context) { }

	abstract public function discover(XRDS_Discovery_Context &$context);
}


/**
 * XRDS Discovery Method that uses HTTP content negotiation.
 */
class XRDS_Discovery_Content_Negotiation extends XRDS_Discovery_Method {

	const CONTENT_TYPE = 'application/xrds+xml';

	public function init(XRDS_Discovery_Context &$context) { 
		$context->init(array('Accept: ' . self::CONTENT_TYPE), true);
	}

	public function discover(XRDS_Discovery_Context &$context) {
		if (@strtolower($context->response_headers['content-type']) == self::CONTENT_TYPE) {
			$xrds = XRDS::loadXML($context->content);
			return $xrds;
		}
	}

}


/**
 * XRDS Discovery Method that looks for an HTTP response header advertising the location of the XRDS document.
 */
class XRDS_Discovery_Location_Header extends XRDS_Discovery_Method {

	public function discover(XRDS_Discovery_Context &$context) {
		if (array_key_exists('x-xrds-location', $context->response_headers)) {
			return XRDS_Discovery::fetch_xrds_url($context->response_headers['x-xrds-location']);
		}
	}

}


/**
 * XRDS Discovery Method that looks for an HTML Meta element advertising the location of the XRDS document.
 */
class XRDS_Discovery_HTML_Meta extends XRDS_Discovery_Method {

	public function discover(XRDS_Discovery_Context &$context) {
		preg_match_all('/<meta [^>]*>/', $context->content, $matches);
		foreach ($matches[0] as $meta) {
			if (preg_match('/ http-equiv=("|\')?x-xrds-location\\1/i', $meta)) {
				preg_match('/ content=("|\')?([^\\1]*)\\1/', $meta, $matches);
				return XRDS_Discovery::fetch_xrds_url($matches[2]);
			}
		}
	}

}


