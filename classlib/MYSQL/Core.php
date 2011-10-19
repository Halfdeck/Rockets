<?php

/**
 * <p>Bare-bones runtime MYSQL class - should be superfast with no extra weight.</p>
 *
 * <p>Memory usage: 94560 93580 0.00420784950256 - using const; 94000 93020 0.00434303283691  using defines</p>
 *
 * @global DB_HOST, DB_USER, DB_PASS, DB_NAME
 * 
 * @copyright Copyright 2010 Halfdeck
 * @author Halfdeck
 * @version 1.0
 *
 */
class ROCKETS_MYSQL_Core {

	private static $db;

	/**
	 * @global DB_HOST
	 * @global DB_USER
	 * @global DB_PASS
	 * @global DB_NAME
	 * @global BOOL_DEBUG
	 * @global BOOL_EXECUTE
	 */

	/**
	 * Connect to MYSQL database
	 */
	static public function connect()
	{
		self::$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME, self::$db);
	}

	/**
	 * Disconnect from MYSQL
	 */
	static public function disconnect()
	{
		mysql_close(self::$db);
	}

}

?>
