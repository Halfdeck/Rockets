<?php

/**
 * Copyright 2011 Halfdeckconsulting.com
 * 
 * This class contains methods related to $_REQUEST
 * 
 * @author Tetsuto Yabuki
 */

class ROCKETS_Request {

    /**
     * An empty check on the request key
     * Used to clean up if(empty($_REQUEST... codes
     * 
     * @param type $key
     * @return type 
     */
    static function get($key) {
        if(empty($_REQUEST[$key])) return null;
        else return $_REQUEST[$key];
    }
}

?>
