<?php

/**
 * XRDS URI.
 */
class XRDS_LocalID {

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
	 * @param int $priority priority for this XRDS_LocalID
	 */
	public function __construct($uri = null, $priority = 10) {
		$this->uri = $uri;
		$this->priority = $priority;
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
	 * Create an XRDS_LocalID object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRDS_LocalID object
	 */
	public static function from_dom(DOMElement $dom) {
		$local_id = new XRDS_LocalID();

		$local_id->priority = $dom->getAttribute('priority');
		$local_id->uri = $dom->nodeValue;

		return $local_id;
	}


	/**
	 * Create a DOMElement from this XRDS_LocalID object.
	 *
	 * @param DOMDocument $dom document used to create elements.
	 * @return DOMElement
	 */
	public function to_dom($dom) {
		$local_id = $dom->createElement('LocalID', $this->uri);

		if ($this->priority) {
			$local_id->setAttribute('priority', $this->priority);
		}

		return $local_id;
	}
}

?>
