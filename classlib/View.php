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

    /**
     * Get the Model class name of an HTML class. For example,
     * if this class is JOB_HTML_Letter, then it returns JOB_MODEL_Letter
     * We use this to run $this->loadObject
     * 
     * @return type 
     */
    static protected function getModelClassName()
    {
        $thisclass = get_called_class();
        $class = str_replace("HTML", "MODEL", $thisclass);
        return $class;
    }

    /**
     * Load record and populate properties.
     * For example, given a JOB_HTML_Letter class, return an JOB_HTML_Letter
     * object with pre-filled properties based on $id
     * 
     * <h3>Usage</h3>
     * <pre>
     * $user = JOB_HTML_User->load_object($id);
     * echo $user->full_name;
     * </pre>
     * 
     * @param <type> $id
     * @return object
     */
    static public function load_object($id)
    {
        /**
         * Find the MODEL classname for an HTML class object
         */
        $classname = self::getModelClassName();
        /**
         * Create a new MODEL object, to retrieve data
         */
        $o = new $classname;

        /**
         * Get a record, using $id
         * this is a JOB_MYSQLTable method
         */
        $result = $o->get_record_by_id($id);
        /**
         * Return the record wrapped in this HTML class, so properties can be manipulated
         */
        return mysql_fetch_object($result, get_called_class());
    }

}

?>
