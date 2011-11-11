<?php

/**
 * Description of Pagenation
 *
 * @author Halfdeck
 */
class ROCKETS_HTML_Pagination
{
	/**
	 * CSS properties
	 */
	const cssPageCount = "pageCount";
	const cssClearFix = "clear";
	const cssIndex = "pageIndex";

	/**
	 * @global STR_URL_QUERY_STRING
	 */

	/**
	 * Draw pagecount .. e.g. "Page 2 of 200 pages"
	 *
	 * @param <type> $limitStart - current page index
	 * @param <type> $pages - max number of pages
	 */
	public static function draw_pagecount($limitStart=0, $pages)
	{
		if ($pages == 0)
		{
			echo "<p class='" . self::cssPageCount . "'>No Results Found</p>";
		}
		else {
			echo "<p class='" . self::cssPageCount . "'>Page " .($limitStart + 1) ." of {$pages}</p>";
			
		}
	}

	/**
	 * Draws an navigation index for the search page.
	 * If no prev-results exist, the button is greyed.
	 * 
	 * @param <type> $limitStart
	 * @param <type> $pages
	 */
	public static function draw_indexSimple($limitStart=0, $pages)
	{

		$thisPage = $limitStart + 1;
		$prev = $thisPage - 1;
		$next = $thisPage + 1;
		$html = "";

		if ($prev > 0)
			$html .= "<input type='button' class='activeNav' name='prevSERP' value=''/>";
		else
			$html .= "<input type='button' class='inactiveNav' name='prevSERP' value=''/>";
		if ($next <= $pages)
			$html .= "<input type='button' class='activeNav' name='nextSERP' value=''/>";
		else
			$html .= "<input type='button' class='inactiveNav' name='nextSERP' value=''/>";
		return $html;
	}

	/**
	 * Draw pagination, given total number of records and current page number
	 * First query string item must not start with an &, which issues a 404.
	 * Also if limitStart is already in REQUEST_URI, you need to replace it instead of adding to it.
	 * @todo this method needs to be broken down into smaller pieces
	 * 
	 * @param array $ar['limitStart']
	 * @param array $ar['totalRows']
	 * @param array $ar['resultsPerPage']
	 * @param array $ar['queryVarName'] (default = 'limitStart') query variable name used to control pagenation, e.g. ?limitStart=0&...
	 */
	static public function draw($ar = array(null))
	{
		$baseURL = self::get_base_url();
		$pages = ceil($ar['totalRows'] / $ar['resultsPerPage']);
		
		self::draw_pagecount($ar['limitStart'], $pages);

		//echo "<div class='" .self::cssClearFix ."'></div>";
		
		echo "<div id='" .self::cssIndex ."'>";

		self::draw_prev_next_ahrefs($ar['limitStart'], $baseURL, $pages);

		self::draw_indexes($baseURL, $ar['limitStart'], $pages);

		echo "</div>";
	}
	
	static protected function get_base_url()
	{
		$url = new ROCKETS_URL;

		/**
		 * erase limitStart from query string, since has to be overwritten
		 */
		$output = $url->getQueryStringArray(array(STR_QUERY_PAGINATION));

		$baseURL = $output[STR_URL_QUERY_STRING] . "?"; // create index root path, e.g. /index.php?
		$output[STR_URL_QUERY_STRING] = null; // erase the URL part of the query string from query string array

		$v = $url->build_query($output); // get clean query string and arg count
		$baseURL .= $v['query']; // build up path, attach filter/sort variables if any

		if ($v['count'] == 0)
			$baseURL .= STR_QUERY_PAGINATION ."="; // handle & - if limitStart is first value, don't use & or page will 404
		else
			$baseURL .= "&" .STR_QUERY_PAGINATION ."=";
		return $baseURL;
	}

	/**
	 * draw prev/next links
	 * 
	 * @param type $limitstart
	 * @param type $baseURL
	 * @param type $pages 
	 */
	static private function draw_prev_next_ahrefs($limitstart, $baseURL, $pages) 
	{
		$thisPage = $limitstart + 1;
		$prev = $thisPage - 1;
		$next = $thisPage + 1;
		
		if ($prev > 0)
			echo "&nbsp;<a href='{$baseURL}" . ($prev - 1) . "'><< Previous</a>";
		if ($next <= $pages)
			echo "&nbsp;<a href='{$baseURL}" . ($next - 1) . "'>Next >></a>";
	}
	
	static private function draw_indexes($baseURL, $limitStart, $pages) {
		
		if($pages == 1) return;
		
		$max = $limitStart + 4;
		if ($max < 5)
			$max = 5;
		if ($max >= $pages)
			$max = $pages - 1;

		$min = $limitStart - 3;
		if ($min < 1)
			$min = 1;
		
		/**
		 * Draw first index
		 */
		self::draw_index($baseURL, 0, $limitStart);

		/**
		 * Draw middle indexes
		 */

		if ($min - 1 > 0)
			echo " ... ";

		for ($i = $min; $i < $max; $i++)
		{
			self::draw_index($baseURL, $i, $limitStart);
		}

		if ($max + 1 < $pages)
			echo " ... ";

		/**
		 * Draw last index
		 */
		self::draw_index($baseURL, $pages - 1, $limitStart);
	}

	static private function draw_index($baseURL, $i, $limitStart)
	{
		if ($i == $limitStart)
			echo "&nbsp;<strong>" . ($i + 1) . "</strong>";
		else
			echo "&nbsp;<a href='{$baseURL}" . ($i) . "'>" . ($i + 1) . "</a>";
	}

}

?>