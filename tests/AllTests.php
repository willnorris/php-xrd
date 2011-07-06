<?php
require_once dirname(__FILE__) . '/Discovery/LRDD/Discovery_LRDD_ParseTest.php';
require_once dirname(__FILE__) . '/Discovery/LRDD/Discovery_LRDD_LinkPatternTest.php';
require_once dirname(__FILE__) . '/Discovery/LRDD/Discovery_LRDD_FetchTest.php';

require_once dirname(__FILE__) . '/Discovery/Yadis/Discovery_Yadis_ParseTest.php';
require_once dirname(__FILE__) . '/Discovery/Yadis/Discovery_Yadis_FetchTest.php';

require_once dirname(__FILE__) . '/XRD/XRD_ParserTest.php';

class AllTests extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
    $suite = new AllTests('Discovery');

		$suite->addTestSuite('Discovery_LRDD_ParseTest');
		$suite->addTestSuite('Discovery_LRDD_LinkPatternTest');
		//$suite->addTestSuite('Discovery_LRDD_FetchTest');

		$suite->addTestSuite('Discovery_Yadis_ParseTest');
		$suite->addTestSuite('Discovery_Yadis_FetchTest');

		$suite->addTestSuite('XRD_ParserTest');

		return $suite;
    }
 
}
?>
