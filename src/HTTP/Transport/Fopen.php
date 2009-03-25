<?php

/**
 * HTTP request method uses fopen function to retrieve the url.
 *
 * Requires PHP version greater than 4.3.0 for stream support. Does not allow for $context support,
 * but should still be okay, to write the headers, before getting the response. Also requires that
 * 'allow_url_fopen' to be enabled.
 *
 * @package WordPress
 * @subpackage HTTP
 * @since 2.7.0
 */
class WP_Http_Fopen {
	/**
	 * Send a HTTP request to a URI using fopen().
	 *
	 * This transport does not support sending of headers and body, therefore should not be used in
	 * the instances, where there is a body and headers.
	 *
	 * Notes: Does not support non-blocking mode. Ignores 'redirection' option.
	 *
	 * @see WP_Http::retrieve For default options descriptions.
	 *
	 * @access public
	 * @since 2.7.0
	 *
	 * @param string $url URI resource.
	 * @param str|array $args Optional. Override the defaults.
	 * @return array 'headers', 'body', 'cookies' and 'response' keys.
	 */
	function request($url, $args = array()) {
		global $http_response_header;

		$defaults = array(
			'method' => 'GET', 'timeout' => 5,
			'redirection' => 5, 'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(), 'body' => null, 'cookies' => array()
		);

		$r = array_merge( $defaults, $args );

		$arrURL = parse_url($url);

		if ( false === $arrURL )
			throw new WP_Http_Fopen_Exception( 'Malformed URL: ' . $url );

		if ( 'http' != $arrURL['scheme'] && 'https' != $arrURL['scheme'] )
			$url = str_replace($arrURL['scheme'], 'http', $url);

		if ( !defined('WP_DEBUG') || ( defined('WP_DEBUG') && false === WP_DEBUG ) )
			$handle = @fopen($url, 'r');
		else
			$handle = fopen($url, 'r');

		if (! $handle)
			throw new WP_Http_Fopen_Exception( 'Could not open handle for fopen() to ' . $url );

		// WordPress supports PHP 4.3, which has this function. Removed sanity
		// checking for performance reasons.
		stream_set_timeout($handle, $r['timeout'] );

		if ( ! $r['blocking'] ) {
			fclose($handle);
			return array( 'headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array() );
		}

		$strResponse = '';
		while ( ! feof($handle) )
			$strResponse .= fread($handle, 4096);

		$theHeaders = '';
		if ( function_exists('stream_get_meta_data') ) {
			$meta = stream_get_meta_data($handle);
			$theHeaders = $meta['wrapper_data'];
			if( isset( $meta['wrapper_data']['headers'] ) )
				$theHeaders = $meta['wrapper_data']['headers'];
		} else {
			if( ! isset( $http_response_header ) )
				global $http_response_header;
			$theHeaders = $http_response_header;
		}

		fclose($handle);

		$processedHeaders = WP_Http::processHeaders($theHeaders);

		if ( ! empty( $strResponse ) && isset( $processedHeaders['headers']['transfer-encoding'] ) && 'chunked' == $processedHeaders['headers']['transfer-encoding'] )
			$strResponse = WP_Http::chunkTransferDecode($strResponse);

		if ( true === $r['decompress'] && true === WP_Http_Encoding::should_decode($processedHeaders) )
			$strResponse = WP_Http_Encoding::decompress( $strResponse );

		return array('headers' => $processedHeaders['headers'], 'body' => $strResponse, 'response' => $processedHeaders['response'], 'cookies' => $processedHeaders['cookies']);
	}

	/**
	 * Whether this class can be used for retrieving an URL.
	 *
	 * @since 2.7.0
	 * @static
	 * @return boolean False means this class can not be used, true means it can.
	 */
	function test() {
		if ( ! function_exists('fopen') || (function_exists('ini_get') && true != ini_get('allow_url_fopen')) )
			return false;

		if ( isset($args['ssl']) && !$args['ssl'] )
			return true;

		if ( isset($args['sslverify']) && !$args['sslverify'] )
			return true;

		return false;
	}
}

class WP_Http_Fopen_Exception extends WP_Http_Exception {
}

?>
