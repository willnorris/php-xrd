<?php

require_once('XRDS/URI.php');
require_once('XRDS/LocalID.php');
require_once('XRDS/Service.php');

/**
 * XRDS Descriptor.
 */
class XRDS_XRD {

	/** 
	 * ID of XRDS Descriptor. 
	 *
	 * @var string
	 */
	public $id;


	/** 
	 * XRDS Version. 
	 *
	 * @var string
	 */
	public $version;


	/** 
	 * Types. 
	 *
	 * @var array of strings
	 */
	public $type;


	/** 
	 * Expiration date for this descriptor. 
	 *
	 * @var string
	 */
	public $expires;


	/** 
	 * Services. 
	 *
	 * @var array of XRDS_Service objects
	 */
	public $service;


	/**
	 * Constructor.
	 *
	 * @param string $id ID
	 * @param mixed $type Type string or array of Type strings
	 * @param string $expires expiration date
	 */
	public function __construct($id=null, $type=null, $expires=null) {
		if (!is_array($type)) $type = array_filter(array($type));
		$this->type = $type;

		$this->id = $id;
		$this->expires = $expires;
		$this->service = array();
	}


	/**
	 * Create an XRDS_XRD object from a DOMElement
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRDS_XRD object
	 */
	public static function from_dom(DOMElement $dom) {
		$xrd = new XRDS_XRD();

		$xrd->version = $dom->getAttribute('version');
		$xrd->id = $dom->getAttribute('xml:id');

		$services = array();

		foreach ($dom->childNodes as $node) {
			if (!isset($node->tagName)) continue;

			switch($node->tagName) {
				case 'Type':
					$xrd->type[] = $node->nodeValue;
					break;

				case 'Expires':
					$xrd->expires = $node->nodeValue;
					break;

				case 'Service':
					$service = XRDS_Service::from_dom($node);
					$xrd->service[] = $service;
					break;
			}
		}

		usort($xrd->service, array('XRDS', 'priority_sort'));

		return $xrd;
	}


	/**
	 * Create a DOMElement from this XRDS_XRD object
	 *
	 * @param DOMDocument $dom document used to create elements.
	 * @return DOMDocument
	 */
	public function to_dom($dom) {
		$xrd = $dom->createElementNS(XRDS::XRD_NS, 'XRD');

		if ($this->id) {
			$xrd->setAttribute('xml:id', $this->id);
		}

		if ($this->version) {
			$xrd->setAttribute('version', $this->version);
		}

		if ($this->expires) {
			$expires_dom = $xrd->createElement('Expires', $expires);
			$xrd->appendChild($expires_dom);
		}

		foreach ($this->type as $type) {
			$type_dom = $dom->createElement('Type', $type);
			$xrd->appendChild($type_dom);
		}

		foreach ($this->service as $service) {
			$service_dom = $service->to_dom($dom);
			$xrd->appendChild($service_dom);
		}

		return $xrd;
	}

}

?>
