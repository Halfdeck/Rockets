<?php

/**
 * Session is used to carry form inputs on a multi-page form, so we can transport
 * data between pages without creating input=hidden
 *
 * @author Halfdeck
 */
class ROCKETS_Session
{

    /**
     * Problem with this method is that input data gets "locked in"
     */
    static public function request_to_session()
    {
        foreach($_REQUEST as $key=>$value) {
            $_SESSION[$key] = $value;
        }
    }
	
	/**
     * An empty check on the request key
     * Used to clean up if(empty($_REQUEST... codes
     * 
     * @param type $key
     * @return type 
     */
    static function get($key) {
        if(!isset($_SESSION[$key])) return null;
        else return $_SESSION[$key];
    }
	
	/**
	 * Save data in a session
	 * 
	 * @param type $key
	 * @param type $value 
	 */
	static function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

}

?>
