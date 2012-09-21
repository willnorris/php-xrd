<?php

require_once('XRD/Property.php');
require_once('XRD/Link.php');

/**
 * XRD Descriptor.
 *
 * @package XRD
 */
class XRD {

	/** 
	 * XRD XML Namespace 
	 */
	const XML_NS = 'http://docs.oasis-open.org/ns/xri/xrd-1.0';

	/**
	 * XRD Content Type
	 */
	const CONTENT_TYPE = 'application/xrd+xml';

	/** 
	 * Expiration date for this descriptor. 
	 *
	 * @var string
	 */
	public $expires;


	/** 
	 * Subject
	 *
	 * @var string
	 */
	public $subject;


	/** 
	 * Aliases
	 *
	 * @var array of strings
	 */
	public $alias;


	/** 
	 * Properties. 
	 *
	 * @var array of XRD_Property objects
	 */
	public $property;


	/** 
	 * Links. 
	 *
	 * @var array of XRD_Link objects
	 */
	public $link;

	/**
	 * Constructor.
	 *
	 * @param mixed $type XRD_Type object or array of XRD_Type objects
	 * @param mixed $link XRD_Link object or array of XRD_Link objects
	 * @param mixed $alias Alias string or array of Alias strings
	 * @param string $subject XRD subject
	 * @param string $expires expiration date
	 */
	public function __construct($property=null, $link=null, $alias=null, $subject=null, $expires=null) {
		$this->property = (array) $property;
		$this->link = (array) $link;
		$this->alias = (array) $alias;
		$this->expires = $expires;
		$this->subject = $subject;
	}


	/**
	 * Create an XRD object from a DOMElement
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRD object
	 */
	public static function from_dom(DOMElement $dom) {
		$xrd = new XRD();

		foreach ($dom->childNodes as $node) {
			if (!isset($node->tagName)) continue;

			switch($node->tagName) {
				case 'Expires':
					$xrd->expires = $node->nodeValue;
					break;

				case 'Subject':
					$xrd->subject = $node->nodeValue;
					break;

				case 'Alias':
					$xrd->alias[] = $node->nodeValue;
					break;

				case 'Property':
					$property = XRD_Property::from_dom($node);
					$xrd->property[] = $property;
					break;

				case 'Link':
					$link = XRD_Link::from_dom($node);
					$xrd->link[] = $link;
					break;
			}
		}

		return $xrd;
	}


	/**
	 * Create a DOMElement from this XRD object
	 *
	 * @param DOMDocument $dom document used to create elements.
	 * @return DOMDocument
	 */
	public function to_dom($dom = null) {
		if ($dom == null) {
			$dom = new DOMDocument();
		}

		$xrd_dom = $dom->createElementNS(XRD::XML_NS, 'XRD');
		$dom->appendChild($xrd_dom);

		if ($this->expires) {
			$expires_dom = $dom->createElement('Expires', $this->expires);
			$xrd_dom->appendChild($expires_dom);
		}

		if ($this->subject) {
			$subject_dom = $dom->createElement('Subject', $this->subject);
			$xrd_dom->appendChild($subject_dom);
		}

		foreach ($this->alias as $alias) {
			$alias_dom = $dom->createElement('Alias', $alias);
			$xrd_dom->appendChild($alias_dom);
		}

		foreach ($this->property as $property) {
			$property_dom = $property->to_dom($dom);
			$xrd_dom->appendChild($property_dom);
		}

		foreach ($this->link as $link) {
			$link_dom = $link->to_dom($dom);
			$xrd_dom->appendChild($link_dom);
		}

		return $dom;
	}


	/**
	 * Create an XRD object from the specified file.
	 *
	 * @param string $file file to load
	 * @return XRD object
	 * @see DOMDocument::load
	 */
	public static function load($file) {
		$dom = new DOMDocument();
		$dom->load($file);
		$xrd_elements = $dom->getElementsByTagName('XRD');
		
		return self::from_dom($xrd_elements->item(0));
	}


	/**
	 * Create an XRD object from the specified XML string.
	 *
	 * @param string $xml XML string to load
	 * @return XRD object
	 * @see DOMDocument::loadXML
	 */
	public static function loadXML($xml) {
		$dom = new DOMDocument();
		$dom->loadXML($xml);
		$xrd_elements = $dom->getElementsByTagName('XRD');
		
		return self::from_dom($xrd_elements->item(0));
	}


	/**
	 * Get the marshalled XML for this XRD object.
	 *
	 * @param boolean $format if true, XML output will be formatted
	 * @return string marshalled xml
	 */
	public function to_xml($format = false) {
		$dom = $this->to_dom();
		$dom->formatOutput = $format;
		return $dom->saveXML();
	}
}
?>