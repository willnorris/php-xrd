<?php
require_once dirname(__FILE__) . '/ParserTest.php';
require_once dirname(__FILE__) . '/LRDDTest.php';
require_once dirname(__FILE__) . '/XRDTest.php';
 
class AllTests extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new AllTests('Discovery');

		$suite->addTestSuite('ParserTest');
		$suite->addTestSuite('LRDDTest');
		$suite->addTestSuite('XRDTest');

		return $suite;
    }
 
}
?>
