<?php

/*
 * String class, containing generic string-related functions
 */

Class ROCKETS_String {

	/**
	 * Alias for ucwords - turns "jill hill" into "Jill Hill"
	 * 
	 * @param type $str
	 * @return type 
	 */
	static public function capitalizeName($str) {
		return ucwords($str);
	}
	
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
	 * Given "(111) 111-1111
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
         * reduce any phone number into numbers, e.g. (123) 234-1234 => 12342341234
         * @param <type> $string
         * @return string
         */
    public static function stripPhone($string) {
    	$string = str_replace(" ", "", $string);
    	$string = str_replace("-", "", $string);
    	$string = str_replace(array("(", ")"), "", $string);
    	return $string;
    }
	
	/**
	* pad zip code when its an integer
	*/
	
	public static function padZip($str) {
		return str_pad($str, 6, "0", STR_PAD_LEFT);	
	}
	
	/**
	* takes a 6 digit zip 95123 and extension 1234 => combine into 95123-1234
	**/
	public static function mergeZip($zip,$ext) {
		if($ext=="") return $zip;
		else return $zip ."-" .$ext;
	}
    
    
    /**
     * Split phone number into 3 components
     */
     
     public static function splitPhone($string) {
     	$string = self::stripPhone($string);
     	$ar[0] = substr($string, 0, 3);
     	$ar[1] = substr($string, 3,3);
     	$ar[2] = substr($string, 6, 4);
     	return $ar;
     }
     
     /**
     * merge 3 variables into a phone number string
     * used to handle phone input from forms that splits up # into 3 parts.
     */
     
     public static function mergePhone($ar = array(null)) {
     	return implode($ar);
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
