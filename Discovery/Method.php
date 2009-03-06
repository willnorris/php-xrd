<?php

/**
 * A XRD Discovery Methods implements one way of discovering the metadata for a URI.
 */
interface Discovery_Method 
{
	public static function discover($uri);
}

?>
