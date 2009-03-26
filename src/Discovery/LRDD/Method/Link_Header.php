<?php

require_once 'Discovery/Util.php';
require_once 'Discovery/Context.php';
require_once 'Discovery/LRDD/Link.php';
require_once 'Discovery/LRDD/Method.php';


/**
 * LRDD Method that uses "Link" HTTP Response header
 *
 * @see http://www.ietf.org/internet-drafts/draft-nottingham-http-link-header-04.txt
 * @package Discovery
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
	 * @param string|array $headers HTTP Link header value or array of values
	 * @return array array of Discovery_LRDD_Link objects
	 */
	public static function parse($headers) {
		$headers = (array) $headers;
		$links = array();

		foreach ($headers as $header) {
			if (empty($header)) continue;

			// we may have multiple comma-separated header values combined on a single line
			if (strpos($header, ',') !== false) {
				$split_headers = Discovery_Util::split($header, ',');
				if (sizeof($split_headers) > 1) {
					$links = array_merge($links, self::parse($split_headers));
					continue;
				}
			}

			$link = Discovery_LRDD_Link::from_header($header);
			if ( $link && in_array('describedby', $link->rel) ) {
				$links[] = $link;
			}
		}

		return $links;
	}

}

?>
