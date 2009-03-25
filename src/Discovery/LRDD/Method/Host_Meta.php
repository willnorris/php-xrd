<?php

require_once 'Discovery/LRDD.php';
require_once 'Discovery/LRDD/Link.php';
require_once 'Discovery/LRDD/Method.php';


/**
 * LRDD Method that uses /host-meta
 *
 * @see http://www.ietf.org/internet-drafts/draft-nottingham-site-meta-01.txt
 */
class Discovery_LRDD_Method_Host_Meta implements Discovery_LRDD_Method {


	public static function discover($uri) {
		$parts = parse_url($uri);

		// build host-meta URL
		$meta_url = $parts['scheme'] . '://' . $parts['host'];
		if (array_key_exists('port', $parts)) $meta_url .= ':' . $parts['port'];
		$meta_url .= '/host-meta';

		$response = Discovery_LRDD::fetch($meta_url);
		if ($response === false) return $response;

		return self::parse($response);
	}


	/**
	 * Parse the given Host Metadata.
	 *
	 * @param string $content contents of a host-meta document
	 * @return array array of Discovery_LRDD_Link objects
	 */
	public static function parse($content) {
		$links = array();

		$lines = explode("\r\n", $content);

		foreach ($lines as $line) {
			if (empty($line)) continue;

			list ($name, $value) = explode(':', $line, 2);
			$name = trim($name);
			$value = trim($value);

			// we only care about "link" host-meta entries
			// TODO do something with the other host-meta entries
			// TODO we actaully should only be working with link-patterns in host-meta
			if (strcasecmp($name, 'link') != 0) continue;

			$link = Discovery_LRDD_Link::from_header($value);
			if ( $link && in_array('describedby', $link->rel) ) {
				$links[] = $link;
			}
		}

		return $links;
	}

}

?>
