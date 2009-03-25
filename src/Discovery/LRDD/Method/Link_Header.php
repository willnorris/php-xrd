<?php

require_once 'Discovery/Context.php';
require_once 'Discovery/LRDD/Link.php';
require_once 'Discovery/LRDD/Method.php';


/**
 * LRDD Method that uses "Link" HTTP Response header
 *
 * @see http://www.ietf.org/internet-drafts/draft-nottingham-http-link-header-04.txt
 */
class Discovery_LRDD_Method_Link_Header implements Discovery_LRDD_Method {


	public static function discover(Discovery_Context $context) {

		$request = null; // create request object

		$response = $context->fetch($request);
		$status_digit = floor( $response->getStatus() / 100 );

		if ($status_digit == 2 || $status_digit == 3) {
			return self::parse( $response->getHeader('link') );
		}
	}


	/**
	 * Parse the given HTTP response headers.
	 *
	 * TODO this method does not currently handle the case where multiple 
	 * header values are included on a single line.
	 *
	 * @param string $content HTTP response headers
	 * @return array array of Discovery_LRDD_Link objects
	 */
	public static function parse($content) {
		$links = array();

		$headers = explode("\r\n", $content);

		// remove HTTP response code
		array_shift($headers);

		// normalize headers that are split over multiple lines
		for ($i=(sizeof($headers)-1); $i>=0; $i--) {
			$char = substr($headers[$i], 0, 1);
			if ( $char == ' ' || $char == "\t" ) {
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

			$link = Discovery_LRDD_Link::from_header($value);
			if ( $link && in_array('describedby', $link->rel) ) {
				$links[] = $link;
			}
		}

		return $links;
	}

}

?>
