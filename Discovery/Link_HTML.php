<?php

require_once 'Discovery.php';
require_once 'Discovery/Link.php';
require_once 'Discovery/Method.php';

/**
 * Discovery Method that uses HTML <link> element
 */
class Discovery_Link_HTML implements Discovery_Method {


	public static function discover($uri) {

		$ch = curl_init($uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'discovery/1.0 (php)');

		$content = curl_exec($ch);
		curl_close($ch);

		return self::parse($content);
	}


	/**
	 * Parse the given HTML.
	 *
	 * @param string $content HTML content
	 * @return array array of Discovery_Link objects
	 */
	public static function parse($html) {
		$links = array();

		preg_match('/<head(\s[^>]*)?>(.*?)<\/head>/is', $html, $head_matches);
		$head_html = $head_matches[2];

		preg_match_all('/<link\s[^>]*>/i', $head_html, $link_matches);

		foreach ($link_matches[0] as $link_html) {
			$link_url = null;
			$link_rel = null;
			$link_type = null;

			preg_match('/\shref=(("|\')([^\\2]*?)\\2|[^"\'\s]+)/i', $link_html, $href_matches);
			if ( isset($href_matches[3]) ) {
				$link_uri = $href_matches[3];
			} else if ( isset($href_matches[1]) ) {
				$link_uri = $href_matches[1];
			}

			preg_match('/\srel=(("|\')([^\\2]*?)\\2|[^"\'\s]+)/i', $link_html, $rel_matches);
			if ( isset($rel_matches[3]) ) {
				$link_rel = explode(' ', $rel_matches[3]);
			} else if ( isset($rel_matches[1]) ) {
				$link_rel = explode(' ', $rel_matches[1]);
			}

			preg_match('/\stype=(("|\')([^\\2]*?)\\2|[^"\'\s]+)/i', $link_html, $type_matches);
			if ( isset($type_matches[3]) ) {
				$link_type = $type_matches[3];
			} else if ( isset($type_matches[1]) ) {
				$link_type = $type_matches[1];
			}

			$links[] = new Discovery_Link($link_uri, $link_rel, $link_type);
		}

		return $links;
	}

}

?>
