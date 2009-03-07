<?php

class LRDD_Link {

	public $uri;

	public $rel;

	public $type;

	public function __construct($uri, $rel, $type) {
		$this->uri = $uri;
		$this->rel = (array) $rel;
		$this->type = $type;
	}

	public static function from_header($header) {
		$params = explode(';', $header);
		if (sizeof($params) == 0) return;

		// link uri is always first
		$link_uri = array_shift($params);
		$link_uri = preg_replace('(^<|>$)', '', trim($link_uri));

		$link_rel = array();
		$link_type = null;

		// parse remaining link-params
		foreach ($params as $param) {
			list($param_name, $param_value) = explode('=', $param, 2);
			$param_name = trim($param_name);
			$param_value = preg_replace('(^"|"$)', '', trim($param_value));

			// for now we only care about 'rel' and 'type' link params
			// TODO do something with the other links-params
			switch ($param_name) {
				case 'rel':
					$rel_values = preg_split('/\s+/', $param_value);
					$link_rel = array_merge($link_rel, $rel_values);
					break;

				case 'type':
					$link_type = trim($param_value);
			}
		}

		return new self($link_uri, $link_rel, $link_type);
	}

}

?>
