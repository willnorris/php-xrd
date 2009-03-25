<?php

require_once dirname(__FILE__) . '/TestCase.php';
require_once 'Discovery/LRDD.php';
require_once 'Discovery/LRDD/Method/Host_Meta.php';
require_once 'Discovery/LRDD/Method/Link_Header.php';
require_once 'Discovery/LRDD/Method/Link_HTML.php';
 
class LRDDParseTest extends Discovery_TestCase {


	public function testLinkHTML() {
		// test 1
		$content = file_get_contents($this->data_dir . 'link-1.html');
		$links = Discovery_LRDD_Method_Link_HTML::parse($content);

		$this->assertEquals(1, sizeof($links));
		$this->assertEquals('http://openxrd.org/xrd.xml', $links[0]->uri);
		$this->assertEquals('application/xrd+xml', $links[0]->type);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('describedby', $links[0]->rel[0]);

		// test 2
		$content = file_get_contents($this->data_dir . 'link-2.html');
		$links = Discovery_LRDD_Method_Link_HTML::parse($content);

		$this->assertEquals(2, sizeof($links));

		$this->assertEquals('http://openxrd.org/powder.xml', $links[0]->uri);
		$this->assertEquals('application/poweder+xml', $links[0]->type);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('describedby', $links[0]->rel[0]);

		$this->assertEquals('http://openxrd.org/xrd.xml', $links[1]->uri);
		$this->assertEquals('application/xrd+xml', $links[1]->type);
		$this->assertEquals(2, sizeof($links[1]->rel));
		$this->assertTrue(in_array('describedby', $links[1]->rel));
		$this->assertTrue(in_array('alternate', $links[1]->rel));
	}


	public function testLinkHeader() {
		// test 1
		$content = '<http://openxrd.org/xrd.xml>; rel="describedby"; type="application/xrd+xml"';
		$links = Discovery_LRDD_Method_Link_Header::parse($content);

		$this->assertEquals(1, sizeof($links));
		$this->assertEquals('http://openxrd.org/xrd.xml', $links[0]->uri);
		$this->assertEquals('application/xrd+xml', $links[0]->type);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('describedby', $links[0]->rel[0]);

		// test 2
		$content = array(
			'<http://openxrd.org/powder.xml>; rel=describedby; type="application/powder+xml"',
			'<http://openxrd.org/xrd.xml>; rel=describedby; rel="alternate http://example.com/custom/rel"; type="application/xrd+xml"'
		);
		$links = Discovery_LRDD_Method_Link_Header::parse($content);

		$this->assertEquals(2, sizeof($links));

		$this->assertEquals('http://openxrd.org/powder.xml', $links[0]->uri);
		$this->assertEquals('application/powder+xml', $links[0]->type);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('describedby', $links[0]->rel[0]);

		$this->assertEquals('http://openxrd.org/xrd.xml', $links[1]->uri);
		$this->assertEquals('application/xrd+xml', $links[1]->type);
		$this->assertEquals(3, sizeof($links[1]->rel));
		$this->assertTrue(in_array('describedby', $links[1]->rel));
		$this->assertTrue(in_array('alternate', $links[1]->rel));
		$this->assertTrue(in_array('http://example.com/custom/rel', $links[1]->rel));
	}


	public function testHostMeta() {
		// test 1
		$content = file_get_contents($this->data_dir . 'host-meta-1');
		$links = Discovery_LRDD_Method_Host_Meta::parse($content);

		$this->assertEquals(1, sizeof($links));
		$this->assertEquals('http://openxrd.org/xrd.xml', $links[0]->uri);
		$this->assertEquals('application/xrd+xml', $links[0]->type);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('describedby', $links[0]->rel[0]);

		// test 2
		$content = file_get_contents($this->data_dir . 'host-meta-2');
		$links = Discovery_LRDD_Method_Host_Meta::parse($content);

		$this->assertEquals(2, sizeof($links));

		$this->assertEquals('http://openxrd.org/powder.xml', $links[0]->uri);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('describedby', $links[0]->rel[0]);

		$this->assertEquals('http://openxrd.org/xrd.xml', $links[1]->uri);
		$this->assertEquals('application/xrd+xml', $links[1]->type);
		$this->assertEquals(3, sizeof($links[1]->rel));
		$this->assertTrue(in_array('describedby', $links[1]->rel));
		$this->assertTrue(in_array('alternate', $links[1]->rel));
		$this->assertTrue(in_array('http://example.com/custom/rel', $links[1]->rel));
	}

}


?>
