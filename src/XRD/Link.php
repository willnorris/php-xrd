<?php

require_once 'XRD/Title.php';
require_once 'XRD/Property.php';

/**
 * XRD Link.
 *
 * @package XRD
 */
class XRD_Link {

	/** 
	 * Rel.
	 *
	 * @var string
	 */
	public $rel;

	/** 
	 * Type.
	 *
	 * @var string
	 */
	public $type;

	/** 
	 * Href.
	 *
	 * @var string
	 */
	public $href;
	
	/** 
	 * Template.
	 *
	 * @var string
	 */
	public $template;
  
  /** 
	 * Title.
	 *
	 * @var array of Titles
	 */
	public $title;

  /** 
	 * Property.
	 *
	 * @var array of Properties
	 */
	public $property;
	
	/**
	 * Constructor.
	 *
	 * @param string $rel Rel string
	 * @param string $type Type string
	 * @param string $href Href string
	 * @param string $template Template string
	 * @param mixed $title XRD_Title object or array of XRD_Title objects
	 * @param mixed $property XRD_Property object or array of XRD_Property objects
	 */
	public function __construct($rel=null, $type=null, $href=null, $template=null, $title=null, $property=null) {
		$this->rel = $rel;
		$this->type = $type;
		$this->href = $href;
		$this->template = $template;
		$this->title = (array) $title;
		$this->property = (array) $property;
	}


	/**
	 * Create an XRD_Link object from a DOMElement.
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRD_Link object
	 */
	public static function from_dom(DOMElement $dom) {
		$link = new self();
    
    if ($dom->hasAttribute('rel')) {
      $link->rel = $dom->getAttribute('rel');
    }
    
    if ($dom->hasAttribute('href')) {
      $link->href = $dom->getAttribute('href');
    }
    
    if ($dom->hasAttribute('type')) {
      $link->type = $dom->getAttribute('type');
    }
    
    if ($dom->hasAttribute('template')) {
      $link->template = $dom->getAttribute('template');
    }
    
		foreach ($dom->childNodes as $node) {
			if (!isset($node->tagName)) continue;

			switch($node->tagName) {
				case 'Title':
					$title = XRD_Title::from_dom($node);
					$link->title[] = $title;
					break;

				case 'Property':
					$property = XRD_Property::from_dom($node);
					$link->property[] = $property;
					break;
			}
		}

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
    
		if ($this->rel) {
			$link_dom->setAttribute('rel', $this->rel);
		}
		
		if ($this->type) {
			$link_dom->setAttribute('type', $this->type);
		}
		
		if ($this->href) {
			$link_dom->setAttribute('href', $this->href);
		}
		
		if ($this->template) {
			$link_dom->setAttribute('template', $this->template);
		}

		foreach ($this->title as $title) {
			$title_dom = $title->to_dom($dom);
			$link_dom->appendChild($title_dom);
		}
		
		foreach ($this->property as $property) {
			$property_dom = $property->to_dom($dom);
			$link_dom->appendChild($property_dom);
		}

		return $link_dom;
	}
}

?>