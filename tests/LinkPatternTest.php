<?php

require_once dirname(__FILE__) . '/TestCase.php';
require_once 'LRDD/LinkPattern.php';
 
class LinkPatternTest extends Discovery_TestCase {

	public function testComponentParsing() {
		$resource = 'foo://william@example.com:8080/over/there?name=ferret#nose';
		$components = LRDD_LinkPattern::getComponents($resource);

		$this->assertEquals(9, sizeof($components));
		$this->assertEquals('foo', $components['scheme']);
		$this->assertEquals('william@example.com:8080', $components['authority']);
		$this->assertEquals('/over/there', $components['path']);
		$this->assertEquals('name=ferret', $components['query']);
		$this->assertEquals('nose', $components['fragment']);
		$this->assertEquals('william', $components['userinfo']);
		$this->assertEquals('example.com', $components['host']);
		$this->assertEquals('8080', $components['port']);
		$this->assertEquals('foo://william@example.com:8080/over/there?name=ferret', $components['uri']);
	}

	public function testPattern() {
		$resource = 'http://example.com/r/1?f=xml#top';

		$template = new LRDD_LinkPattern('http://example.org?q={%uri}');
		$this->assertEquals('http://example.org?q=http%3A%2F%2Fexample.com%2Fr%2F1%3Ff%3Dxml', $template->applyPattern($resource));

		$template = new LRDD_LinkPattern('http://meta.{host}:8080{path}?{query}');
		$this->assertEquals('http://meta.example.com:8080/r/1?f=xml', $template->applyPattern($resource));

		$template = new LRDD_LinkPattern('https://{authority}/v1{path}#{fragment}');
		$this->assertEquals('https://example.com/v1/r/1#top', $template->applyPattern($resource));
	}

}

?>
