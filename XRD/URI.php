<?php

/**
 * XRDS URI.
 */
class XRDS_URI {

	/** 
	 * Priority. 
	 *
	 * @var int
	 */
	public $priority;


	/** 
	 * URI value. 
	 *
	 * @var string
	 */
	public $uri;


	/** 
	 * HTTP method. 
	 *
	 * @var string
	 */
	public $http_method;


	/**
	 * Constructor.
	 *
	 * @param string $uri URI value
	 * @param int $priority priority for this XRDS_URI
	 * @param string $http_method HTTP method for this XRDS_URI
	 */
	public function __construct($uri = null, $priority = 10, $http_method = null) {
		$this->uri = $uri;
		$this->priority = $priority;
		$this->http_method = $http_method;
	}


	/**
	 * When converted to string, simply return the URI value.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->uri;
	}


	/**
	 * Create an XRDS_URI object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRDS_URI object
	 */
	public static function from_dom(DOMElement $dom) {
		$uri = new XRDS_URI();

		$uri->priority = $dom->getAttribute('priority');
		$uri->http_method = $dom->getAttributeNS(XRDS::SIMPLE_NS, 'httpMethod');
		$uri->uri = $dom->nodeValue;

		return $uri;
	}


	/**
	 * Create a DOMElement from this XRDS_URI object.
	 *
	 * @param DOMDocument $dom document used to create elements.
	 * @return DOMElement
	 */
	public function to_dom($dom) {
		$uri = $dom->createElement('URI', $this->uri);

		if ($this->priority) {
			$uri->setAttribute('priority', $this->priority);
		}

		if ($this->http_method) {
			$uri->setAttribute('simple:httpMethod', $this->http_method);
		}

		return $uri;
	}
}

?>
