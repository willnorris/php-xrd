<?php

require_once 'LRDD.php';
require_once 'LRDD/Link.php';
require_once 'LRDD/Method.php';


/**
 * LRDD Method that uses /host-meta
 *
 * @see http://www.ietf.org/internet-drafts/draft-nottingham-site-meta-01.txt
 */
class LRDD_Method_Host_Meta implements LRDD_Method {


	public static function discover($uri) {
		$parts = parse_url($uri);

		// build host-meta URL
		$meta_url = $parts['scheme'] . '://' . $parts['host'];
		if (array_key_exists('port', $parts)) $meta_url .= ':' . $parts['port'];
		$meta_url .= '/host-meta';

		$response = LRDD::fetch($meta_url);
		if ($response === false) return $response;

		return self::parse($response);
	}


	/**
	 * Parse the given Host Metadata.
	 *
	 * @param string $content contents of a host-meta document
	 * @return array array of LRDD_Link objects
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

			$link = LRDD_Link::from_header($value);
			if ($link) {
				$links[] = $link;
			}
		}

		return $links;
	}

}

?>
