<?php

require_once 'XRD/URI.php';

/**
 * XRD SourceAlias.
 *
 * @package XRD
 */
class XRD_SourceAlias extends XRD_URI {

	public function nodeName() {
		return 'SourceAlias';
	}

	/**
	 * Create an XRD_SourceAlias object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRD_SourceAlias object
	 */
	public static function from_dom(DOMElement $dom) {
		$source_alias = new self();

		$source_alias->priority = $dom->getAttribute('priority');
		$source_alias->uri = $dom->nodeValue;

		return $source_alias;
	}
}

?>
