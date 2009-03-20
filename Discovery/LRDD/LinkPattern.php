<?php

require_once 'Discovery/LRDD/Link.php';

class Discovery_LRDD_LinkPattern extends Discovery_LRDD_Link {

	public function __construct($uri = null, $rel = null, $type = null) {
		$this->uri = $uri;
		$this->rel = (array) $rel;
		$this->type = $type;
	}

	public function applyPattern($resource, $uri = null) {
		if ($uri == null && isset($this)) $uri = $this->uri;
		$components = self::getComponents($resource);

		$valid = array('scheme', 'authority', 'path', 'query', 'fragment', 'userinfo', 'host', 'port', 'uri');

		$pattern = '/\{(%?( ' . implode('|', $valid) . '))\}/e';
		$output = preg_replace($pattern, 'self::preg_callback("\1", $components)', $uri);
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

}

?>
