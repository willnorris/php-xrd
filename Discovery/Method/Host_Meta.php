<?php

require_once 'Discovery.php';
require_once 'Discovery/Link.php';
require_once 'Discovery/Method.php';


/**
 * Discovery Method that uses /host-meta
 *
 * @see http://www.ietf.org/internet-drafts/draft-nottingham-site-meta-01.txt
 */
class Discovery_Method_Host_Meta implements Discovery_Method {


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

			$link = Discovery_Link::from_header($value);
			if ($link) {
				$links[] = $link;
			}
		}

		return $links;
	}

}

?>
