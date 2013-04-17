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
	 * If resulting string is not numeric, returns false
	 * 
	 * @param type $str
	 * @return type
	 */
	public static function moneyToFloat($str)
	{
		$replacements = array("$",",");
		$str = str_replace($replacements, "", $str);
		
		if(is_numeric($str))
		{
			return $str;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Checks if a given input is a valid number or not.
	 * 
	 * @param type $str
	 * @param type $fieldname
	 * @return boolean
	 * @throws Exception
	 */
	public static function is_valid_number($str, $fieldname, $options = array())
	{
		if($str === NULL || $str === '')
		{
			throw new Exception("{$fieldname} can't be blank.");
		}
		else if(is_numeric($str) == false)
		{
			throw new Exception("{$fieldname} must be a number.");
		}
		else if($str === 0 && !isset($options['allow zero']))
		{
			throw new Exception("{$fieldname} can't be zero.");
		}
		else if(isset($options["positive"]) && $str < 0)
		{
			throw new Exception("{$fieldname} must be a positive number.");
		}
		else if(isset($options["integer"]) && is_int($str) == false)
		{
			throw new Exception("{$fieldname} must be an integer.");
		}
		else {
			return true;
		}
	}

}

?>
