<?php

require_once dirname(__FILE__) . '/TestCase.php';
require_once 'Discovery/LRDD/Method/Host_Meta.php';
require_once 'Discovery/LRDD/Method/Link_Header.php';
require_once 'Discovery/LRDD/Method/Link_HTML.php';
 
class LRDDTest extends Discovery_TestCase {

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

		$this->assertEquals('http://openxrd.org/sitemap.xml', $links[0]->uri);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('index', $links[0]->rel[0]);

		$this->assertEquals('http://openxrd.org/xrd.xml', $links[1]->uri);
		$this->assertEquals('application/xrd+xml', $links[1]->type);
		$this->assertEquals(3, sizeof($links[1]->rel));
		$this->assertTrue(in_array('describedby', $links[1]->rel));
		$this->assertTrue(in_array('alternate', $links[1]->rel));
		$this->assertTrue(in_array('http://example.com/custom/rel', $links[1]->rel));
	}

	public function testLinkHeader() {
		// test 1
		$content = file_get_contents($this->data_dir . 'http-headers-1.txt');
		$links = Discovery_LRDD_Method_Link_Header::parse($content);

		$this->assertEquals(1, sizeof($links));
		$this->assertEquals('http://openxrd.org/xrd.xml', $links[0]->uri);
		$this->assertEquals('application/xrd+xml', $links[0]->type);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('describedby', $links[0]->rel[0]);

		// test 2
		$content = file_get_contents($this->data_dir . 'http-headers-2.txt');
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

		$this->assertEquals('http://openxrd.org/sitemap.xml', $links[0]->uri);
		$this->assertEquals('application/xml', $links[0]->type);
		$this->assertEquals(1, sizeof($links[0]->rel));
		$this->assertEquals('index', $links[0]->rel[0]);

		$this->assertEquals('http://openxrd.org/xrd.xml', $links[1]->uri);
		$this->assertEquals('application/xrd+xml', $links[1]->type);
		$this->assertEquals(2, sizeof($links[1]->rel));
		$this->assertTrue(in_array('describedby', $links[1]->rel));
		$this->assertTrue(in_array('alternate', $links[1]->rel));
	}

	public function testFetch() {
		$url = 'http://openxrd.org/';

		// test 1 - HTML only
		$disco = new Discovery_LRDD();
		$disco->discovery_methods = array();
		$disco->register_discovery_method('Discovery_LRDD_Method_Link_HTML');

		$links = $disco->discover($url);
		$this->assertEquals(2, sizeof($links));
		$this->assertEquals('text/css', $links[1]->type);

		// test 2 - HTML + HTTP Header
		$disco->discovery_methods = array();
		$disco->register_discovery_method('Discovery_LRDD_Method_Link_Header');
		$disco->register_discovery_method('Discovery_LRDD_Method_Link_HTML');

		$links = $disco->discover($url);
		$this->assertEquals(2, sizeof($links));
		$this->assertEquals('http://openxrd.org/xrd', $links[0]->uri);
		$this->assertEquals('http://openxrd.org/powder', $links[1]->uri);

		// test 3 - all discovery methods
		$disco = new Discovery_LRDD();
		$links = $disco->discover($url);
		$this->assertEquals(2, sizeof($links));
		$this->assertEquals('http://openxrd.org/xrd.xml', $links[0]->uri);
		$this->assertEquals('http://openxrd.org/powder.xml', $links[1]->uri);
	}
}


?>
