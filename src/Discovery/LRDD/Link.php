<?php

require_once 'Discovery/Util.php';

class Discovery_LRDD_Link {

	public $uri;

	public $rel;

	public $type;

	public function __construct($uri, $rel, $type) {
		$this->uri = $uri;
		$this->rel = (array) $rel;
		$this->type = $type;
	}

	public static function from_header($header) {
		// link uri is always first
		preg_match('/^<[^>]+>/', $header, $uri_reference);
		if (empty($uri_reference)) return;

		$link_uri = trim($uri_reference[0], '<>');
		$link_rel = array();
		$link_type = null;

		// remove uri-reference from header
		$header = substr($header, strlen($uri_reference[0]));

		// parse link-params
		$params = Discovery_Util::split($header, ';');

		foreach ($params as $param) {
			if (empty($param)) continue;
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
