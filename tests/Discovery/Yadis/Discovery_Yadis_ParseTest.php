<?php

require_once dirname(dirname(dirname(__FILE__))) . '/Discovery_TestCase.php';

require_once 'Discovery/Yadis.php';
require_once 'Discovery/Yadis/Methods.php';
 
class Discovery_Yadis_ParseTest extends Discovery_TestCase {


	public function testHTMLMeta() {
		// test 1
		$content = file_get_contents($this->data_dir . 'yadis/meta-1.html');
		$meta = Discovery_Yadis_HTML_Meta::parse($content);
		$this->assertEquals('http://openxrd.org/xrds.xml', $meta);

		// test 2
		$content = file_get_contents($this->data_dir . 'yadis/meta-2.html');
		$meta = Discovery_Yadis_HTML_Meta::parse($content);
		$this->assertEquals('http://openxrd.org/xrds.xml', $meta);
	}


	public function testHeader() {
		// no parsing to test
	}


	public function testContentNegotiation() {
		// no parsing to test
	}

}


?>
