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
}

?>
