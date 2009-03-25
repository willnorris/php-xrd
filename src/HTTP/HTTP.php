<?php

require_once dirname(__FILE__) . '/Compress.php';
require_once dirname(__FILE__) . '/Cookie.php';
require_once dirname(__FILE__) . '/Proxy.php';
require_once dirname(__FILE__) . '/Transport/Curl.php';
require_once dirname(__FILE__) . '/Transport/ExtHTTP.php';
require_once dirname(__FILE__) . '/Transport/Fopen.php';
require_once dirname(__FILE__) . '/Transport/Socket.php';
require_once dirname(__FILE__) . '/Transport/Streams.php';


/**
 * Simple and uniform HTTP request API.
 *
 * Will eventually replace and standardize the WordPress HTTP requests made.
 *
 * @link http://trac.wordpress.org/ticket/4779 HTTP API Proposal
 *
 * @package WordPress
 * @subpackage HTTP
 * @since 2.7.0
 * @author Jacob Santos <wordpress@santosj.name>
 */

/**
 * WordPress HTTP Class for managing HTTP Transports and making HTTP requests.
 *
 * This class is called for the functionality of making HTTP requests and should replace Snoopy
 * functionality, eventually. There is no available functionality to add HTTP transport
 * implementations, since most of the HTTP transports are added and available for use.
 *
 * The exception is that cURL is not available as a transport and lacking an implementation. It will
 * be added later and should be a patch on the WordPress Trac.
 *
 * There are no properties, because none are needed and for performance reasons. Some of the
 * functions are static and while they do have some overhead over functions in PHP4, the purpose is
 * maintainability. When PHP5 is finally the requirement, it will be easy to add the static keyword
 * to the code. It is not as easy to convert a function to a method after enough code uses the old
 * way.
 *
 * Debugging includes several actions, which pass different variables for debugging the HTTP API.
 *
 * <strong>http_transport_get_debug</strong> - gives working, nonblocking, and blocking transports.
 *
 * <strong>http_transport_post_debug</strong> - gives working, nonblocking, and blocking transports.
 *
 * @package WordPress
 * @subpackage HTTP
 * @since 2.7.0
 */
class WP_Http {

	/**
	 * PHP4 style Constructor - Calls PHP5 Style Constructor
	 *
	 * @since 2.7.0
	 * @return WP_Http
	 */
	function WP_Http() {
		$this->__construct();
	}

	/**
	 * PHP5 style Constructor - Setup available transport if not available.
	 *
	 * PHP4 does not have the 'self' keyword and since WordPress supports PHP4,
	 * the class needs to be used for the static call.
	 *
	 * The transport are setup to save time. This should only be called once, so
	 * the overhead should be fine.
	 *
	 * @since 2.7.0
	 * @return WP_Http
	 */
	function __construct() {
		WP_Http::_getTransport();
		WP_Http::_postTransport();
	}

