<?php

require_once 'Discovery/Link.php';

/**
 * This library helps to discover descriptor documents for resources identified with URIs.  By 
 * default, three discovery methods are registered:
 *   - host-meta
 *   - Link HTTP response header
 *   - Link HTML element
 *
 * @see http://www.ietf.org/internet-drafts/draft-hammer-discovery-02.txt
 */
class Discovery {

	/**
	 * Discovery_Method implementations to use for discovery.
	 */
	public $discovery_methods;


	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->discovery_methods = array();

		$this->register_discovery_method('Discovery_Host_Meta');
		//$this->register_discovery_method('Discovery_Link_Header');
		//$this->register_discovery_method('Discovery_Link_HTML');
	}


	/**
	 * Register a discovery method to be used.
	 *
	 * @param string class name of Discovery_Method implementation
	 */
	public function register_discovery_method($class) {
		$this->discovery_methods[] = $class;
	}


	/**
	 * Discover available descriptor documents for the specified identifier.
	 *
	 * @param string $uri URI to perform discovery on
	 * @return array array of Discovery_Link objects
	 */
	public function discover($uri) {

		foreach($this->discovery_methods as $class) {
			$links = call_user_func(array($class, 'discover'), $uri);
			if ($links) break;
		}

		return $links;
	}


	/**
	 * Helper function to fetch the contents of a URL.
	 *
	 * @param string $url URL to fetch
	 * @param array $headers additional HTTP headers to include in the request
	 * @return mixed contents of URL fetch, or FALSE if fetching failed
	 */
	public static function fetch($url, $headers=null) {

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'discovery/1.0 (php)');

		if (!empty($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$content = curl_exec($ch);
		curl_close($ch);

		return $content;
	}

}



?>
