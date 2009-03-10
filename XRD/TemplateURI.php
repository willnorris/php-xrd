<?php

require_once 'XRD/URI.php';

/**
 * XRD TemplateURI.
 */
class XRD_TemplateURI extends XRD_URI {


	public function nodeName() {
		return 'TemplateURI';
	}


	public function applyTemplate($resource) {
		$components = self::getComponents($resource);

		$valid = array('scheme', 'authority', 'path', 'query', 'fragment', 'userinfo', 'host', 'port', 'uri');

		$pattern = '/\{(%?( ' . implode('|', $valid) . '))\}/e';
		$output = preg_replace($pattern, 'self::preg_callback("\1", $components)', $this->uri);
		return $output;
	}


	private static function preg_callback($key, $components) {
		if (substr($key, 0, 1) == '%') {
			$encode = true;
			$key = substr($key, 1);
		}

		$value = $components[$key];
		if (isset($encode) && $encode) $value = urlencode($value);

		return $value;
	}


	/**
	 * Break the resource URI into its URI components.
	 *
	 * @param string $resource resource URI
	 * @return array URI components of resource URI
	 * @see http://www.ietf.org/internet-drafts/draft-hammer-discovery-02.txt
	 */
	public static function getComponents($resource) {
		$components = parse_url($resource);

		$components['uri'] = $resource;
		if (array_key_exists('fragment', $components)) {
			$pattern = '/#' . preg_quote($components['fragment']) . '$/';
			$components['uri'] = preg_replace($pattern, '', $components['uri']);
		}

		// rename 'user' to 'userinfo'
		if (array_key_exists('user', $components)) {
			$components['userinfo'] = $components['user'];
			unset($components['user']);
		}

		// rfc3986 strongly discourages including the password in the userinfo
		if (array_key_exists('pass', $components)) unset($components['pass']);

		// build authority
		$components['authority'] = $components['host'];
		if (array_key_exists('userinfo', $components)) {
			$components['authority'] = $components['userinfo'] . '@' . $components['authority'];
		}
		if (array_key_exists('port', $components)) {
			$components['authority'] .= ':' . $components['port'];
		}

		return $components;
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
