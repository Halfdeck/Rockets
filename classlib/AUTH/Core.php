<?php

/**
 * Description of BasicAuth
 *
 * @author Halfdeck
 */
abstract class ROCKETS_AUTH_Core {
	/**
	 * Session keys .. e.g. $_SESSION['username']
	 */
	const USERNAME = 'username';
	const PASSWORD = 'password';

	/**
	 * username is more commonly used to access table users
	 */
	const USER_ID = 'user_id';

	/**
	 * if users click "remember me", set a cookie so user can enter the site without logging in again.
	 */
	const REMEMBER_ME = 'remember_me';

	/**
	 * default session decay - 100 days
	 */
	const DECAY = 8640000;

	/**
	 * keys to store sessions and cookies.
	 */
	private static $keys = array
		(
		'username',
		'password',
		'user_id'
	);

	/**
	 * tracks whether a user is logged in or not.
	 * @var type
	 */
	public $logged_in = false;

	/*	 * ********************************* */

	protected function logout()
	{
		foreach (self::$keys as $key)
		{
			unset($_SESSION[$key]);
			setcookie($key, "", time() - self::DECAY, "/");
		}

		session_unset();
		session_destroy();

		$this->logged_in = false;
	}

	/**
	 * This runs on every page. 
	 * STR_URL_QUERY_STRING contains the current page name.
	 * 
	 */
	public function validate()
	{
		switch ($_REQUEST[STR_URL_QUERY_STRING]) {
			/**
			 * If we're on the Logout page, logout by clearing sessions and cookies.
			 */
			case FILE_LOGOUT:
			/**
			 * If we're on the Register page, logout (clear sessions and cookies)
			 * Don't redirect: allow user to stay on this page so he/she can register
			 */
			case FILE_REGISTER:
				$this->logout();
				break;
			/**
			 * If we're on the login page, run ->login();
			 */
			case FILE_LOGIN:
				$this->login();
				break;
			/**
			 * If we're creating a new user, "pass through" - 
			 */
			case FILE_CREATE_USER:
				return;
			/**
			 * On any other page....see if a user is logged in
			 */
			default:
				if (!$this->is_logged_in())
				{
					ROCKETS_HTTP::redirect(RPATH_ROOT . "/" . FILE_LOGIN);
				}
				break;
		}
	}

	/**
	 * Create session and if user selects "remember me", save cookies
	 * Password is md5 encrypted
	 * 
	 * @param type $options 
	 */
	protected function create_sessions($options = array(null))
	{
		foreach ($options as $key => $value)
		{
			if ($key == self::PASSWORD)
			{
				$_SESSION[$key] = md5($value);
			}
			else
			{
				$_SESSION[$key] = $value;
			}
			if (isset($_POST[self::REMEMBER_ME]))
			{
				/**
				 * remember me checked, so remember info in a cookie
				 * Add additional member to cookie array as per requirement
				 */
				setcookie($key, $value, time() + self::DECAY, "/");
			}
		}
	}

	/**
	 * Exact implementation is application specific
	 * 
	 * @note must return true or false. If true, must also call create_sessions()
	 * 
	 * @return bool 
	 */
	abstract protected function is_valid($username, $password);

	/**
	 * @note This function gets called on login.php ONLY
	 * So if creditials are bad, there's no need to redirect.
	 *
	 * @return <type>
	 */

	/**
	 * Load session variable
	 */
	private function load_properties_by_session()
	{
		foreach ($_SESSION as $key => $value)
		{
			$this->$key = $value;
		}
	}

	protected function login()
	{
		$username = ROCKETS_Request::get(self::USERNAME);
		$password = ROCKETS_Request::get(self::PASSWORD);

		if ($username == "" or $password == "" or $this->is_valid($username, $password) == FALSE)
		{
			$this->logout();
		}
		else
		{
			ROCKETS_HTTP::redirect(FILE_SUCCESS);
		}
	}

	/**
	 * Checks to see if a user is logged in or not.
	 * 
	 * @return boolean True if user is logged in, False otherwise
	 */
	public function is_logged_in()
	{
		/** if username and password are saved in sessions, user is logged in * */
		if (isset($_SESSION[self::USERNAME]) AND isset($_SESSION[self::PASSWORD]))
		{
			$this->logged_in = true;
			$this->load_properties_by_session();
			return true;
		}
		elseif (isset($_COOKIE[self::USERNAME]) && isset($_COOKIE[self::PASSWORD]))
		{
			if ($this->is_valid($username, $password))
			{
				ROCKETS_HTTP::redirect(FILE_SUCCESS);
			}
			else
			{
				$this->logout();
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get a user's ID
	 * 
	 * @note can't use __get here because its a static call
	 * 
	 * @return type 
	 */
	static public function get_user_id()
	{
		if (isset($_SESSION[self::USER_ID]))
			return $_SESSION[self::USER_ID];
		else
			return null;
	}

	/**
	 * Get a user's username
	 * 
	 * @return type 
	 */
	static public function get_username()
	{
		if (isset($_SESSION[self::USERNAME]))
			return $_SESSION[self::USERNAME];
		else
			return null;
	}

	/**
	 * Default __get
	 * First tries to fetch value from SESSIONS
	 * 
	 * @param type $name
	 * @return type
	 */
	public function __get($name)
	{
		switch ($name) {
			default:
				if (isset($this->$name))
				{
					return $this->$name;
				}
				else
				{
					if(isset($_SESSION[$name])) $this->$name = $_SESSION[$name];
					else $this->$name = NULL;
					return $this->$name;
				}
		};
	}

}

?>
