<?php

/**
 * Generic Cookie controller used for various cookie operations.
 * Response is sent in HTML instead of forcing a redirect - so that it can be processed with AJAX
 */
abstract Class ROCKETS_CONTROLLER_Cookie {

    protected $cookie;
    public $name = null;

    /**
     * Cookie scope .. "/" means domain wide
     * @var string
     */
    public $scope = "/";

    public $status;

    const STATUS_SET = 0;
    const STATUS_UNSET = 1;

    /**
     * Initializes internal values
     */
    function __construct() {
	$this->status = self::STATUS_UNSET;
	if (isset($_COOKIE[$this->name])) {
	    $this->cookie = $_COOKIE[$this->name];
	    $this->status = self::STATUS_SET;
	}
    }

    public function getLifeSpan($span) {
	switch($span) {
	    case "forever":
		return time() + 60 * 60 * 24 * 30;
		break;
	    case "logout":
		return time() - 3600;
		break;
	    default:
		return 0;
		break;
	}
    }

    protected function setCookie($key, $value, $time) {
	setcookie("{$this->name}[$key]", $value, $time, $this->scope);
    }

    /**
     * Set an array of values - for added flexibility
     * @param <type> $ar
     */
    public function setArray($ar = null) {
	$time = $this->getLifeSpan("forever");
	foreach($ar as $key => $val) {
	    $this->setCookie($key, $val, $time);
	    $this->$key = $val;
	}
    }

    /**
     * UnSet an array of values - for added flexibility
     * @param <type> $ar
     */
    public function unsetArray() {
	$time = $this->getLifeSpan("logout");
	foreach($this->cookie as $key => $val) {
	    $this->setCookie($key, FALSE, $time);
	    $this->$key = null;
	}
    }

    /**
     * autoLoad class properties with cookie data
     */
    protected function loadPropertiesFromCookie() {
	if(!$this->cookie) return;
	foreach($this->cookie as $key => $val) {
	    //echo "{$key} {$val}<br>" .PHP_EOL;
	    $this->$key = $val;
	    //print_r($this);
	}
    }
}

?>