<?php

require_once dirname(dirname(dirname(__FILE__))) . '/Discovery_TestCase.php';

require_once 'Discovery/LRDD.php';
require_once 'Discovery/LRDD/Method/Host_Meta.php';
require_once 'Discovery/LRDD/Method/Link_Header.php';
require_once 'Discovery/LRDD/Method/Link_HTML.php';
 
class Discovery_LRDD_ParseTest extends Discovery_TestCase {


	public function testLinkHTML() {
		// test 1
		$content = file_get_contents($this->data_dir . 'lrdd/link-1.html');
		$links = Discovery_LRDD_Method_Link_HTML::parse($content);

		$this->assertEquals(1, sizeof($links));
		$this->assertEquals('http://openxrd.org/xrd.xml', $links[0]->uri);
		$this->assertEquals('application/xrd+xml', $links[0]->type);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('lrdd', $links[0]->rel[0]);

		// test 2
		$content = file_get_contents($this->data_dir . 'lrdd/link-2.html');
		$links = Discovery_LRDD_Method_Link_HTML::parse($content);

		$this->assertEquals(2, sizeof($links));

		$this->assertEquals('http://openxrd.org/powder.xml', $links[0]->uri);
		$this->assertEquals('application/poweder+xml', $links[0]->type);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('lrdd', $links[0]->rel[0]);

		$this->assertEquals('http://openxrd.org/xrd.xml', $links[1]->uri);
		$this->assertEquals('application/xrd+xml', $links[1]->type);
		$this->assertEquals(2, sizeof($links[1]->rel));
		$this->assertTrue(in_array('lrdd', $links[1]->rel));
		$this->assertTrue(in_array('alternate', $links[1]->rel));
	}


	public function testLinkHeader() {
		// test 1
		$content = '<http://openxrd.org/xrd.xml>; rel="lrdd"; type="application/xrd+xml"';
		$links = Discovery_LRDD_Method_Link_Header::parse($content);

		$this->assertEquals(1, sizeof($links));
		$this->assertEquals('http://openxrd.org/xrd.xml', $links[0]->uri);
		$this->assertEquals('application/xrd+xml', $links[0]->type);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('lrdd', $links[0]->rel[0]);

		// test 2
		$content = array(
			'<http://openxrd.org/powder.xml>; rel=lrdd; type="application/powder+xml"',
			'<http://openxrd.org/xrd.xml>; rel=lrdd; rel="alternate http://example.com/custom/rel"; type="application/xrd+xml"'
		);
		$links = Discovery_LRDD_Method_Link_Header::parse($content);

		$this->assertEquals(2, sizeof($links));

		$this->assertEquals('http://openxrd.org/powder.xml', $links[0]->uri);
		$this->assertEquals('application/powder+xml', $links[0]->type);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('lrdd', $links[0]->rel[0]);

		$this->assertEquals('http://openxrd.org/xrd.xml', $links[1]->uri);
		$this->assertEquals('application/xrd+xml', $links[1]->type);
		$this->assertEquals(3, sizeof($links[1]->rel));
		$this->assertTrue(in_array('lrdd', $links[1]->rel));
		$this->assertTrue(in_array('alternate', $links[1]->rel));
		$this->assertTrue(in_array('http://example.com/custom/rel', $links[1]->rel));
	}
}


?>