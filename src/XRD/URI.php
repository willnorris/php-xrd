<?php

/**
 * XRD URI.
 *
 * @package XRD
 */
class XRD_URI {

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
	 * Constructor.
	 *
	 * @param string $uri URI value
	 * @param int $priority priority for this XRD_URI
	 */
	public function __construct($uri = null, $priority = 10) {
		$this->uri = $uri;
		$this->priority = $priority;
	}

	protected function nodeName() {
		return 'URI';
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
	 * Create an XRD_URI object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRD_URI object
	 */
	public static function from_dom(DOMElement $dom) {
		$uri = new self();

		$uri->priority = $dom->getAttribute('priority');
		$uri->uri = $dom->nodeValue;

		return $uri;
	}


	/**
	 * Create a DOMElement from this XRD_URI object.
	 *
	 * @param DOMDocument $dom document used to create elements.
	 * @return DOMElement
	 */
	public function to_dom($dom) {
		$nodeName = $this->nodeName();
		$uri_dom = $dom->createElement($nodeName, $this->uri);

		if ($this->priority) {
			$uri_dom->setAttribute('priority', $this->priority);
		}

		return $uri_dom;
	}

}

?>
