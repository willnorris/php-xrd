<?php

/**
 * HTTP request method uses Streams to retrieve the url.
 *
 * Requires PHP 5.0+ and uses fopen with stream context. Requires that 'allow_url_fopen' PHP setting
 * to be enabled.
 *
 * Second preferred method for getting the URL, for PHP 5.
 *
 * @package WordPress
 * @subpackage HTTP
 * @since 2.7.0
 */
class WP_Http_Streams {
	/**
	 * Send a HTTP request to a URI using streams with fopen().
	 *
	 * @access public
	 * @since 2.7.0
	 *
	 * @param string $url
	 * @param str|array $args Optional. Override the defaults.
	 * @return array 'headers', 'body', 'cookies' and 'response' keys.
	 */
	function request($url, $args = array()) {
		$defaults = array(
			'method' => 'GET', 'timeout' => 5,
			'redirection' => 5, 'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(), 'body' => null, 'cookies' => array()
		);

		$r = array_merge( $defaults, $args );

		if ( isset($r['headers']['User-Agent']) ) {
			$r['user-agent'] = $r['headers']['User-Agent'];
			unset($r['headers']['User-Agent']);
		} else if( isset($r['headers']['user-agent']) ) {
			$r['user-agent'] = $r['headers']['user-agent'];
			unset($r['headers']['user-agent']);
		}

		// Construct Cookie: header if any cookies are set
		WP_Http::buildCookieHeader( $r );

		$arrURL = parse_url($url);

		if ( false === $arrURL )
			throw new WP_Http_Streams_Exception( 'Malformed URL: ' . $url );

		if ( 'http' != $arrURL['scheme'] && 'https' != $arrURL['scheme'] )
			$url = str_replace($arrURL['scheme'], 'http', $url);

		// Convert Header array to string.
		$strHeaders = '';
		if ( is_array( $r['headers'] ) )
			foreach( $r['headers'] as $name => $value )
				$strHeaders .= "{$name}: $value\r\n";
		else if ( is_string( $r['headers'] ) )
			$strHeaders = $r['headers'];

		$arrContext = array('http' =>
			array(
				'method' => strtoupper($r['method']),
				'user_agent' => $r['user-agent'],
				'max_redirects' => $r['redirection'],
				'protocol_version' => (float) $r['httpversion'],
				'header' => $strHeaders,
				'timeout' => $r['timeout'],
				'ssl' => array(
						'verify_peer' => $r['sslverify'],
						'verify_host' => $r['sslverify']
				)
			)
		);

		$proxy = new WP_HTTP_Proxy();

		if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {
			$arrContext['http']['proxy'] = 'tcp://'.$proxy->host().':'.$proxy->port();

			// We only support Basic authentication so this will only work if that is what your proxy supports.
			if ( $proxy->use_authentication() ) {
				$arrContext['http']['header'] .= $proxy->authentication_header() . "\r\n";
			}
		}

		if ( ! is_null($r['body']) && ! empty($r['body'] ) )
			$arrContext['http']['content'] = $r['body'];

		$context = stream_context_create($arrContext);

		if ( ! defined('WP_DEBUG') || ( defined('WP_DEBUG') && false === WP_DEBUG ) )
			$handle = @fopen($url, 'r', false, $context);
		else
			$handle = fopen($url, 'r', false, $context);

		if ( ! $handle)
			throw new WP_Http_Streams_Exception( 'Could not open handle for fopen() to ' . $url );

		// WordPress supports PHP 4.3, which has this function. Removed sanity checking for
		// performance reasons.
		stream_set_timeout($handle, $r['timeout'] );

		if ( ! $r['blocking'] ) {
			stream_set_blocking($handle, 0);
			fclose($handle);
			return array( 'headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array() );
		}

		$strResponse = stream_get_contents($handle);
		$meta = stream_get_meta_data($handle);

		fclose($handle);

		$processedHeaders = array();
		if( isset( $meta['wrapper_data']['headers'] ) )
			$processedHeaders = WP_Http::processHeaders($meta['wrapper_data']['headers']);
		else
			$processedHeaders = WP_Http::processHeaders($meta['wrapper_data']);

		if ( ! empty( $strResponse ) && isset( $processedHeaders['headers']['transfer-encoding'] ) && 'chunked' == $processedHeaders['headers']['transfer-encoding'] )
			$strResponse = WP_Http::chunkTransferDecode($strResponse);

		if ( true === $r['decompress'] && true === WP_Http_Encoding::should_decode($processedHeaders) )
			$strResponse = WP_Http_Encoding::decompress( $strResponse );

		return array('headers' => $processedHeaders['headers'], 'body' => $strResponse, 'response' => $processedHeaders['response'], 'cookies' => $processedHeaders['cookies']);
	}

	/**
	 * Whether this class can be used for retrieving an URL.
	 *
	 * @static
	 * @access public
	 * @since 2.7.0
	 *
	 * @return boolean False means this class can not be used, true means it can.
	 */
	function test($args = array()) {
		if ( ! function_exists('fopen') || (function_exists('ini_get') && true != ini_get('allow_url_fopen')) )
			return false;

		if ( version_compare(PHP_VERSION, '5.0', '<') )
			return false;

		return true;
	}
}

class WP_Http_Streams_Exception extends WP_Http_Exception {
}

?>
