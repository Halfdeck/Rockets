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

}

?>
