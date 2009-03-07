<?php

/**
 * An LRDD Method implements one way of discovering the metadata for a URI.
 */
interface LRDD_Method 
{
	public static function discover($uri);
}

?>
