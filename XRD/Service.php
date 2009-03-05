<?php

/**
 * XRDS Service.
 */
class XRDS_Service {

	/** 
	 * Priority. 
	 *
	 * @var int
	 */
	public $priority;


	/** 
	 * Types.
	 *
	 * @var array of strings
	 */
	public $type;


	/** 
	 * Media types.
	 *
	 * @var array of strings
	 */
	public $media_type;


	/** 
	 * URIs.
	 *
	 * @var array of XRDS_URI objects
	 */
	public $uri;


	/** 
	 * Local IDs.
	 *
	 * @var array of XRDS_LocalID objects
	 */
	public $local_id;


	/** 
	 * Required Support.
	 *
	 * @var array of strings
	 */
	public $must_support;


	/**
	 * Constructor.
	 *
	 * @param mixed $type Type string or array of Type strings
	 * @param mixed $media_type Media Type string or array of Media Type strings
	 * @param mixed $uri XRDS_URI object or array of XRDS_URI objects
	 * @param mixed $type XRDS_LocalID object or array of XRDS_LocalID objects
	 * @param mixed $type Must Support string or array of Must Support strings
	 * @param int $priority Priority
	 */
	public function __construct($type=null, $media_type=null, $uri=null, $local_id=null, $must_support=null, $priority=10) {
		if (!is_array($type)) $type = array_filter(array($type));
		$this->type = $type;

		if (!is_array($media_type)) $media_type = array_filter(array($media_type));
		$this->media_type = $media_type;

		if (!is_array($uri)) $uri = array_filter(array($uri));
		$this->uri = $uri;

		if (!is_array($local_id)) $local_id = array_filter(array($local_id));
		$this->local_id = $local_id;

		if (!is_array($must_support)) $must_support = array_filter(array($must_support));
		$this->must_support = $must_support;

		$this->priority = $priority;
	}


	/**
	 * Create an XRDS_Service object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRDS_Service object
	 */
	public static function from_dom(DOMElement $dom) {
		$service = new XRDS_Service();

		$service->priority = $dom->getAttribute('priority');

		$elements = $dom->getElementsByTagName('Type');
		foreach ($elements as $e) {
			$service->type[] = $e->nodeValue;
		}

		$elements = $dom->getElementsByTagName('MediaType');
		foreach ($elements as $e) {
			$service->media_type[] = $e->nodeValue;
		}

		$elements = $dom->getElementsByTagName('URI');
		foreach ($elements as $e) {
			$uri = XRDS_URI::from_dom($e);
			$service->uri[] = $uri;
		}
		usort($service->uri, array('XRDS', 'priority_sort'));

		$elements = $dom->getElementsByTagName('LocalID');
		foreach ($elements as $e) {
			$local_id = XRDS_LocalID::from_dom($e);
			$service->local_id[] = $local_id;
		}
		usort($service->local_id, array('XRDS', 'priority_sort'));

		$elements = $dom->getElementsByTagNameNS(XRDS::SIMPLE_NS, 'MustSupport');
		foreach ($elements as $e) {
			$service->must_support[] = $e->nodeValue;
		}

		return $service;
	}


	/**
	 * Create a DOMElement from this XRDS_Service object.
	 *
	 * @param DOMDocument $dom document used to create elements.
	 * @return DOMElement
	 */
	public function to_dom($dom) {
		$service = $dom->createElement('Service');

		if ($this->priority) {
			$service->setAttribute('priority', $this->priority);
		}

		foreach ($this->type as $type) {
			$type_dom = $dom->createElement('Type', $type);
			$service->appendChild($type_dom);
		}

		foreach ($this->media_type as $type) {
			$type_dom = $dom->createElement('MediaType', $type);
			$service->appendChild($type_dom);
		}

		foreach ($this->uri as $uri) {
			$uri_dom = $uri->to_dom($dom);
			$service->appendChild($uri_dom);
		}

		foreach ($this->local_id as $local_id) {
			$id_dom = $local_id->to_dom($dom);
			$service->appendChild($id_dom);
		}

		foreach ($this->must_support as $support) {
			$support_dom = $dom->createElement('simple:MustSupport', $support);
			$service->appendChild($support_dom);
		}

		return $service;
	}
}

?>
