<?php

/**
 * Implementation for deflate and gzip transfer encodings.
 *
 * Includes RFC 1950, RFC 1951, and RFC 1952.
 *
 * @since 2.8
 * @package WordPress
 * @subpackage HTTP
 */
class WP_Http_Encoding {

	/**
	 * Compress raw string using the deflate format.
	 *
	 * Supports the RFC 1951 standard.
	 *
	 * @since 2.8
	 *
	 * @param string $raw String to compress.
	 * @param int $level Optional, default is 9. Compression level, 9 is highest.
	 * @param string $supports Optional, not used. When implemented it will choose the right compression based on what the server supports.
	 * @return string|bool False on failure.
	 */
	function compress( $raw, $level = 9, $supports = null ) {
		return gzdeflate( $raw, $level );
	}

	/**
	 * Decompression of deflated string.
	 *
	 * Will attempt to decompress using the RFC 1950 standard, and if that fails
	 * then the RFC 1951 standard deflate will be attempted. Finally, the RFC
	 * 1952 standard gzip decode will be attempted. If all fail, then the
	 * original compressed string will be returned.
	 *
	 * @since 2.8
	 *
	 * @param string $compressed String to decompress.
	 * @param int $length The optional length of the compressed data.
	 * @return string|bool False on failure.
	 */
	function decompress( $compressed, $length = null ) {
		$decompressed = gzinflate( $compressed );

		if( false !== $decompressed )
			return $decompressed;

		$decompressed = gzuncompress( $compressed );

		if( false !== $decompressed )
			return $decompressed;

		$decompressed = gzdecode( $compressed );

		if( false !== $decompressed )
			return $decompressed;

		return $compressed;
	}

	/**
	 * What encoding types to accept and their priority values.
	 *
	 * @since 2.8
	 *
	 * @return string Types of encoding to accept.
	 */
	function accept_encoding() {
		$type = array();
		if( function_exists( 'gzinflate' ) )
			$type[] = 'deflate;q=1.0';

		if( function_exists( 'gzuncompress' ) )
			$type[] = 'compress;q=0.5';

		if( function_exists( 'gzdecode' ) )
			$type[] = 'gzip;q=0.5';

		return implode(', ', $type);
	}

	/**
	 * What enconding the content used when it was compressed to send in the headers.
	 *
	 * @since 2.8
	 *
	 * @return string Content-Encoding string to send in the header.
	 */
	function content_encoding() {
		return 'deflate';
	}

	/**
	 * Whether the content be decoded based on the headers.
	 *
	 * @since 2.8
	 *
	 * @param array|string $headers All of the available headers.
	 * @return bool
	 */
	function should_decode($headers) {
		if( is_array( $headers ) ) {
			if( array_key_exists('content-encoding', $headers) && ! empty( $headers['content-encoding'] ) )
				return true;
		} else if( is_string( $headers ) ) {
			return ( stripos($headers, 'content-encoding:') !== false );
		}

		return false;
	}

	/**
	 * Whether decompression and compression are supported by the PHP version.
	 *
	 * Each function is tested instead of checking for the zlib extension, to
	 * ensure that the functions all exist in the PHP version and aren't
	 * disabled.
	 *
	 * @since 2.8
	 *
	 * @return bool
	 */
	function is_available() {
		return ( function_exists('gzuncompress') || function_exists('gzdeflate') ||
				 function_exists('gzinflate') );
	}
}

?>
