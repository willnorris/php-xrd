<?php

require_once 'Discovery.php';
require_once 'Discovery/Link.php';

/**
 * A XRD Discovery Methods implements one way of discovering the metadata for a URI
 */
interface Discovery_Method 
{
	public static function discover($uri);
}


/**
 * Discovery Method that uses /host-meta
 *
 * @see http://www.ietf.org/internet-drafts/draft-nottingham-site-meta-01.txt
 */
class Discovery_Host_Meta implements Discovery_Method {


	public static function discover($uri) {
		$parts = parse_url($uri);

		// build host-meta URL
		$meta_url = $parts['scheme'] . '://' . $parts['host'];
		if (array_key_exists('port', $parts)) $meta_url .= ':' . $parts['port'];
		$meta_url .= '/host-meta';

		$response = Discovery::fetch($meta_url);
		if ($response === false) return $response;

		return self::parse($response);
	}


	/**
	 * Parse the given Host Metadata.
	 *
	 * @param string $content contents of a host-meta document
	 * @return array array of Discovery_Link objects
	 */
	public static function parse($content) {
		$links = array();

		$lines = explode("\n", $content);

		foreach ($lines as $line) {
			if (empty($line)) continue;

			list ($name, $value) = explode(':', $line, 2);
			$name = trim($name);
			$value = trim($value);

			// we only care about "link" host-meta entries
			// TODO do something with the other host-meta entries
			if (strcasecmp($name, 'link') != 0) continue;

			$params = explode(';', $value);
			if (sizeof($params) == 0) continue;

			// link uri is always first
			$link_uri = array_shift($params);
			$link_uri = preg_replace('(^<|>$)', '', trim($link_uri));

			$link_rel = array();
			$link_type = array();

			// parse remaining link-params
			foreach ($params as $param) {
				list($param_name, $param_value) = explode('=', $param, 2);
				$param_name = trim($param_name);
				$param_value = preg_replace('(^"|"$)', '', trim($param_value));

				// for now we only care about 'rel' and 'type' link params
				// TODO do something with the other links-params
				switch ($param_name) {
					case 'rel':
						$rel_values = preg_split('/\s+/', $param_value);
						$link_rel = array_merge($link_rel, $rel_values);
						break;

					case 'type':
						$link_type = trim($param_value);
				}
			}

			$links[] = new Discovery_Link($link_uri, $link_rel, $link_type);
		}

		return $links;
	}

}

?>
