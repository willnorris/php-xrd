<?php

/**
 * HTTP request method uses fsockopen function to retrieve the url.
 *
 * This would be the preferred method, but the fsockopen implementation has the most overhead of all
 * the HTTP transport implementations.
 *
 * @package WordPress
 * @subpackage HTTP
 * @since 2.7.0
 */
class WP_Http_Fsockopen {
	/**
	 * Send a HTTP request to a URI using fsockopen().
	 *
	 * Does not support non-blocking mode.
	 *
	 * @see WP_Http::request For default options descriptions.
	 *
	 * @since 2.7
	 * @access public
	 * @param string $url URI resource.
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

		$iError = null; // Store error number
		$strError = null; // Store error string

		$arrURL = parse_url($url);

		$secure_transport = false;

		if ( ! isset($arrURL['port']) ) {
			if ( ($arrURL['scheme'] == 'ssl' || $arrURL['scheme'] == 'https') && extension_loaded('openssl') ) {
				$arrURL['host'] = 'ssl://' . $arrURL['host'];
				$arrURL['port'] = 443;
				$secure_transport = true;
			} else {
				$arrURL['port'] = 80;
			}
		}

		// There are issues with the HTTPS and SSL protocols that cause errors that can be safely
		// ignored and should be ignored.
		if ( true === $secure_transport )
			$error_reporting = error_reporting(0);

		$startDelay = time();

		$proxy = new WP_HTTP_Proxy();

		if ( !defined('WP_DEBUG') || ( defined('WP_DEBUG') && false === WP_DEBUG ) ) {
			if( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) )
				$handle = @fsockopen($proxy->host(), $proxy->port(), $iError, $strError, $r['timeout'] );
			else
				$handle = @fsockopen($arrURL['host'], $arrURL['port'], $iError, $strError, $r['timeout'] );
		}
		else {
			if( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) )
				$handle = fsockopen($proxy->host(), $proxy->port(), $iError, $strError, $r['timeout'] );
			else
				$handle = fsockopen($arrURL['host'], $arrURL['port'], $iError, $strError, $r['timeout'] );
		}

		$endDelay = time();

		// If the delay is greater than the timeout then fsockopen should't be used, because it will
		// cause a long delay.
		$elapseDelay = ($endDelay-$startDelay) > $r['timeout'];
		if ( true === $elapseDelay ) {
		}

		if ( false === $handle )
			throw new WP_Http_Fsockopen_Exception( $iError . ': ' . $strError );

		// WordPress supports PHP 4.3, which has this function. Removed sanity checking for
		// performance reasons.
		stream_set_timeout($handle, $r['timeout'] );

		$requestPath = $arrURL['path'] . ( isset($arrURL['query']) ? '?' . $arrURL['query'] : '' );
		$requestPath = empty($requestPath) ? '/' : $requestPath;

		$strHeaders = '';
		$strHeaders .= strtoupper($r['method']) . ' ' . $requestPath . ' HTTP/' . $r['httpversion'] . "\r\n";

		if( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) )
			$strHeaders .= 'Host: ' . $arrURL['host'] .':'. $arrURL['port'] . "\r\n";
		else
			$strHeaders .= 'Host: ' . $arrURL['host'] . "\r\n";

		if( isset($r['user-agent']) )
			$strHeaders .= 'User-agent: ' . $r['user-agent'] . "\r\n";

		if ( is_array($r['headers']) ) {
			foreach ( (array) $r['headers'] as $header => $headerValue )
				$strHeaders .= $header . ': ' . $headerValue . "\r\n";
		} else {
			$strHeaders .= $r['headers'];
		}

		if ( $proxy->use_authentication() ) {
			$strHeaders .= $proxy->authentication_header() . "\r\n";
		}

		$strHeaders .= "\r\n";

		if ( ! is_null($r['body']) )
			$strHeaders .= $r['body'];

		fwrite($handle, $strHeaders);

		if ( ! $r['blocking'] ) {
			fclose($handle);
			return array( 'headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array() );
		}

		$strResponse = '';
		while ( ! feof($handle) )
			$strResponse .= fread($handle, 4096);

		fclose($handle);

		if ( true === $secure_transport )
			error_reporting($error_reporting);

		$process = WP_Http::processResponse($strResponse);
		$arrHeaders = WP_Http::processHeaders($process['headers']);

		// Is the response code within the 400 range?
		if ( (int) $arrHeaders['response']['code'] >= 400 && (int) $arrHeaders['response']['code'] < 500 )
			throw new WP_Http_Fsockopen_Exception( $arrHeaders['response']['code'] . ': ' . $arrHeaders['response']['message'] );

		// If location is found, then assume redirect and redirect to location.
		if ( isset($arrHeaders['headers']['location']) ) {
			if ( $r['redirection']-- > 0 ) {
				return $this->request($arrHeaders['headers']['location'], $r);
			} else {
				throw new WP_Http_Fsockopen_Exception( 'Too many redirects.' );
			}
		}

		// If the body was chunk encoded, then decode it.
		if ( ! empty( $process['body'] ) && isset( $arrHeaders['headers']['transfer-encoding'] ) && 'chunked' == $arrHeaders['headers']['transfer-encoding'] )
			$process['body'] = WP_Http::chunkTransferDecode($process['body']);

		if ( true === $r['decompress'] && true === WP_Http_Encoding::should_decode($arrHeaders) )
			$process['body'] = WP_Http_Encoding::decompress( $process['body'] );

		return array('headers' => $arrHeaders['headers'], 'body' => $process['body'], 'response' => $arrHeaders['response'], 'cookies' => $arrHeaders['cookies']);
	}

	/**
	 * Whether this class can be used for retrieving an URL.
	 *
	 * @since 2.7.0
	 * @static
	 * @return boolean False means this class can not be used, true means it can.
	 */
	function test($args = array()) {
		if ( function_exists( 'fsockopen' ) && ( isset($args['ssl']) && !$args['ssl'] ) )
			return true;

		return false;
	}
}

class WP_Http_Fsockopen_Exception extends WP_Http_Exception {
}

?>
