<?php

require_once 'Discovery/HTTP/Adaptor.php';

class Discovery_HTTP_WP extends WP_Http implements Discovery_HTTP_Adaptor {

	public function fetch($request) {
		return self::request($request['uri'], $request);
	}

}

?>
