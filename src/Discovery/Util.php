<?php

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
}

?>
