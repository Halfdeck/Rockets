<?php

/*
 * String class, containing generic string-related functions
 */

Class ROCKETS_String {

    /**
     * <p>Gets rid of extra spaces in between words and at the end/beginning of string.</p>
     * @param string $str any string
     * @return string cleaned up string
     */
    public static function removeSpaces($str) {
	return preg_replace("/[ ]+/"," ",trim($str)); 
    }

    /**
     * Strip extension from filename (e.g. "hello.txt" => "hello"
     * @param <type> $filename
     * @return <type>
     */
    public static function stripExtension($filename) {
	$path_parts = pathinfo($filename);
	return $path_parts['filename'];
    }

    /**
     * Remove anything that's not a letter or a number and turn the character into whitespace.
     * E.g. "this-is-a-house_by_the_lake" => "this is a house by the lake"
     * Used to clean CSV lines
     * 
     * @param <type> $str
     * @return <type>
     */
    public static function removeCharacters($str) {
	return preg_replace("/[^a-z0-9]/i"," ",$str);
    }

    /**
     * Clean phone number string entered by user.
     * @param <type> $string
     * @return string
     */
    public static function cleanPhone($string) {
	$string = str_replace(" ", "", $string);
	$string = str_replace("-", "", $string);
	$string = str_replace(array("(", ")"), "", $string);
	$string = substr($string, 0, 3) . "-" . substr($string, 3, 3) . "-" . substr($string, 6, 4);
	return $string;
    }

    /**
     * Add a space after every comma (e.g. "cookies,sandwiches.." -> "cookies, sandwiches")
     * @param string $str
     * @return string
     */
    public static function fixComma($str) {
	$str = preg_replace("/,([^ ])/",", $1",$str);
	return $str;
    }

    /**
     * Get rid of all caps from a sentence 
     * Capitalize the first letter of the string.
     * This method fixes text that contain all capped words.
     *
     * @param string $sentence
     * @return string
     */
    public static function fixCase($sentence) {
	$sentence = strtolower($sentence);
	$sentence = ucfirst($sentence);
	return $sentence;
    }

    /**
     * This method fixes text so &amp is used instead of &, so that the resulting HTML is wc3 compliant.
     *
     * @param string $str
     * @return string
     */
    public static function fixAmpr($str) {
	return str_replace("&","&amp;",$str);
    }

    /**
     * Clean string - a combination of string functions to clean up a sentence for HTML output
     * @param string $str
     * @return string
     */
    public static function cleanStr($str) {
	$str = self::fixComma($str);
	$str = self::fixCase($str);
	$str = self::fixAmpr($str);
	return $str;
    }

//    public static function splitFullName($str) {
//	$ar = array();
//	list($ar['first'],$ar['last']) = explode(" ", $str);
//	return $ar;
//    }
}

?>
