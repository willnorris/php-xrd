<?php

/**
 * Adds Proxy support to the WordPress HTTP API.
 *
 * There are caveats to proxy support. It requires that defines be made in the wp-config.php file to
 * enable proxy support. There are also a few filters that plugins can hook into for some of the
 * constants.
 *
 * The constants are as follows:
 * <ol>
 * <li>WP_PROXY_HOST - Enable proxy support and host for connecting.</li>
 * <li>WP_PROXY_PORT - Proxy port for connection. No default, must be defined.</li>
 * <li>WP_PROXY_USERNAME - Proxy username, if it requires authentication.</li>
 * <li>WP_PROXY_PASSWORD - Proxy password, if it requires authentication.</li>
 * <li>WP_PROXY_BYPASS_HOSTS - Will prevent the hosts in this list from going through the proxy.
 * You do not need to have localhost and the blog host in this list, because they will not be passed
 * through the proxy.</li>
 * </ol>
 *
 * An example can be as seen below.
 * <code>
 * define('WP_PROXY_HOST', '192.168.84.101');
 * define('WP_PROXY_PORT', '8080');
 * define('WP_PROXY_BYPASS_HOSTS', array('localhost', 'www.example.com'));
 * </code>
 *
 * @link http://core.trac.wordpress.org/ticket/4011 Proxy support ticket in WordPress.
 * @since 2.8
 */
class WP_HTTP_Proxy {

	function WP_HTTP_Proxy() {
		$this->__construct();
	}

	function __construct() {

	}

	/**
	 * Whether proxy connection should be used.
	 *
	 * @since 2.8
	 * @use WP_PROXY_HOST
	 * @use WP_PROXY_PORT
	 *
	 * @return bool
	 */
	function is_enabled() {
		return ( defined('WP_PROXY_HOST') && defined('WP_PROXY_PORT') );
	}

	/**
	 * Whether authentication should be used.
	 *
	 * @since 2.8
	 * @use WP_PROXY_USERNAME
	 * @use WP_PROXY_PASSWORD
	 *
	 * @return bool
	 */
	function use_authentication() {
		return ( defined('WP_PROXY_USERNAME') && defined('WP_PROXY_PASSWORD') );
	}

	/**
	 * Retrieve the host for the proxy server.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function host() {
		if( defined('WP_PROXY_HOST') )
			return WP_PROXY_HOST;

		return '';
	}

	/**
	 * Retrieve the port for the proxy server.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function port() {
		if( defined('WP_PROXY_PORT') )
			return WP_PROXY_PORT;

		return '';
	}

	/**
	 * Retrieve the username for proxy authentication.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function username() {
		if( defined('WP_PROXY_USERNAME') )
			return WP_PROXY_USERNAME;

		return '';
	}

	/**
	 * Retrieve the password for proxy authentication.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function password() {
		if( defined('WP_PROXY_PASSWORD') )
			return WP_PROXY_PASSWORD;

		return '';
	}

	/**
	 * Retrieve authentication string for proxy authentication.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function authentication() {
		return $this->username() .':'. $this->password();
	}

	/**
	 * Retrieve header string for proxy authentication.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function authentication_header() {
		return 'Proxy-Authentication: Basic '. base64_encode( $this->authentication() );
	}

	/**
	 * Whether URL should be sent through the proxy server.
	 *
	 * We want to keep localhost and the blog URL from being sent through the proxy server, because
	 * some proxies can not handle this. We also have the constant available for defining other
	 * hosts that won't be sent through the proxy.
	 *
	 * @uses WP_PROXY_BYPASS_HOSTS
	 * @since unknown
	 *
	 * @param string $uri URI to check.
	 * @return bool True, to send through the proxy and false if, the proxy should not be used.
	 */
	function send_through_proxy( $uri ) {
		// parse_url() only handles http, https type URLs, and will emit E_WARNING on failure.
		// This will be displayed on blogs, which is not reasonable.
		$check = @parse_url($uri);

		// Malformed URL, can not process, but this could mean ssl, so let through anyway.
		if( $check === false )
			return true;

		if ( $uri == 'localhost' )
			return false;

		if ( defined('WP_PROXY_BYPASS_HOSTS') && is_array( WP_PROXY_BYPASS_HOSTS ) && in_array( $check['host'], WP_PROXY_BYPASS_HOSTS ) ) {
			return false;
		}

		return true;
	}
}

?>
