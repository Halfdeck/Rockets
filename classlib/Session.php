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
	 * Save $_POST in $_SESSION, which is useful when dealing with multiple page
	 * submission pages
     */
    static public function post_to_session()
	{
		if (isset($_POST))
		{
			foreach ($_POST as $key => $value)
			{
				self::set_prefixed($key, $value);
			}
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
	 * Get prefixed session value.
	 * Prefixes are used to add scope to session variables.
	 * Prefix is defined in ROCKETS_AUTH_Core
	 * 
	 * @param type $key
	 * @return type 
	 */
	static function get_prefixed($key)
	{
		if(!isset($_SESSION[ROCKETS_AUTH_Core::get_cookie_name_prefix() .$key]))
		{
			return null;
		}
        else {
			return $_SESSION[ROCKETS_AUTH_Core::get_cookie_name_prefix() .$key];
		}
	}
	
	/**
	 * Unset, with prefix
	 * @param type $key 
	 */
	static function delete($key)
	{
		unset($_SESSION[ROCKETS_AUTH_Core::get_cookie_name_prefix() .$_SERVER['SERVER_PORT'] ."_" .$GLOBALS['c']->user_id .'_bookmark']);
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
	
	/**
	 * 
	 * Prefix session key with a prefix to scope the variable.
	 * 
	 * @param type $key
	 * @param type $value 
	 */
	static function set_prefixed($key, $value)
	{
		$_SESSION[ROCKETS_AUTH_Core::get_cookie_name_prefix() .$key] = $value;
	}

}

?>
