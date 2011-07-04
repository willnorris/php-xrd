<?php

require_once 'Discovery/Context.php';
require_once 'XRDS.php';

/**
 * A XRDS Discovery Methods implements one way of discovering the metadata for a URI
 *
 * @package Discovery
 */
interface Discovery_Yadis_Method {
	public static function discover(Discovery_Context $context);
}


/**
 * XRDS Discovery Method that uses HTTP content negotiation.
 *
 * @package Discovery
 */
class Discovery_Yadis_Content_Negotiation implements Discovery_Yadis_Method {

	const CONTENT_TYPE = 'application/xrds+xml';

	public static function discover(Discovery_Context $context) {
		$request = array(
			'uri' => $context->uri,
			'headers' => array( 'accept' => self::CONTENT_TYPE ),
		);

		$response = $context->fetch($request);
		$status_digit = floor( $response['response']['code'] / 100 );

		if ($status_digit == 2 || $status_digit == 3) {
			if ($response['headers']['content-type'] == self::CONTENT_TYPE) {
				return XRDS::loadXML($response['body']);
			}
		}
	}

}


/**
 * XRDS Discovery Method that looks for an HTTP response header advertising the location of the XRDS document.
 *
 * @package Discovery
 */
class Discovery_Yadis_Location_Header implements Discovery_Yadis_Method {

	const XRDS_HEADER = 'x-xrds-location';

	public static function discover(Discovery_Context $context) {
		$request = array( 'uri' => $context->uri );
		$response = $context->fetch($request);
		$status_digit = floor( $response['response']['code'] / 100 );

		if ($status_digit == 2 || $status_digit == 3) {
			if (array_key_exists('x-xrds-location', $response['headers'])) {
				return Discovery_Yadis::fetch_xrds_url($response['headers']['x-xrds-location'], $context);
			}
		}
	}

}


/**
 * XRDS Discovery Method that looks for an HTML Meta element advertising the location of the XRDS document.
 *
 * @package Discovery
 */
class Discovery_Yadis_HTML_Meta implements Discovery_Yadis_Method {

	public static function discover(Discovery_Context $context) {
		$request = array( 'uri' => $context->uri );
		$response = $context->fetch($request);
		$status_digit = floor( $response['response']['code'] / 100 );

		if ($status_digit == 2 || $status_digit == 3) {
			$xrds_url = self::parse($response['body']);
			if ( $xrds_url ) {
				return Discovery_Yadis::fetch_xrds_url($xrds_url, $context);
			}
		}
	}

	/**
	 * Parse the given HTML.
	 *
	 * @param string $content HTML content
	 * @return array array of Discovery_LRDD_Link objects
	 */
	public static function parse($html) {
		preg_match('/<head(\s[^>]*)?>(.*?)<\/head>/is', $html, $head_matches);
		$head_html = $head_matches[2];

		preg_match_all('/<meta\s[^>]*>/i', $head_html, $meta_matches);

		foreach ($meta_matches[0] as $meta_html) {
			$meta_equiv = null;
			$meta_content = null;

			preg_match('/\shttp-equiv=(("|\')([^\\2]*?)\\2|[^"\'\s]+)/i', $meta_html, $equiv_matches);
			if ( isset($equiv_matches[3]) ) {
				$meta_equiv = $equiv_matches[3];
			} else if ( isset($equiv_matches[1]) ) {
				$meta_equiv = $equiv_matches[1];
			}
			if ( strcasecmp($meta_equiv, 'x-xrds-location') != 0 ) continue;

			preg_match('/\scontent=(("|\')([^\\2]*?)\\2|[^"\'\s]+)/i', $meta_html, $content_matches);
			if ( isset($content_matches[3]) ) {
				$meta_content = $content_matches[3];
			} else if ( isset($content_matches[1]) ) {
				$meta_content = $content_matches[1];
			}

			return $meta_content;
		}
	}
}

?>
