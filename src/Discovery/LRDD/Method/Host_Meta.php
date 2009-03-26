<?php

require_once 'Discovery/Context.php';
require_once 'Discovery/LRDD/LinkPattern.php';
require_once 'Discovery/LRDD/Method.php';


/**
 * LRDD Method that uses /host-meta
 *
 * @see http://www.ietf.org/internet-drafts/draft-nottingham-site-meta-01.txt
 * @package Discovery
 */
class Discovery_LRDD_Method_Host_Meta implements Discovery_LRDD_Method {


	public static function discover(Discovery_Context $context) {
		$meta_url = self::hostmeta_url($context->uri);

		$request = array( 'uri' => $meta_url);

		$response = $context->fetch($request);
		$status_digit = floor( $response['response']['code'] / 100 );

		if ($status_digit == 2 || $status_digit == 3) {
			return self::parse( $response['body'] );
		}
	}

	public static function hostmeta_url($uri) {
		$parts = parse_url($uri);

		// build host-meta URL
		$meta_url = $parts['scheme'] . '://' . $parts['host'];
		if (array_key_exists('port', $parts)) $meta_url .= ':' . $parts['port'];
		$meta_url .= '/host-meta';

		return $meta_url;
	}

	/**
	 * Parse the given Host Metadata.
	 *
	 * @param string $content contents of a host-meta document
	 * @return array array of Discovery_LRDD_Link objects
	 */
	public static function parse($content) {
		$links = array();

		// be lenient with line endings
		$lines = explode("\n", str_replace(array("\r\n", "\r"), "\n", $content) );

		foreach ($lines as $line) {
			if (empty($line)) continue;

			list ($name, $value) = explode(':', $line, 2);
			$name = trim($name);
			$value = trim($value);

			// we only care about "link-pattern" host-meta entries
			if (strcasecmp($name, 'link-pattern') != 0) continue;

			$link = Discovery_LRDD_LinkPattern::from_header($value);
			if ( $link && in_array('describedby', $link->rel) ) {
				$links[] = $link;
			}
		}

		return $links;
	}

}

?>
