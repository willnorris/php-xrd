<?php

/**
 * HTTP request method uses HTTP extension to retrieve the url.
 *
 * Requires the HTTP extension to be installed. This would be the preferred transport since it can
 * handle a lot of the problems that forces the others to use the HTTP version 1.0. Even if PHP 5.2+
 * is being used, it doesn't mean that the HTTP extension will be enabled.
 *
 * @package WordPress
 * @subpackage HTTP
 * @since 2.7.0
 */
class WP_Http_ExtHTTP {
	/**
	 * Send a HTTP request to a URI using HTTP extension.
	 *
	 * Does not support non-blocking.
	 *
	 * @access public
	 * @since 2.7
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

		switch ( $r['method'] ) {
			case 'POST':
				$r['method'] = HTTP_METH_POST;
				break;
			case 'HEAD':
				$r['method'] = HTTP_METH_HEAD;
				break;
			case 'GET':
			default:
				$r['method'] = HTTP_METH_GET;
		}

		$arrURL = parse_url($url);

		if ( 'http' != $arrURL['scheme'] || 'https' != $arrURL['scheme'] )
			$url = str_replace($arrURL['scheme'], 'http', $url);

		$options = array(
			'timeout' => $r['timeout'],
			'connecttimeout' => $r['timeout'],
			'redirect' => $r['redirection'],
			'useragent' => $r['user-agent'],
			'headers' => $r['headers'],
			'ssl' => array(
				'verifypeer' => $r['sslverify'],
				'verifyhost' => $r['sslverify']
			)
		);

		// The HTTP extensions offers really easy proxy support.
		$proxy = new WP_HTTP_Proxy();

		if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {
			$options['proxyhost'] = $proxy->host();
			$options['proxyport'] = $proxy->port();
			$options['proxytype'] = HTTP_PROXY_HTTP;

			if ( $proxy->use_authentication() ) {
				$options['proxyauth'] = $proxy->authentication();
				$options['proxyauthtype'] = HTTP_AUTH_BASIC;
			}
		}

		if ( !defined('WP_DEBUG') || ( defined('WP_DEBUG') && false === WP_DEBUG ) ) //Emits warning level notices for max redirects and timeouts
			$strResponse = @http_request($r['method'], $url, $r['body'], $options, $info);
		else
			$strResponse = http_request($r['method'], $url, $r['body'], $options, $info); //Emits warning level notices for max redirects and timeouts

		// Error may still be set, Response may return headers or partial document, and error
		// contains a reason the request was aborted, eg, timeout expired or max-redirects reached.
		if ( false === $strResponse || ! empty($info['error']) )
			throw new WP_Http_ExtHTTP_Exception( $info['response_code'] . ': ' . $info['error'] );

		if ( ! $r['blocking'] )
			return array( 'headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array() );

		list($theHeaders, $theBody) = explode("\r\n\r\n", $strResponse, 2);
		$theHeaders = WP_Http::processHeaders($theHeaders);

		if ( ! empty( $theBody ) && isset( $theHeaders['headers']['transfer-encoding'] ) && 'chunked' == $theHeaders['headers']['transfer-encoding'] ) {
			if ( !defined('WP_DEBUG') || ( defined('WP_DEBUG') && false === WP_DEBUG ) )
				$theBody = @http_chunked_decode($theBody);
			else
				$theBody = http_chunked_decode($theBody);
		}

		if ( true === $r['decompress'] && true === WP_Http_Encoding::should_decode($theHeaders) )
			$theBody = http_inflate( $theBody );

		$theResponse = array();
		$theResponse['code'] = $info['response_code'];
		$theResponse['message'] = WP_Http::get_status_header_desc($info['response_code']);

		return array('headers' => $theHeaders['headers'], 'body' => $theBody, 'response' => $theResponse, 'cookies' => $theHeaders['cookies']);
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
		if ( function_exists('http_request') )
			return true;

		return false;
	}
}

class WP_Http_ExtHTTP_Exception extends WP_Http_Exception {
}

?>
