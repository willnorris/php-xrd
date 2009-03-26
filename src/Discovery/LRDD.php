<?php

require_once 'Discovery/Context.php';
require_once 'Discovery/Util.php';
require_once 'Discovery/LRDD/Link.php';
require_once 'Discovery/LRDD/Method/Host_Meta.php';
require_once 'Discovery/LRDD/Method/Link_Header.php';
require_once 'Discovery/LRDD/Method/Link_HTML.php';

/**
 * This library helps to discover descriptor documents for resources identified with URIs.  By 
 * default, three discovery methods are registered:
 *   - host-meta
 *   - Link HTTP response header
 *   - Link HTML element
 *
 * @see http://www.ietf.org/internet-drafts/draft-hammer-discovery-02.txt
 * @package Discovery
 */
class Discovery_LRDD {

	/**
	 * Discovery_LRDD_Method implementations to use for discovery.
	 */
	public $discovery_methods;

	/**
	 *
	 * @var Discovery_HTTP_Adapter
	 */
	public $http;

	/**
	 * Constructor.
	 */
	public function __construct(Discovery_HTTP_Adapter $http = null) {
		if ( $http == null ) $http = Discovery_Util::httpAdapter();
		$this->http = $http;

		$this->discovery_methods = array();
		$this->register_discovery_method('Discovery_LRDD_Method_Link_HTML');
		$this->register_discovery_method('Discovery_LRDD_Method_Link_Header');
		$this->register_discovery_method('Discovery_LRDD_Method_Host_Meta');
	}


	/**
	 * Register a discovery method to be used.
	 *
	 * @param string class name of Discovery_LRDD_Method implementation
	 */
	public function register_discovery_method($class) {
		$this->discovery_methods[] = $class;
	}


	/**
	 * Discover available descriptor documents for the specified identifier.
	 *
	 * @param string|Discovery_Context $uri discovery context used for discovery,
	 *     or URI use to create a new discovery context
	 * @return array array of Discovery_LRDD_Link objects
	 */
	public function discover($uri) {

		// allow this method to be called statically
		if (!isset($this)) {
			$lrdd = new self();
			return $lrdd->discover($uri);
		}

		$context = new Discovery_Context($uri, $this->http);

		foreach ($this->discovery_methods as $class) {
			$links = call_user_func(array($class, 'discover'), $context);
			if ($links) break;
		}

		return $links;
	}

}

?>
