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
	 * @param string|array $headers HTTP Link header value or array of values
	 * @return array array of Discovery_LRDD_Link objects
	 */
	public static function parse($headers) {
		$headers = (array) $headers;
		$links = array();

		foreach ($headers as $header) {
			if (empty($header)) continue;

			$link = Discovery_LRDD_Link::from_header($header);
			if ( $link && in_array('describedby', $link->rel) ) {
				$links[] = $link;
			}
		}

		return $links;
	}

}

?>
