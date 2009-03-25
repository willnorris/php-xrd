<?php

/**
 * HTTP request method uses Curl extension to retrieve the url.
 *
 * Requires the Curl extension to be installed.
 *
 * @package WordPress
 * @subpackage HTTP
 * @since 2.7
 */
class WP_Http_Curl {

	/**
	 * Send a HTTP request to a URI using cURL extension.
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

		// Construct Cookie: header if any cookies are set.
		WP_Http::buildCookieHeader( $r );

		// cURL extension will sometimes fail when the timeout is less than 1 as it may round down
		// to 0, which gives it unlimited timeout.
		if ( $r['timeout'] > 0 && $r['timeout'] < 1 )
			$r['timeout'] = 1;

		$handle = curl_init();

		// cURL offers really easy proxy support.
		$proxy = new WP_HTTP_Proxy();

		if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {
			curl_setopt( $handle, CURLOPT_HTTPPROXYTUNNEL, true );

			$isPHP5 = version_compare(PHP_VERSION, '5.0.0', '>=');

			if ( $isPHP5 ) {
				curl_setopt( $handle, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
				curl_setopt( $handle, CURLOPT_PROXY, $proxy->host() );
				curl_setopt( $handle, CURLOPT_PROXYPORT, $proxy->port() );
			} else {
				curl_setopt( $handle, CURLOPT_PROXY, $proxy->host() .':'. $proxy->port() );
			}

			if ( $proxy->use_authentication() ) {
				if ( $isPHP5 )
					curl_setopt( $handle, CURLOPT_PROXYAUTH, CURLAUTH_BASIC );

				curl_setopt( $handle, CURLOPT_PROXYUSERPWD, $proxy->authentication() );
			}
		}

		curl_setopt( $handle, CURLOPT_URL, $url);
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, $r['sslverify'] );
		curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, $r['sslverify'] );
		curl_setopt( $handle, CURLOPT_USERAGENT, $r['user-agent'] );
		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, $r['timeout'] );
		curl_setopt( $handle, CURLOPT_TIMEOUT, $r['timeout'] );
		curl_setopt( $handle, CURLOPT_MAXREDIRS, $r['redirection'] );

		switch ( $r['method'] ) {
			case 'HEAD':
				curl_setopt( $handle, CURLOPT_NOBODY, true );
				break;
			case 'POST':
				curl_setopt( $handle, CURLOPT_POST, true );
				curl_setopt( $handle, CURLOPT_POSTFIELDS, $r['body'] );
				break;
		}

		if ( true === $r['blocking'] )
			curl_setopt( $handle, CURLOPT_HEADER, true );
		else
			curl_setopt( $handle, CURLOPT_HEADER, false );

		// The option doesn't work with safe mode or when open_basedir is set.
		if ( !ini_get('safe_mode') && !ini_get('open_basedir') )
			curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, true );

		if ( !empty( $r['headers'] ) ) {
			// cURL expects full header strings in each element
			$headers = array();
			foreach ( $r['headers'] as $name => $value ) {
				$headers[] = "{$name}: $value";
			}
			curl_setopt( $handle, CURLOPT_HTTPHEADER, $headers );
		}

		if ( $r['httpversion'] == '1.0' )
			curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
		else
			curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );

		// We don't need to return the body, so don't. Just execute request and return.
		if ( ! $r['blocking'] ) {
			curl_exec( $handle );
			curl_close( $handle );
			return array( 'headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array() );
		}

		$theResponse = curl_exec( $handle );

		if ( !empty($theResponse) ) {
			$parts = explode("\r\n\r\n", $theResponse);

			$headerLength = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
			$theHeaders = trim( substr($theResponse, 0, $headerLength) );
			$theBody = substr( $theResponse, $headerLength );
			if ( false !== strrpos($theHeaders, "\r\n\r\n") ) {
				$headerParts = explode("\r\n\r\n", $theHeaders);
				$theHeaders = $headerParts[ count($headerParts) -1 ];
			}
			$theHeaders = WP_Http::processHeaders($theHeaders);
		} else {
			if ( $curl_error = curl_error($handle) )
				throw new WP_Http_Curl_Exception( $curl_error );
			if ( in_array( curl_getinfo( $handle, CURLINFO_HTTP_CODE ), array(301, 302) ) )
				throw new WP_Http_Curl_Exception( 'Too many redirects.' );

			$theHeaders = array( 'headers' => array(), 'cookies' => array() );
			$theBody = '';
		}

		$response = array();
		$response['code'] = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
		$response['message'] = WP_Http::get_status_header_desc($response['code']);

		curl_close( $handle );

		if ( true === $r['decompress'] && true === WP_Http_Encoding::should_decode($theHeaders) )
			$theBody = WP_Http_Encoding::decompress( $theBody );

		return array('headers' => $theHeaders['headers'], 'body' => $theBody, 'response' => $response, 'cookies' => $theHeaders['cookies']);
	}

	/**
	 * Whether this class can be used for retrieving an URL.
	 *
	 * @static
	 * @since 2.7.0
	 *
	 * @return boolean False means this class can not be used, true means it can.
	 */
	function test($args = array()) {
		if ( function_exists('curl_init') && function_exists('curl_exec') )
			return true;

		return false;
	}
}

class WP_Http_Curl_Exception extends WP_Http_Exception {
}

?>
