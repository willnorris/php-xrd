<?php

require_once 'PHPUnit/Framework.php';
require_once 'XRD.php';
 
class ParserTest extends PHPUnit_Framework_TestCase {

	var $data_dir;

	public function __construct() {
		$this->data_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
	}

	public function testParsing() {
		$file = $this->data_dir . 'example.xml';
		$xrd = XRD::load($file);

		$this->assertEquals(1, sizeof($xrd->type));
		$this->assertEquals(2, sizeof($xrd->link));

		$xml = $xrd->to_xml(true);
		$xrd2 = XRD::loadXML($xml);
		$this->assertEquals($xrd, $xrd2);
	}

	public function testServiceRetrieval() {
		return;
		$file = $this->data_dir . 'example.xml';
		$xrds = XRDS::load($file);

		$uri = $xrds->getServiceURI('http://specs.example.com/wish_list/1.0');
		$this->assertEquals('http://books.example.com/wishlist', $uri->uri);

		$uri = $xrds->getServiceURI('http://specs.example.com/wish_list/2.0');
		$this->assertEquals('https://dvds.example.org/lists/wishes', $uri->uri);

		$uri = $xrds->getServiceURI(array('http://specs.example.com/wish_list/1.0', 'http://specs.example.com/wish_list/2.0'));
		$this->assertEquals('https://dvds.example.org/lists/wishes', $uri->uri);
	}
}

?>