	/**
	 * Tests the WordPress HTTP objects for an object to use and returns it.
	 *
	 * Tests all of the objects and returns the object that passes. Also caches
	 * that object to be used later.
	 *
	 * The order for the GET/HEAD requests are Streams, HTTP Extension, Fopen,
	 * and finally Fsockopen. fsockopen() is used last, because it has the most
	 * overhead in its implementation. There isn't any real way around it, since
	 * redirects have to be supported, much the same way the other transports
	 * also handle redirects.
	 *
	 * There are currently issues with "localhost" not resolving correctly with
	 * DNS. This may cause an error "failed to open stream: A connection attempt
	 * failed because the connected party did not properly respond after a
	 * period of time, or established connection failed because connected host
	 * has failed to respond."
	 *
	 * @since 2.7.0
	 * @access private
	 *
	 * @param array $args Request args, default us an empty array
	 * @return object|null Null if no transports are available, HTTP transport object.
	 */
	function &_getTransport( $args = array() ) {
		static $working_transport, $blocking_transport, $nonblocking_transport;

		if ( is_null($working_transport) ) {
			if ( true === WP_Http_ExtHttp::test($args) ) {
				$working_transport['exthttp'] = new WP_Http_ExtHttp();
				$blocking_transport[] = &$working_transport['exthttp'];
			} else if ( true === WP_Http_Curl::test($args) ) {
				$working_transport['curl'] = new WP_Http_Curl();
				$blocking_transport[] = &$working_transport['curl'];
			} else if ( true === WP_Http_Streams::test($args) ) {
				$working_transport['streams'] = new WP_Http_Streams();
				$blocking_transport[] = &$working_transport['streams'];
			} else if ( true === WP_Http_Fopen::test($args) ) {
				$working_transport['fopen'] = new WP_Http_Fopen();
				$blocking_transport[] = &$working_transport['fopen'];
			} else if ( true === WP_Http_Fsockopen::test($args) ) {
				$working_transport['fsockopen'] = new WP_Http_Fsockopen();
				$blocking_transport[] = &$working_transport['fsockopen'];
			}

			foreach ( array('curl', 'streams', 'fopen', 'fsockopen', 'exthttp') as $transport ) {
				if ( isset($working_transport[$transport]) )
					$nonblocking_transport[] = &$working_transport[$transport];
			}
		}

		if ( isset($args['blocking']) && !$args['blocking'] )
			return $nonblocking_transport;
		else
			return $blocking_transport;
	}

	/**
	 * Tests the WordPress HTTP objects for an object to use and returns it.
	 *
	 * Tests all of the objects and returns the object that passes. Also caches
	 * that object to be used later. This is for posting content to a URL and
	 * is used when there is a body. The plain Fopen Transport can not be used
	 * to send content, but the streams transport can. This is a limitation that
	 * is addressed here, by just not including that transport.
	 *
	 * @since 2.7.0
	 * @access private
	 *
	 * @param array $args Request args, default us an empty array
	 * @return object|null Null if no transports are available, HTTP transport object.
	 */
	function &_postTransport( $args = array() ) {
		static $working_transport, $blocking_transport, $nonblocking_transport;

		if ( is_null($working_transport) ) {
			if ( true === WP_Http_ExtHttp::test($args) ) {
				$working_transport['exthttp'] = new WP_Http_ExtHttp();
				$blocking_transport[] = &$working_transport['exthttp'];
			} else if ( true === WP_Http_Curl::test($args) ) {
				$working_transport['curl'] = new WP_Http_Curl();
				$blocking_transport[] = &$working_transport['curl'];
			} else if ( true === WP_Http_Streams::test($args) ) {
				$working_transport['streams'] = new WP_Http_Streams();
				$blocking_transport[] = &$working_transport['streams'];
			} else if ( true === WP_Http_Fsockopen::test($args) ) {
				$working_transport['fsockopen'] = new WP_Http_Fsockopen();
				$blocking_transport[] = &$working_transport['fsockopen'];
			}

			foreach ( array('curl', 'streams', 'fsockopen', 'exthttp') as $transport ) {
				if ( isset($working_transport[$transport]) )
					$nonblocking_transport[] = &$working_transport[$transport];
			}
		}

		if ( isset($args['blocking']) && !$args['blocking'] )
			return $nonblocking_transport;
		else
			return $blocking_transport;
	}

