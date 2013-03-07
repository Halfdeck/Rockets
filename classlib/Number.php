<?php

/**
 * ROCKETS_Number deals with numbers.
 *
 * @copyright 2011 Halfdeckconsulting
 * @author Halfdeck
 * @version 1.0
 */
class ROCKETS_Number {

	/**
	 * <p>Print a number in USD money format (e.g. "$165,200").
	 * money_format doesn't work on Windows (a problem when developing locally on Xampp), so
	 * use number_format.</p>
	 *
	 * @param int $x - an integer (e.g. 123461)
	 * @param bool $pennies - if true, we also show pennies
	 * @return string e.g $123,461
	 */
	public static function getMoney($x, $pennies = FALSE)
	{
		if ($pennies)
			return "$" . number_format($x, 2, ".", ",");
		else
			return "$" . number_format($x, 0, ".", ",");
	}

	public static function numberFormat($x)
	{
		return number_format($x, 0, ".", ",");
	}

	/**
	 * If a float is stored as an int in MYSQL, convert it to float.
	 * The float is padded using number_format
	 * 
	 * @param type $int
	 * @param type $digits_after_decimal
	 * @return type 
	 */
	static public function mysql_float_conversion($int, $digits_after_decimal = 2)
	{
		$divider = pow(10, $digits_after_decimal);
		$int = $int / $divider;
		return number_format($int, $digits_after_decimal);
	}
	
	/**
	 * Method that takes a money strings and turns it into a float
	 * 
	 * @param type $str
	 * @return type
	 */
	public static function moneyToFloat($str)
	{
		$replacements = array("$",",");
		$str = str_replace($replacements, "", $str);
		return $str;
	}

}

?>
