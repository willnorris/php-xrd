<?php

require_once('XRD/Type.php');
require_once('XRD/Link.php');

/**
 * XRD Descriptor.
 *
 * @package XRD
 */
class XRD {

	/** 
	 * XRD XML Namespace 
	 */
	const XML_NS = 'http://xrd.org/1.0';

	/**
	 * XRD Content Type
	 */
	const CONTENT_TYPE = 'application/xrd+xml';

	/** 
	 * Expiration date for this descriptor. 
	 *
	 * @var string
	 */
	public $expires;


	/** 
	 * Subject
	 *
	 * @var string
	 */
	public $subject;


	/** 
	 * Aliases
	 *
	 * @var array of strings
	 */
	public $alias;


	/** 
	 * Types. 
	 *
	 * @var array of XRD_Type objects
	 */
	public $type;


	/** 
	 * Links. 
	 *
	 * @var array of XRD_Link objects
	 */
	public $link;


	/**
	 * Constructor.
	 *
	 * @param mixed $type XRD_Type object or array of XRD_Type objects
	 * @param mixed $link XRD_Link object or array of XRD_Link objects
	 * @param mixed $link Alias string or array of Alias strings
	 * @param string $subject XRD subject
	 * @param string $expires expiration date
	 */
	public function __construct($type=null, $link=null, $alias=null, $subject=null, $expires=null) {
		$this->type = (array) $type;
		$this->link = (array) $link;
		$this->alias = (array) $alias;
		$this->expires = $expires;
		$this->subject = $subject;
	}


	/**
	 * Create an XRD object from a DOMElement
	 *
	 * @param DOMElement $dom DOM element to load
	 * @return XRD object
	 */
	public static function from_dom(DOMElement $dom) {
		$xrd = new XRD();

		foreach ($dom->childNodes as $node) {
			if (!isset($node->tagName)) continue;

			switch($node->tagName) {
				case 'Expires':
					$xrd->expires = $node->nodeValue;
					break;

				case 'Subject':
					$xrd->subject = $node->nodeValue;
					break;

				case 'Alias':
					$xrd->alias[] = $node->nodeValue;
					break;

				case 'Type':
					$type = XRD_Type::from_dom($node);
					$xrd->type[] = $type;
					break;

				case 'Link':
					$link = XRD_Link::from_dom($node);
					$xrd->link[] = $link;
					break;
			}
		}

		usort($xrd->link, array('XRD', 'priority_sort'));

		return $xrd;
	}


	/**
	 * Create a DOMElement from this XRD object
	 *
	 * @param DOMDocument $dom document used to create elements.
	 * @return DOMDocument
	 */
	public function to_dom($dom = null) {
		if ($dom == null) {
			$dom = new DOMDocument();
		}

		$xrd_dom = $dom->createElementNS(XRD::XML_NS, 'XRD');
		$dom->appendChild($xrd_dom);

		if ($this->expires) {
			$expires_dom = $xrd->createElement('Expires', $expires);
			$xrd_dom->appendChild($expires_dom);
		}

		if ($this->subject) {
			$subject_dom = $xrd->createElement('Subject', $expires);
			$xrd_dom->appendChild($subject_dom);
		}

		foreach ($this->alias as $alias) {
			$alias_dom = $dom->createElement('Alias', $alias);
			$xrd_dom->appendChild($alias_dom);
		}

		foreach ($this->type as $type) {
			$type_dom = $dom->createElement('Type', $type);
			$xrd_dom->appendChild($type_dom);
		}

		foreach ($this->link as $link) {
			$link_dom = $link->to_dom($dom);
			$xrd_dom->appendChild($link_dom);
		}

		return $dom;
	}


	/**
	 * Create an XRD object from the specified file.
	 *
	 * @param string $file file to load
	 * @return XRD object
	 * @see DOMDocument::load
	 */
	public static function load($file) {
		$dom = new DOMDocument();
		$dom->load($file);
		$xrd_elements = $dom->getElementsByTagName('XRD');
		
		return self::from_dom($xrd_elements->item(0));
	}


	/**
	 * Create an XRD object from the specified XML string.
	 *
	 * @param string $xml XML string to load
	 * @return XRD object
	 * @see DOMDocument::loadXML
	 */
	public static function loadXML($xml) {
		$dom = new DOMDocument();
		$dom->loadXML($xml);
		$xrd_elements = $dom->getElementsByTagName('XRD');
		
		return self::from_dom($xrd_elements->item(0));
	}


	/**
	 * Get the marshalled XML for this XRD object.
	 *
	 * @param boolean $format if true, XML output will be formatted
	 * @return string marshalled xml
	 */
	public function to_xml($format = false) {
		$dom = $this->to_dom();
		$dom->formatOutput = $format;
		return $dom->saveXML();
	}


	/**
	 * Discover XRD document for specified URI.
	 */
	public static function discover($uri) {
		require_once 'Discovery/LRDD.php';

		$links = Discovery_LRDD::discover($uri);

		foreach ($links as $link) {
			if (in_array('describedby', $link->rel) && $link->type == self::CONTENT_TYPE) {
				$content = Discovery_LRDD::fetch($link->uri);
				return self::loadXML($content);
			}
		}
	}


	/**
	 * Compare items based on the priority rules of XRD.
	 * Items are sorted in increasing priority order, with null 
	 * values interpreted as infinity.
	 *
	 * @param object $a first object to compare
	 * @param object $b second object to compare
	 * @return int -1 if $a has a lower priority, +1 if $b has a lower priority, 0 if the two priorities are equal
	 * @see usort
	 */
	public function priority_sort($a, $b) {

		// deal with null values
		if ($a->priority === null) {
			if ($b->priority === null) {
				return 0;
			} else {
				return 1;
			}
		} else if ($b->priority === null) {
			return -1;
		}

		if ($a->priority == $b->priority) return 0;
		if ($a->priority > $b->priority) return 1;
		if ($a->priority < $b->priority) return -1;
	}

}

?>
