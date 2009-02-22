<?php

require_once 'PHPUnit/Framework.php';
require_once 'XRDS.php';
 
class ParserTest extends PHPUnit_Framework_TestCase {

	var $data_dir;

	public function __construct() {
		$this->data_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
	}

	public function testParsing() {
		$file = $this->data_dir . 'example.xml';
		$xrds = XRDS::load($file);
		print_r($xrds);

		$this->assertEquals(1, sizeof($xrds->xrd));
		$this->assertEquals(2, sizeof($xrds->xrd[0]->service));

		$xml = $xrds->to_xml();
		$xrds2 = XRDS::loadXML($xml);
		$this->assertEquals($xrds, $xrds2);
	}

	public function testParsing2() {
		$file = $this->data_dir . 'vidoop.xml';
		$xrds = XRDS::load($file);
	}

	public function testServiceRetrieval() {
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
