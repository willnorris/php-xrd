<?php

require_once 'XRD/URI.php';

/**
 * XRD TemplateURI.
 *
 * @package XRD
 */
class XRD_TemplateURI extends XRD_URI {


	public function nodeName() {
		return 'TemplateURI';
	}


	public function applyTemplate($resource) {
		require_once 'Discovery/LRDD/LinkPattern.php';
		return Discovery_LRDD_LinkPattern::applyPattern($resource, $this->uri);
	}

	/**
	 * Create an XRD_TemplateURI object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRD_TemplateURI object
	 */
	public static function from_dom(DOMElement $dom) {
		$uri = new self();

		$uri->priority = $dom->getAttribute('priority');
		$uri->uri = $dom->nodeValue;

		return $uri;
	}

}

?>