	/**
	 * Send a HTTP request to a URI.
	 *
	 * The body and headers are part of the arguments. The 'body' argument is for the body and will
	 * accept either a string or an array. The 'headers' argument should be an array, but a string
	 * is acceptable. If the 'body' argument is an array, then it will automatically be escaped
	 * using http_build_query().
	 *
	 * The only URI that are supported in the HTTP Transport implementation are the HTTP and HTTPS
	 * protocols. HTTP and HTTPS are assumed so the server might not know how to handle the send
	 * headers. Other protocols are unsupported and most likely will fail.
	 *
	 * The defaults are 'method', 'timeout', 'redirection', 'httpversion', 'blocking' and
	 * 'user-agent'.
	 *
	 * Accepted 'method' values are 'GET', 'POST', and 'HEAD', some transports technically allow
	 * others, but should not be assumed. The 'timeout' is used to sent how long the connection
	 * should stay open before failing when no response. 'redirection' is used to track how many
	 * redirects were taken and used to sent the amount for other transports, but not all transports
	 * accept setting that value.
	 *
	 * The 'httpversion' option is used to sent the HTTP version and accepted values are '1.0', and
	 * '1.1' and should be a string. Version 1.1 is not supported, because of chunk response. The
	 * 'user-agent' option is the user-agent and is used to replace the default user-agent, which is
	 * 'WordPress/WP_Version', where WP_Version is the value from $wp_version.
	 *
	 * 'blocking' is the default, which is used to tell the transport, whether it should halt PHP
	 * while it performs the request or continue regardless. Actually, that isn't entirely correct.
	 * Blocking mode really just means whether the fread should just pull what it can whenever it
	 * gets bytes or if it should wait until it has enough in the buffer to read or finishes reading
	 * the entire content. It doesn't actually always mean that PHP will continue going after making
	 * the request.
	 *
	 * @access public
	 * @since 2.7.0
	 *
	 * @param string $url URI resource.
	 * @param str|array $args Optional. Override the defaults.
	 * @return array containing 'headers', 'body', 'response', 'cookies'
	 */
	function request( $url, $args = array() ) {
		$defaults = array(
			'method' => 'GET',
			'timeout' => 5,
			'redirection' => 5,
			'httpversion' => '1.0',
			'user-agent' => 'WP_Http',
			'blocking' => true,
			'headers' => array(),
			'cookies' => array(),
			'body' => null,
			'compress' => false,
			'decompress' => true,
			'sslverify' => true
		);

		$r = array_merge( $defaults, $args );

		$arrURL = parse_url($url);

		// Determine if this is a https call and pass that on to the transport functions
		// so that we can blacklist the transports that do not support ssl verification
		if ( $arrURL['scheme'] == 'https' || $arrURL['scheme'] == 'ssl' )
			$r['ssl'] = true;
		else
			$r['ssl'] = false;

		if ( is_null( $r['headers'] ) )
			$r['headers'] = array();

		if ( ! is_array($r['headers']) ) {
			$processedHeaders = WP_Http::processHeaders($r['headers']);
			$r['headers'] = $processedHeaders['headers'];
		}

		if ( isset($r['headers']['User-Agent']) ) {
			$r['user-agent'] = $r['headers']['User-Agent'];
			unset($r['headers']['User-Agent']);
		}

		if ( isset($r['headers']['user-agent']) ) {
			$r['user-agent'] = $r['headers']['user-agent'];
			unset($r['headers']['user-agent']);
		}

		// Construct Cookie: header if any cookies are set
		WP_Http::buildCookieHeader( $r );

		if( WP_Http_Encoding::is_available() )
			$r['headers']['Accept-Encoding'] = WP_Http_Encoding::accept_encoding();

		if ( is_null($r['body']) ) {
			// Some servers fail when sending content without the content-length
			// header being set.
			$r['headers']['Content-Length'] = 0;
			$transports = WP_Http::_getTransport($r);
		} else {
			if ( is_array( $r['body'] ) || is_object( $r['body'] ) ) {
				$r['body'] = http_build_query($r['body'], null, '&');
				$r['headers']['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
				$r['headers']['Content-Length'] = strlen($r['body']);
			}

			if ( ! isset( $r['headers']['Content-Length'] ) && ! isset( $r['headers']['content-length'] ) )
				$r['headers']['Content-Length'] = strlen($r['body']);

			$transports = WP_Http::_postTransport($r);
		}

		$response = array( 'headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array() );
		foreach( (array) $transports as $transport ) {
			try {
				$response = $transport->request($url, $r);
				return $response;
			} catch ( WP_Http_Exception $exception ) {
				// TODO: do anything?
				throw $exception;
			}
		}

		return $response;
	}

	/**
	 * Uses the POST HTTP method.
	 *
	 * Used for sending data that is expected to be in the body.
	 *
	 * @access public
	 * @since 2.7.0
	 *
	 * @param string $url URI resource.
	 * @param str|array $args Optional. Override the defaults.
	 * @return boolean
	 */
	function post($url, $args = array()) {
		$defaults = array('method' => 'POST');
		$r = array_merge( $defaults, $args );
		return $this->request($url, $r);
	}

	/**
	 * Uses the GET HTTP method.
	 *
	 * Used for sending data that is expected to be in the body.
	 *
	 * @access public
	 * @since 2.7.0
	 *
	 * @param string $url URI resource.
	 * @param str|array $args Optional. Override the defaults.
	 * @return boolean
	 */
	function get($url, $args = array()) {
		$defaults = array('method' => 'GET');
		$r = array_merge( $defaults, $args );
		return $this->request($url, $r);
	}

	/**
	 * Uses the HEAD HTTP method.
	 *
	 * Used for sending data that is expected to be in the body.
	 *
	 * @access public
	 * @since 2.7.0
	 *
	 * @param string $url URI resource.
	 * @param str|array $args Optional. Override the defaults.
	 * @return boolean
	 */
	function head($url, $args = array()) {
		$defaults = array('method' => 'HEAD');
		$r = array_merge( $defaults, $args );
		return $this->request($url, $r);
	}

	/**
	 * Parses the responses and splits the parts into headers and body.
	 *
	 * @access public
	 * @static
	 * @since 2.7.0
	 *
	 * @param string $strResponse The full response string
	 * @return array Array with 'headers' and 'body' keys.
	 */
	function processResponse($strResponse) {
		list($theHeaders, $theBody) = explode("\r\n\r\n", $strResponse, 2);
		return array('headers' => $theHeaders, 'body' => $theBody);
	}

	/**
	 * Transform header string into an array.
	 *
	 * If an array is given then it is assumed to be raw header data with numeric keys with the
	 * headers as the values. No headers must be passed that were already processed.
	 *
	 * @access public
	 * @static
	 * @since 2.7.0
	 *
	 * @param string|array $headers
	 * @return array Processed string headers. If duplicate headers are encountered,
	 * 					Then a numbered array is returned as the value of that header-key.
	 */
	function processHeaders($headers) {
		if ( is_string($headers) )
			$headers = explode("\n", str_replace(array("\r\n", "\r"), "\n", $headers) );

		$response = array('code' => 0, 'message' => '');

		$cookies = array();
		$newheaders = array();

        // first, process the HTTP status line
        if ( false === strpos($headers[0], ':') ) {
            $tempheader = array_shift($headers);
            list( , $iResponseCode, $strResponseMsg) = explode(' ', $tempheader, 3);
            $response['code'] = $iResponseCode;
            $response['message'] = $strResponseMsg;
        }    

        // combine any headers split over multiple lines
        for ( $i=(sizeof($headers)-1); $i>=0; $i-- ) {
            $c = substr($headers[$i], 0, 1);
            if ($c == ' ' || $c == "\t") {
                $headers[$i-1] = rtrim($headers[$i-1]) . ' ' . ltrim($headers[$i]);
                unset($headers[$i]);
            }    
        }    

		foreach ( $headers as $tempheader ) {
			if ( empty($tempheader) )
				continue;

			list($key, $value) = explode(':', $tempheader, 2);

			if ( !empty( $value ) ) {
				$key = strtolower( $key );
				if ( isset( $newheaders[$key] ) ) {
					$newheaders[$key] = array( $newheaders[$key], trim( $value ) );
				} else {
					$newheaders[$key] = trim( $value );
				}
				if ( 'set-cookie' == strtolower( $key ) )
					$cookies[] = new WP_Http_Cookie( $value );
			}
		}

		return array('response' => $response, 'headers' => $newheaders, 'cookies' => $cookies);
	}

	/**
	 * Takes the arguments for a ::request() and checks for the cookie array.
	 *
	 * If it's found, then it's assumed to contain WP_Http_Cookie objects, which are each parsed
	 * into strings and added to the Cookie: header (within the arguments array). Edits the array by
	 * reference.
	 *
	 * @access public
	 * @version 2.8.0
	 * @static
	 *
	 * @param array $r Full array of args passed into ::request()
	 */
	function buildCookieHeader( &$r ) {
		if ( ! empty($r['cookies']) ) {
			$cookies_header = '';
			foreach ( (array) $r['cookies'] as $cookie ) {
				$cookies_header .= $cookie->getHeaderValue() . '; ';
			}
			$cookies_header = substr( $cookies_header, 0, -2 );
			$r['headers']['cookie'] = $cookies_header;
		}
	}

	/**
	 * Decodes chunk transfer-encoding, based off the HTTP 1.1 specification.
	 *
	 * Based off the HTTP http_encoding_dechunk function. Does not support UTF-8. Does not support
	 * returning footer headers. Shouldn't be too difficult to support it though.
	 *
	 * @todo Add support for footer chunked headers.
	 * @access public
	 * @since 2.7.0
	 * @static
	 *
	 * @param string $body Body content
	 * @return string Chunked decoded body on success or raw body on failure.
	 */
	function chunkTransferDecode($body) {
		$body = str_replace(array("\r\n", "\r"), "\n", $body);
		// The body is not chunked encoding or is malformed.
		if ( ! preg_match( '/^[0-9a-f]+(\s|\n)+/mi', trim($body) ) )
			return $body;

		$parsedBody = '';
		//$parsedHeaders = array(); Unsupported

		while ( true ) {
			$hasChunk = (bool) preg_match( '/^([0-9a-f]+)(\s|\n)+/mi', $body, $match );

			if ( $hasChunk ) {
				if ( empty( $match[1] ) )
					return $body;

				$length = hexdec( $match[1] );
				$chunkLength = strlen( $match[0] );

				$strBody = substr($body, $chunkLength, $length);
				$parsedBody .= $strBody;

				$body = ltrim(str_replace(array($match[0], $strBody), '', $body), "\n");

				if( "0" == trim($body) )
					return $parsedBody; // Ignore footer headers.
			} else {
				return $body;
			}
		}
	}

	/**
	 * Retrieve the description for the HTTP status.
	 *
	 * @since 2.3.0
	 *
	 * @param int $code HTTP status code.
	 * @return string Empty string if not found, or description if found.
	 */
	function get_status_header_desc( $code ) {
		static $wp_header_to_desc;

		if ( !isset( $wp_header_to_desc ) ) {
			$wp_header_to_desc = array(
				100 => 'Continue',
				101 => 'Switching Protocols',
				102 => 'Processing',

				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
				207 => 'Multi-Status',
				226 => 'IM Used',

				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found',
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				306 => 'Reserved',
				307 => 'Temporary Redirect',

				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Long',
				415 => 'Unsupported Media Type',
				416 => 'Requested Range Not Satisfiable',
				417 => 'Expectation Failed',
				422 => 'Unprocessable Entity',
				423 => 'Locked',
				424 => 'Failed Dependency',
				426 => 'Upgrade Required',

				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version Not Supported',
				506 => 'Variant Also Negotiates',
				507 => 'Insufficient Storage',
				510 => 'Not Extended'
			);
		}

		if ( isset( $wp_header_to_desc[$code] ) )
			return $wp_header_to_desc[$code];
		else
			return '';
	}

}

class WP_Http_Exception extends Exception {
}

?>
