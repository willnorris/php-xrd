<?php

require_once dirname(__FILE__) . '/TestCase.php';
require_once 'XRD.php';
 
class XRDTest extends Discovery_TestCase {

	public function testXRD() {
		$xrd = XRD::discover('http://openxrd.org/');

		$this->assertNotNull($xrd);
		$this->assertEquals('http://openxrd.org', $xrd->subject);
		$this->assertEquals(2, sizeof($xrd->link));

		$this->assertEquals(10, $xrd->link[0]->priority);
		$this->assertEquals(1, sizeof($xrd->link[0]->rel));
		$this->assertEquals('http://specs.openid.net/relation/provider', $xrd->link[0]->rel[0]);
		$this->assertEquals(1, sizeof($xrd->link[0]->uri));
		$this->assertEquals('http://openid.yahoo.com', (string) $xrd->link[0]->uri[0]);

		$this->assertEquals(20, $xrd->link[1]->priority);
		$this->assertEquals(1, sizeof($xrd->link[1]->rel));
		$this->assertEquals('http://portablecontacts.net/relation/provider', $xrd->link[1]->rel[0]);
		$this->assertEquals(2, sizeof($xrd->link[1]->uri));
		$this->assertEquals('http://pulse.plaxo.com/pulse/pdata/contacts', (string) $xrd->link[1]->uri[0]);
		$this->assertEquals('http://www.google.com/m8/feeds/contacts/default/full', (string) $xrd->link[1]->uri[1]);
	}
}


?>
