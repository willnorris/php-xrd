<?php

require_once('XRDS/XRD.php');

/**
 * XRDS-Simple Document.
 *
 * @package XRDS
 * @see http://xrds-simple.net/core/1.0/
 */
class XRDS {

	/** 
	 * XRDS XML Namespace 
	 */
	const XRDS_NS = 'xri://$xrds';


	/** 
	 * XRD XML Namespace 
	 */
	const XRD_NS = 'xri://$XRD*($v*2.0)';


	/** 
	 * OpenID XML Namespace 
	 */
	const OPENID_NS = 'http://openid.net/xmlns/1.0';


	/** 
	 * XRDS-Simple XML Namespace
	 */
	const SIMPLE_NS = 'http://xrds-simple.net/core/1.0';


	/** 
	 * XRDS Descriptors 
	 *
	 * @var array of XRDS_XRD objects
	 */
	public $xrd;


	/** Constructor */
	public function __construct() {
		$this->xrd = array();
	}


	/** 
	 * Get the URI for the Service that includes the specified type(s).  
	 * If the service contains multiple URIs, only one is returned (based 
	 * on priority).
	 *
	 * @param mixed $type a single type string, or an array of types
	 * @return object XRDS_URI for the service
	 */
	public function getServiceURI($type) {
		$service = $this->getService($type);
		if ($service) {
			return $service->uri[0];
		}
	}


	/** 
	 * Get the Service that includes the specified type(s).  
	 * If multiple service are found, only one is returned (based 
	 * on priority).
	 *
	 * @param mixed $type a single type string, or an array of types
	 * @return object XRDS_Service with specified type(s)
	 */
	public function getService($type) {
		if (!is_array($type)) {
			$type = array($type);
		}

		foreach ($this->xrd as $xrd) {
			foreach ($xrd->service as $service) {
				foreach ($type as $t) {
					if (!in_array($t, $service->type)) {
						continue 2;
					}
				}
				return $service;
			}
		}
	}


	/* Marshalling / Unmarshalling functions */

	/**
	 * Create an XRDS object from the specified file.
	 *
	 * @param string $file file to load
	 * @return XRDS object
	 * @see DOMDocument::load
	 */
	public static function load($file) {
		$dom = new DOMDocument();
		$dom->load($file);
		$xrds_elements = $dom->getElementsByTagName('XRDS');
		
		return self::from_dom($xrds_elements->item(0));
	}


	/**
	 * Create an XRDS object from the specified XML string.
	 *
	 * @param string $xml XML string to load
	 * @return XRDS object
	 * @see DOMDocument::loadXML
	 */
	public static function loadXML($xml) {
		$dom = new DOMDocument();
		$dom->loadXML($xml);
		$xrds_elements = $dom->getElementsByTagName('XRDS');
		
		return self::from_dom($xrds_elements->item(0));
	}


	/**
	 * Create an XRDS object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRDS object
	 */
	public static function from_dom(DOMElement $dom) {
		$xrds = new XRDS();

		$xrd_elements = $dom->getElementsByTagName('XRD');
		foreach ($xrd_elements as $element) {
			$xrd = XRDS_XRD::from_dom($element);
			$xrds->xrd[] = $xrd;
		}

		return $xrds;
	}


	/**
	 * Create a DOMDocument from this XRDS object.
	 *
	 * @return DOMDocument
	 */
	public function to_dom() {
		$dom = new DOMDocument();
		$xrds = $dom->createElementNS(XRDS::XRDS_NS, 'XRDS');
		$dom->appendChild($xrds);

		$xrds->setAttribute('xmlns:simple', XRDS::SIMPLE_NS);
		$xrds->setAttribute('xmlns:openid', XRDS::OPENID_NS);

		foreach ($this->xrd as $xrd) {
			$xrd_dom = $xrd->to_dom($dom);
			$xrds->appendChild($xrd_dom);
		}

		return $dom;
	}


	/**
	 * Get the marshalled XML for this XRDS object.
	 *
	 * @param boolean $format if true, XML output will be formatted
	 * @return string marshalled xml
	 */
	public function to_xml($format = false) {
		$dom = $this->to_dom();
		$dom->formatOutput = $format;
		return $dom->saveXML();
	}


	/**
	 * Compare items based on the priority rules of XRDS-Simple.  
	 * Items are sorted in increasing priority order, with null 
	 * values interpreted as infinity.
	 *
	 * @param object $a first object to compare
	 * @param object $b second object to compare
	 * @return int -1 if $a has a lower priority, +1 if $b has a lower priority, 0 if the two priorities are equal
	 * @see usort
	 */
	public function priority_sort($a, $b) {

		// deal with null values
		if ($a->priority === null) {
			if ($b->priority === null) {
				return 0;
			} else {
				return 1;
			}
		} else if ($b->priority === null) {
			return -1;
		}

		if ($a->priority == $b->priority) return 0;
		if ($a->priority > $b->priority) return 1;
		if ($a->priority < $b->priority) return -1;
	}
}

?>
