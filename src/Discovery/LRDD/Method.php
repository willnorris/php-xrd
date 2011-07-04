<?php

/**
 * An LRDD Method implements one way of discovering the metadata for a URI.
 *
 * @package Discovery
 */
interface Discovery_LRDD_Method 
{
	public static function discover(Discovery_Context $context);
}

?>
