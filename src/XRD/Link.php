<?php

require_once 'XRD/URI.php';
require_once 'XRD/URITemplate.php';
require_once 'XRD/SourceAlias.php';

/**
 * XRD Link.
 *
 * @package XRD
 */
class XRD_Link {

	/** 
	 * Priority. 
	 *
	 * @var int
	 */
	public $priority;


	/**
	 * Target Subject.
	 *
	 * @var string
	 */
	public $target_subject;

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
	 * @var array of XRD_URITemplate objects
	 */
	public $uri_template;


	/** 
	 * Source Aliases.
	 *
	 * @var array of XRD_SourceAlias objects
	 */
	public $source_alias;


	/**
	 * Constructor.
	 *
	 * @param mixed $rel Rel string or array of Rel strings
	 * @param mixed $media_type Media Type string or array of Media Type strings
	 * @param mixed $uri XRD_URI object or array of XRD_URI objects
	 * @param mixed $uri_template XRD_URITemplate object or array of XRD_URITemplate objects
	 * @param mixed $source_alias XRD_SourceAlias object or array of XRD_SourceAlias objects
	 * @param int $priority Priority
	 * @param string $target_subject TargetSubject value
	 */
	public function __construct($rel=null, $media_type=null, $uri=null, $uri_template=null, $source_alias=null, $priority=10, $target_subject=null) {
		$this->rel = (array) $rel;
		$this->media_type = (array) $media_type;
		$this->uri = (array) $uri;
		$this->uri_template = (array) $uri_template;
		$this->source_alias = (array) $source_alias;
		$this->priority = $priority;
		$this->target_subject = (string) $target_subject;
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
				case 'TargetSubject':
					$link->target_subject = $node->nodeValue;
					break;

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

				case 'URITemplate':
					$uri_template = XRD_URITemplate::from_dom($node);
					$link->uri_template[] = $uri_template;
					break;

				case 'SourceAlias':
					$source_alias = XRD_SourceAlias::from_dom($node);
					$link->source_alias[] = $source_alias;
					break;
			}
		}

		usort($link->uri, array('XRD', 'priority_sort'));
		usort($link->uri_template, array('XRD', 'priority_sort'));
		usort($link->source_alias, array('XRD', 'priority_sort'));

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

		if ($this->target_subject) {
			$subject_dom = $dom->createElement('TargetSubject', $this->target_subject);
			$link_dom->appendChild($subject_dom);
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

		foreach ($this->uri_template as $uri_template) {
			$uri_dom = $uri_template->to_dom($dom);
			$link_dom->appendChild($uri_dom);
		}

		foreach ($this->source_alias as $source_alias) {
			$alias_dom = $source_alias->to_dom($dom);
			$link_dom->appendChild($alias_dom);
		}

		return $link_dom;
	}
}

?>
