<?php

/**
 * XRD Property.
 *
 * @package XRD
 */
class XRD_Property {

	/** 
	 * Type value. 
	 *
	 * @var string
	 */
	public $type;

	/** 
	 * Value. 
	 *
	 * @var boolean
	 */
	public $value;

	/**
	 * Constructor.
	 *
	 * @param string $type
	 * @param string $value
	 */
	public function __construct($type = null, $value = false) {
		$this->type = $type;
		$this->value = $value;
	}

	/**
	 * When converted to string, simply return the value.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->value;
	}


	/**
	 * Create an XRD_Property object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRD_Property object
	 */
	public static function from_dom(DOMElement $dom) {
		$property = new self();

		$property->type = $dom->getAttribute('type');
		$property->value = $dom->nodeValue;

		return $property;
	}


	/**
	 * Create a DOMElement from this XRD_Property object.
	 *
	 * @param DOMDocument $dom document used to create elements.
	 * @return DOMElement
	 */
	public function to_dom($dom) {
		$type_dom = $dom->createElement('Property', $this->value);
		$type_dom->setAttribute('type', $this->type);

		return $type_dom;
	}

}

?>