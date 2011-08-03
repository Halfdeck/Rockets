<?php

/**
 * Tracks traffic - inserts traffic data into MYSQL
 *
 * Instructions
 *
 * To use:
 *
 * 1. Create an extension class in your app
 * 2. set cookieKey and cookieID
 * 3. write an insert() method
 */
abstract class ROCKETS_TrafficTracker {

    /**
     * User ID defaults to empty string
     * @var string
     */
    protected $userID = "";
    protected $queryString = "";
    protected $referer = "";
    /**
     * Set cookie Key
     * @var <type>
     */
    protected $cookieKey;
    /**
     * Set cookie name
     * @var <type>
     */
    protected $cookieID;

    /**
     * Check user agent. If user agent is in the blocked user agents list, don't record hit.
     * @return <type>
     */
//    private function isBlockedUserAgent() {
//	if(array_key_exists($_SERVER['HTTP_USER_AGENT'], $this->blockedUserAgents)) return true;
//	else return false;
//    }

    function __construct() {
	$this->getUserID();
	$this->getQueryString();
	$this->getReferer();
	//if(!$this->isBlockedUserAgent()) $this->insert();
	$this->insert();
    }
    
    /**
     * MYSQL Insert function - depends on the app
     */
    protected function insert() {
	mysql_query($this->getMYSQLQuery());
    }

    /**
     * query string is application-specific, so is abstract
     * @return string MYSQL insert query string
     */
    abstract protected function getMYSQLQuery();
    /**
     * Get referer string, if any
     */
    protected function getReferer() {
	if (isset($_SERVER['HTTP_REFERER']))
	    $this->referer = $_SERVER['HTTP_REFERER']; //prevents script from issuing an error
    }

    /**
     * Get query string, if any
     */
    protected function getQueryString() {
	if (isset($_SERVER['QUERY_STRING']))
	    $this->queryString = $_SERVER['QUERY_STRING'];
    }

    /**
     * Get user ID, if any
     * Cookie to look in is in $this->cookieID, and the userID key is in $this->cookieKey
     */
    protected function getUserID() {
	if (isset($_COOKIE[$this->cookieID])) {
	    $cookie = $_COOKIE[$this->cookieID];
	    if (isset($cookie[$this->cookieKey]))
		$this->userID = $cookie[$this->cookieKey];
	}
    }
}

?>