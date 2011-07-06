<?php

require_once dirname(dirname(dirname(__FILE__))) . '/Discovery_TestCase.php';

require_once 'Discovery/Yadis.php';
require_once 'Discovery/Yadis/Methods.php';
 
class Discovery_Yadis_FetchTest extends Discovery_TestCase {

	public function testHTMLMeta() {
		$url = 'http://test.openxrd.org/xrds/simple/';

		$disco = new Discovery_Yadis();
		$disco->discovery_methods = array();
		$disco->register_discovery_method('Discovery_Yadis_HTML_Meta');

		$xrds = $disco->discover($url);
		$this->assertNotNull($xrds);
		$this->assertEquals(1, sizeof($xrds->xrd));
		$this->assertEquals(2, sizeof($xrds->xrd[0]->service));
	}


	public function testLocationHeader() {
		$url = 'http://test.openxrd.org/xrds/simple/';

		$disco = new Discovery_Yadis();
		$disco->discovery_methods = array();
		$disco->register_discovery_method('Discovery_Yadis_Location_Header');

		$xrds = $disco->discover($url);
		$this->assertNotNull($xrds);
		$this->assertEquals(1, sizeof($xrds->xrd));
		$this->assertEquals(2, sizeof($xrds->xrd[0]->service));
	}


	public function testContentNegotiation() {
		$url = 'http://test.openxrd.org/xrds/simple/';

		$disco = new Discovery_Yadis();
		$disco->discovery_methods = array();
		$disco->register_discovery_method('Discovery_Yadis_Content_Negotiation');

		$xrds = $disco->discover($url);
		$this->assertNotNull($xrds);
		$this->assertEquals(1, sizeof($xrds->xrd));
		$this->assertEquals(2, sizeof($xrds->xrd[0]->service));
	}

}

?>
