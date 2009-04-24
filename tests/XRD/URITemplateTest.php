<?php

require_once dirname(dirname(__FILE__)) . '/TestCase.php';
require_once 'XRD/URITemplate.php';
 
class XRD_URITemplateTest extends Discovery_TestCase {

	public function testTemplate() {
		$resource = 'http://example.com/r/1?f=xml#top';

		$template = new XRD_URITemplate('http://example.org?q={%uri}');
		$this->assertEquals('http://example.org?q=http%3A%2F%2Fexample.com%2Fr%2F1%3Ff%3Dxml', $template->applyTemplate($resource));

		$template = new XRD_URITemplate('http://meta.{host}:8080{path}?{query}');
		$this->assertEquals('http://meta.example.com:8080/r/1?f=xml', $template->applyTemplate($resource));

		$template = new XRD_URITemplate('https://{authority}/v1{path}#{fragment}');
		$this->assertEquals('https://example.com/v1/r/1#top', $template->applyTemplate($resource));
	}

}

?>
