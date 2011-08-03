<?php

/**
 * ROCKETS_View - base View class, used primarily to print class properties
 * 
 * ROCKETS_View doesn't extend ROCKETS_ConfigureObject because doing so makes auto-loading
 * property values very difficult.
 * 
 *
 * @author Halfdeck
 */
class ROCKETS_View
{

    /**
     * Default __get
     * @param type $name
     * @return type
     */
    public function __get($name)
    {
        if (!isset($this->$name))
            return null;

        switch ($name) {
            default:
                return $this->$name;
                break;
        };
    }

    /**
     * Allows me to set protected property
     * This method is necessary when loading protected property values from MYSQL
     * 
     * @param <type> $name
     * @param <type> $value
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

}

?>
