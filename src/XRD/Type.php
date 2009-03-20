<?php

/**
 * XRD Type.
 */
class XRD_Type {

	/** 
	 * Required. 
	 *
	 * @var boolean
	 */
	public $required;


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
	 * @param boolean $required whether the type is required
	 */
	public function __construct($uri = null, $required = false) {
		$this->uri = $uri;
		$this->required = $required;
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
	 * Create an XRD_Type object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRD_Type object
	 */
	public static function from_dom(DOMElement $dom) {
		$type = new self();

		if ($dom->getAttribute('required') == 'true') {
			$type->required = true;
		} else {
			$type->required = false;
		}

		$type->uri = $dom->nodeValue;

		return $type;
	}


	/**
	 * Create a DOMElement from this XRD_Type object.
	 *
	 * @param DOMDocument $dom document used to create elements.
	 * @return DOMElement
	 */
	public function to_dom($dom) {
		$type_dom = $dom->createElement('Type', $this->uri);

		if ($this->required) {
			$type_dom->setAttribute('required', $this->required);
		}

		return $type_dom;
	}

}

?>
