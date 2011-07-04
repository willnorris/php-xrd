<?php

/**
 * XRD Title.
 *
 * @package XRD
 */
class XRD_Title {

	/** 
	 * Value. 
	 *
	 * @var int
	 */
	public $value;


	/** 
	 * Language. 
	 *
	 * @var string
	 */
	public $lang;


	/**
	 * Constructor.
	 *
	 * @param string $value
	 * @param string $lang language of title
	 */
	public function __construct($value = null, $lang = null) {
		$this->value = $value;
		$this->lang = $lang;
	}

	protected function nodeName() {
		return 'Title';
	}

	/**
	 * When converted to string, simply return the URI value.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->value;
	}


	/**
	 * Create an XRD_Title object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRD_Title object
	 */
	public static function from_dom(DOMElement $dom) {
		$title = new self();

		$title->lang = $dom->getAttribute('xml:lang');
		$title->value = $dom->nodeValue;

		return $title;
	}


	/**
	 * Create a DOMElement from this XRD_Title object.
	 *
	 * @param DOMDocument $dom document used to create elements.
	 * @return DOMElement
	 */
	public function to_dom($dom) {
		$nodeName = $this->nodeName();
		$title_dom = $dom->createElement($nodeName, $this->value);

		if ($this->priority) {
			$title_dom->setAttribute('xml:lang', $this->lang);
		}

		return $title_dom;
	}

}

?>