<?php

require_once 'XRD/URI.php';
require_once 'XRD/TemplateURI.php';
require_once 'XRD/LocalID.php';

/**
 * XRD Link.
 */
class XRD_Link {

	/** 
	 * Priority. 
	 *
	 * @var int
	 */
	public $priority;


	/** 
	 * Rels.
	 *
	 * @var array of strings
	 */
	public $rel;


	/** 
	 * Media types.
	 *
	 * @var array of strings
	 */
	public $media_type;


	/** 
	 * URIs.
	 *
	 * @var array of XRD_URI objects
	 */
	public $uri;


	/** 
	 * Template URIs.
	 *
	 * @var array of XRD_TemplateURI objects
	 */
	public $template_uri;


	/** 
	 * Local IDs.
	 *
	 * @var array of XRD_LocalID objects
	 */
	public $local_id;


	/**
	 * Constructor.
	 *
	 * @param mixed $rel Rel string or array of Rel strings
	 * @param mixed $media_type Media Type string or array of Media Type strings
	 * @param mixed $uri XRD_URI object or array of XRD_URI objects
	 * @param mixed $type XRD_TemplateURI object or array of XRD_TemplateURI objects
	 * @param mixed $type XRD_LocalID object or array of XRD_LocalID objects
	 * @param int $priority Priority
	 */
	public function __construct($rel=null, $media_type=null, $uri=null, $template_uri=null, $local_id=null, $priority=10) {
		$this->rel = (array) $rel;
		$this->media_type = (array) $media_type;
		$this->uri = (array) $uri;
		$this->template_uri = (array) $template_uri;
		$this->local_id = (array) $local_id;
		$this->priority = $priority;
	}


	/**
	 * Create an XRD_Link object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRD_Link object
	 */
	public static function from_dom(DOMElement $dom) {
		$link = new self();

		$link->priority = $dom->getAttribute('priority');

		foreach ($dom->childNodes as $node) {
			if (!isset($node->tagName)) continue;

			switch($node->tagName) {
				case 'Rel':
					$link->rel[] = $node->nodeValue;
					break;

				case 'MediaType':
					$link->media_type[] = $node->nodeValue;
					break;

				case 'URI':
					$uri = XRD_URI::from_dom($node);
					$link->uri[] = $uri;
					break;

				case 'TemplateURI':
					$template_uri = XRD_TemplateURI::from_dom($node);
					$link->template_uri[] = $template_uri;
					break;

				case 'LocalID':
					$local_id = XRD_LocalID::from_dom($node);
					$link->local_id[] = $local_id;
					break;
			}
		}

		usort($link->uri, array('XRD', 'priority_sort'));
		usort($link->template_uri, array('XRD', 'priority_sort'));
		usort($link->local_id, array('XRD', 'priority_sort'));

		return $link;
	}


	/**
	 * Create a DOMElement from this XRD_Link object.
	 *
	 * @param DOMDocument $dom document used to create elements.
	 * @return DOMElement
	 */
	public function to_dom($dom) {
		$link_dom = $dom->createElement('Link');

		if ($this->priority) {
			$link_dom->setAttribute('priority', $this->priority);
		}

		foreach ($this->rel as $rel) {
			$rel_dom = $dom->createElement('Rel', $rel);
			$link_dom->appendChild($rel_dom);
		}

		foreach ($this->media_type as $type) {
			$type_dom = $dom->createElement('MediaType', $type);
			$link_dom->appendChild($type_dom);
		}

		foreach ($this->uri as $uri) {
			$uri_dom = $uri->to_dom($dom);
			$link_dom->appendChild($uri_dom);
		}

		foreach ($this->template_uri as $template_uri) {
			$uri_dom = $template_uri->to_dom($dom);
			$link_dom->appendChild($uri_dom);
		}

		foreach ($this->local_id as $local_id) {
			$id_dom = $local_id->to_dom($dom);
			$link_dom->appendChild($id_dom);
		}

		return $link_dom;
	}
}

?>
