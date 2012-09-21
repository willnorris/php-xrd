<?php

require_once 'Discovery/HTTP/Adapter.php';
require_once 'Discovery/HTTP/WP_Http_org.php';

class Discovery_HTTP_WP extends WP_Http implements Discovery_HTTP_Adapter {

	public function fetch($request) {
		return self::request($request['uri'], $request);
	}

}

?>
