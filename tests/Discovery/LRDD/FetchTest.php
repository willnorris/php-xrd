<?php

require_once dirname(dirname(dirname(__FILE__))) . '/TestCase.php';

require_once 'Discovery/LRDD.php';
require_once 'Discovery/LRDD/Method/Host_Meta.php';
require_once 'Discovery/LRDD/Method/Link_Header.php';
require_once 'Discovery/LRDD/Method/Link_HTML.php';
 
class Discovery_LRDD_FetchTest extends Discovery_TestCase {

	public function testLinkHTML() {
		$url = 'http://test.openxrd.org/html/simple';

		$disco = new Discovery_LRDD();
		$disco->discovery_methods = array();
		$disco->register_discovery_method('Discovery_LRDD_Method_Link_HTML');

		$links = $disco->discover($url);
		$this->assertEquals(2, sizeof($links));
		$this->assertEquals('http://test.openxrd.org/html/simple;about', $links[0]->uri);
		$this->assertEquals('http://test.openxrd.org/html/simple;powder', $links[1]->uri);
	}

	public function testLinkHeader() {
		$url = 'http://test.openxrd.org/header/simple';

		$disco = new Discovery_LRDD();
		$disco->discovery_methods = array();
		$disco->register_discovery_method('Discovery_LRDD_Method_Link_Header');

		$links = $disco->discover($url);
		$this->assertEquals(1, sizeof($links));
		$this->assertEquals('http://test.openxrd.org/header/simple;about', $links[0]->uri);
	}

	public function testHostMeta() {
		$url = 'http://test.openxrd.org/header/simple';

		$disco = new Discovery_LRDD();
		$disco->discovery_methods = array();
		$disco->register_discovery_method('Discovery_LRDD_Method_Host_Meta');

		$links = $disco->discover($url);

		$this->assertEquals(2, sizeof($links));
		$this->assertEquals('{uri};about', $links[0]->uri);
		$this->assertEquals('http://test.openxrd.org/header/simple;about', $links[0]->applyPattern($url));
		$this->assertEquals('{uri};powder', $links[1]->uri);
	}

}

?>
