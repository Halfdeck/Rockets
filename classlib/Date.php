<?php

/*
 * Generic Date class that deals with date-related functions
 * 
 * v1.1
 * 
 * Update: added default_time_zone since setting that is required
 *
 */

class ROCKETS_Date {
	/**
	 * Date-time Format: July 4, 2011 12:00 am
	 */
	const FRMT_DATETIME = 0;
	/**
	 * Date Format: July 4, 2011
	 */
	const FRMT_DATE = 1;
	
	/**
	 * Date Format: 8/1/2011
	 */
	const FRMT_DATE_SLASHES = 2;
	/**
	 * Time only: 12:00 pm
	 */
	const FRMT_TIME = 3;
	/**
	 * June 10
	 */
	const FRMT_DATE_NO_YEAR = 4;
	/**
	 *
	 * @global TIMEZONE - default time zone constant - required
	 */
	public static $days = array("mon" => 0, "tue" => 1, "wed" => 2, "thu" => 3, "fri" => 4, "sat" => 5, "sun" => 6);
	const TIMEZONE = 'America/Los_Angeles';

	/**
	 * Create a date MYSQL likes: "2/4/2011" => "2011/02/04"
	 * Issue: date could also be Aug 11, 2011?? which throws an error
	 * 
	 * @param type $str 
	 */
	static public function mysql_makeDate($str)
	{
		if(empty($str)) return null;
		list($month, $date, $year) = explode("/", $str);
		return "{$year}/{$month}/{$date}";
	}
	
	/**
	 * "Jan 1, 2011 1:30 am" -> "2011/1/1 13:30:00"
	 * 
	 * @param type $str 
	 */
	static public function mysql_makeDateTime($str) 
	{
		$result = strtotime($str);
		return date("Y-m-d H:i:s", $result);
	}

	/**
	 * takes a string like "06/02/2010" and returns "June 2, 2010"
	 */
	public static function createDateStr($datestr)
	{
		list($m, $d, $y) = split("/", $datestr);
		$time = mktime(0, 0, 0, $m, $d, $y);
		return date("M j, Y", $time);
	}

	/**
	 * Take a MYSQL date string and convert it into readable form.
	 * @param <type> $datestr e.g. 2010-07-28 17:39:57
	 */
	public static function createDateStrFromMYSQL($datestr, $format = self::FRMT_DATETIME)
	{
		list($y, $m, $d, $h, $min, $s) = sscanf($datestr, "%d-%d-%d %d:%d:%d");
		if($y == 0 && $m == 0 && $d == 0) return null;
		$time = mktime($h, $min, $s, $m, $d, $y);
		
		switch($format) {
			case self::FRMT_DATETIME:
				return date("M j, Y g:i A ", $time);
			case self::FRMT_DATE:
				return date("M j, Y", $time);
			case self::FRMT_DATE_SLASHES:
				return date("m/j/Y", $time);
			case self::FRMT_TIME:
				return date("g:i A", $time);
			case self::FRMT_DATE_NO_YEAR:
				return date("M j", $time);
			default:
				return null;
		}
	}

	/**
	 * Get today's date
	 * @param int $offset Days offset (e.g. -1 : yesterday, 1: tomorrow, 0: today)
	 * @return date Return today's date
	 */
	static public function get($offset = 0)
	{
		return mktime(0, 0, 0, date("m"), date("d") + $offset, date("Y"));
	}

	/**
	 * Get today's date
	 * @param int $offset Days offset (e.g. -1 : yesterday, 1: tomorrow, 0: today)
	 * @return date Return today's date
	 */
	public function getTimePlusOffset($offset = 0)
	{
		return mktime(0, 0, 0, date("m"), date("d") + $offset, date("Y"));
	}

	/**
	 * Get now's hour.
	 * @return int Returns the current time's hour (e.g. if current time is 8:24pm, returns 8)
	 */
	public function getHour()
	{
		return $hour = date("G");
	}

	/**
	 * Create date from string.
	 * @param string $ar['format'] - string format, supported: XX/XX/XXXX
	 * @param string $ar['date'] - date string
	 * @return date returns date
	 */
	public function createDateFromStr($ar)
	{
		switch ($ar['format']) {
			case "XX/XX/XXXX":
				list($m, $d, $y) = explode("/", $ar['date']);
				return mktime(0, 0, 0, $m, $d, $y);
				break;
			case "XXXXXXXX":
				return mktime(0, 0, 0, substr($ar['date'], 0, 2), substr($ar['date'], 2, 2), substr($ar['date'], 4, 4));
				break;
			default:
				break;
		}
	}

	/**
	 * Get day of week index (wednesday => returns 2).
	 * <p>For details, see public $days array.</p>
	 * @return int array index 
	 */
	public function getDayOfWeekIndex()
	{
		$dayOfWeek = strtolower(date("D"));
		$offset = self::$days[$dayOfWeek];
		return $offset;
	}

	/**
	 * Return difference between two dates in days.
	 *
	 * <p>D1 is subtracted from D2, so D2 is presumed to be the more recent date.</p>
	 *
	 * @param <type> $d1 date 1
	 * @param <type> $d2 date 2
	 * @return <type> number of days difference
	 */
	public function getDifferenceDays($d1, $d2)
	{
		$delta = $d2 - $d1;
		return ($delta / 86400);
	}

	/**
	 * Get an array containing the start/end timestamps for today
	 */
	public static function getTodayMYSQLStartEndTimestamps()
	{
		date_default_timezone_set(TIMEZONE);
		$times['start'] = date("Y-m-d") . " 00:00:00";
		$times['end'] = date("Y-m-d") . " 23:59:59";
		return $times;
	}

	public static function getWeekMYSQLTimestamps()
	{
		date_default_timezone_set(TIMEZONE);
		$times['end'] = date("Y-m-d") . " 23:59:59";
		$times['start'] = date("Y-m-d", self::getTimePlusOffset(-7)) . " 00:00:00";
		return $times;
	}

	/**
	 * Get today's date in long form e.g. "June 20, 2011"
	 * @return <type>
	 */
	public static function getLongDate()
	{
		return date("M d, Y");
	}

	/**
	 * Format today's date in various ways
	 *
	 * @param <type> $formatStr "XX/XX/XXXX" (02/01/2011)
	 * @return <type>
	 */
	public static function formatDate($formatStr)
	{
		switch ($formatStr) {
			case "XX/XX/XXXX":
				return date("m/d/Y");
				break;
			default:
				return false;
		}
	}

	/**
	 * Alias of strtotime() - used to translate MYSQL date string into unix timestamp.
	 * 
	 * @param string $date
	 * @return int	Unix timestamp 
	 */
	static public function date_str_to_unix_timestamp($date)
	{
		return strtotime($date);
	}

	/**
	 * @todo Must compare without comparing hours/mins/secs
	 * 
	 * @param string $date date string, like "june 12, 2011"
	 * @return boolean true if $date is later than today, otherwise false
	 */
	static public function isLate($date)
	{
		if (strtotime("today") > self::date_str_to_unix_timestamp($date))
			return true;
		else
			return false;
	}
	
	static public function get_age($birth_date_string) 
	{
		$diff = time()-strtotime($birth_date_string);
		$age = $diff / 31556926;
		return floor($age); 
	}
}

?>
