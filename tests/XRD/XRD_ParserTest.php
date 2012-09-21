<?php

require_once dirname(dirname(__FILE__)) . '/Discovery_TestCase.php';
require_once 'XRD.php';
 
class XRD_ParserTest extends Discovery_TestCase {

	public function testParsing() {
		$file = $this->data_dir . 'example.xml';
		$xrd = XRD::load($file);

		$this->assertEquals(1, sizeof($xrd->subject));
		$this->assertEquals(2, sizeof($xrd->link));

		$xml = $xrd->to_xml(true);
		$xrd2 = XRD::loadXML($xml);
		$this->assertEquals($xrd, $xrd2);
	}
}

?>