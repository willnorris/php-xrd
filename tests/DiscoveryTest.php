<?php

require_once 'PHPUnit/Framework.php';
require_once 'XRDS.php';
require_once 'XRDS/Discovery.php';
 
class DiscoveryTest extends PHPUnit_Framework_TestCase {

	var $data_dir;

	public function __construct() {
		$this->data_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
	}

	public function testContentNegotiation() {
		$disco = new XRDS_Discovery();
		$disco->discovery_methods = array('XRDS_Discovery_Content_Negotiation');
		$xrds = $disco->discover('http://willnorris.myvidoop.com/');

		$this->assertNotNull($xrds);
	}

	public function testLocationHeader() {
		$disco = new XRDS_Discovery();
		$disco->discovery_methods = array('XRDS_Discovery_Location_Header');
		$xrds = $disco->discover('http://willnorris.myvidoop.com/');

		$this->assertNotNull($xrds);
	}

	public function testHtmlMeta() {
		$disco = new XRDS_Discovery();
		$disco->discovery_methods = array('XRDS_Discovery_HTML_Meta');
		$xrds = $disco->discover('http://willnorris.myvidoop.com/');

		$this->assertNotNull($xrds);
	}
}

?>
