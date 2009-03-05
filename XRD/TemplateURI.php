<?php

require_once 'XRD/URI.php';

/**
 * XRD TemplateURI.
 */
class XRD_TemplateURI extends XRD_URI {

	public function nodeName() {
		return 'TemplateURI';
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
