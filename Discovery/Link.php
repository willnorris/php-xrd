<?php

class Discovery_Link {

	public $uri;

	public $rel;

	public $type;

	public function __construct($uri, $rel, $type) {
		$this->uri = $uri;
		$this->rel = (array) $rel;
		$this->type = $type;
	}
}

?>
