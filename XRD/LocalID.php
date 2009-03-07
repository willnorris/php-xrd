<?php

require_once 'XRD/URI.php';

/**
 * XRD LocalID.
 */
class XRD_LocalID extends XRD_URI {

	public function nodeName() {
		return 'LocalID';
	}

	/**
	 * Create an XRD_LocalID object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRD_LocalID object
	 */
	public static function from_dom(DOMElement $dom) {
		$local_id = new self();

		$local_id->priority = $dom->getAttribute('priority');
		$local_id->uri = $dom->nodeValue;

		return $local_id;
	}
}

?>
