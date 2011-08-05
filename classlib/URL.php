<?php

/**
 * Generic URL class dealing with any URL-handling related functions. ROCKETS_URL does NOT
 * do any header checks like ROCKETS_HTTP. This class only manipulates URL strings.
 */
class ROCKETS_URL {

	/**
	 * Given a series of query string items from $_SERVER Query String, unwanted values are erased.
	 * 
	 * @param <type> $ar - values to erase
	 */
	public static function getQueryStringArray($ar = array(null))
	{
		parse_str($_SERVER['QUERY_STRING'], $output);
		foreach ($ar as $v)
		{ // erase unwanted values
			if (isset($output[$v]))
				$output[$v] = null;
		}
		return $output;
	}

	/**
	 * Private Callback function for URL->build_query, that filters out null values from an array of query strings
	 * 
	 * @param <type> $a
	 * @return <type>
	 */
	private function filterNullValues($a)
	{
		if ($a)
			return $a;
		else
			return null;
	}

	/**
	 * Builds http query, filtering out empty values. Useful for building clean query string urls with no
	 * empty values.
	 * 
	 * @param <type> $ar
	 * @return <type> array - Query string (String), number of values (int)
	 */
	public function build_query($ar)
	{
		$newAr = array_filter($ar, array("self", "filterNullValues"));
		$str = http_build_query($newAr);
		return array(
			"query" => $str,
			"count" => count($newAr)
		);
	}

	public function getBaseFileName()
	{
		//print_r($_SERVER);
	}

	/**
	 * <p>Checks if URL is an actual URL by checking if there's a host in the string.
	 * For example, "cnncom" will return false, while "http://www.cnn.com/" will return true.</p>
	 *
	 * @param string $url
	 * @return boolean true if it seems to be a real url.
	 */
	public static function isWellFormedURL($url)
	{
		$result = parse_url($url);
		if (!isset($result['host']))
			return false;
		else
			return true;
	}

	/**
	 * gets Host URL, like www.example.com. Escape string is useful when writing to .htaccess files.
	 *
	 * @param boolean $ar['escape'] if set to true, adds an escape string, returning something like www\.example\.com
	 * @param boolean $ar['non-www'] if true, returns example.com instead of www.example.com
	 * @return string returns a string containing the host name
	 */
	public static function getHostURL($ar = null)
	{
		$host = $_SERVER['HTTP_HOST'];
		if ($ar['non-www'] == true)
			$host = str_replace("www.", "", $host);
		if ($ar['escape'] == true)
			$host = str_replace(".", "\.", $host);
		return $host;
	}

}

?>