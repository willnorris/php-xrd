<?php

require_once 'LRDD.php';
require_once 'LRDD/Link.php';
require_once 'LRDD/Method.php';


/**
 * LRDD Method that uses "Link" HTTP Response header
 *
 * @see http://www.ietf.org/internet-drafts/draft-nottingham-http-link-header-04.txt
 */
class LRDD_Method_Link_Header implements LRDD_Method {


	public static function discover($uri) {

		$ch = curl_init($uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'discovery/1.0 (php)');

		$content = curl_exec($ch);
		curl_close($ch);

		return self::parse($content);
	}


	/**
	 * Parse the given HTTP response headers.
	 *
	 * TODO this method does not currently handle the case where multiple 
	 * header values are included on a single line.
	 *
	 * @param string $content HTTP response headers
	 * @return array array of LRDD_Link objects
	 */
	public static function parse($content) {
		$links = array();

		$headers = explode("\r\n", $content);

		// remove HTTP response code
		array_shift($headers);

		// normalize headers that are split over multiple lines
		for ($i=(sizeof($headers)-1); $i>=0; $i--) {
			if ( strpos($headers[$i], ' ') === 0 || strpos($headers[$i], "\t") === 0 ) {
				$headers[$i-1] .= preg_replace('/^\s+/', '', $headers[$i]);
				unset($headers[$i]);
			}
		}

		foreach ($headers as $header) {
			if (empty($header)) continue;

			list ($name, $value) = explode(':', $header, 2);
			$name = trim($name);
			$value = trim($value);

			// we only care about "link" headers
			if (strcasecmp($name, 'link') != 0) continue;

			if ( $link = LRDD_Link::from_header($value) ) {
				$links[] = $link;
			}
		}

		return $links;
	}

}

?>
