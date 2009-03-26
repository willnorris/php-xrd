<?php

/**
 * Discovery Utilities
 *
 * @package Discovery
 */
class Discovery_Util {
	/**
	 * Split a combined HTTP header into its separate values.  Multiple values 
	 * for an HTTP header can be combined on a single line, and separated by a 
	 * comma.  Commas may also appear within header values themselves, but only 
	 * within a quoted string, or if escaped with a single backslash "\".  
	 * Empty values within a comma separted list of values will be removed.
	 *
	 * @param string $input string value to split
	 * @return array array of separated values
	 */
	public static function split($input, $delimiter = ',') {
		$values = array();

		$characters = str_split($input);
		$value = '';

		// track when we're in a quoted string
		$quote = false;

		// track when characters are escaped
		$escape = false;

		foreach ($characters as $c) {
			if ($c == $delimiter && !$quote && !$escape) {
				$value = trim($value);
				if (!empty($value)) $values[] = $value;
				$value = '';
				continue;
			}

			if ($c == '\\' && !$escape) {
				$escape = true;
			} else {
				$escape = false;
			}

			if ($c == '"') $quote = !$quote;

			$value .= $c;
		}
		$value = trim($value);
		if (!empty($value)) $values[] = $value;

		return $values;
	}


	/**
	 * Get appropriate HTTP adapter based on what libraries are available.
	 *
	 * @return Discovery_HTTP_Adapter
	 */
	public function httpAdapter() {
		static $http;

		if ( isset($http) ) return $http;

		// WP_Http
		if ( class_exists('WP_Http') ) {
			require_once 'Discovery/HTTP/WP_Http.php';
			return $http = new Discovery_HTTP_WP();
		}

		// Zend_HTTP
		@include_once 'Zend/Http/Client.php';
		if ( class_exists('Zend_Http_Client') ) {
			require_once 'Discovery/HTTP/Zend.php';
			return $http = new Discovery_HTTP_Zend();
		}

		// PEAR HTTP_Request2
		@include_once 'HTTP/Request2.php';
		if ( class_exists('HTTP_Request2') ) {
			require_once 'Discovery/HTTP/Pear.php';
			return $http = new Discovery_HTTP_Pear();
		}
	}

}

?>
