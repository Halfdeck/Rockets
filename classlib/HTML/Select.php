<?php

/**
 * Description of Select
 *
 * @author Aaron
 */
class ROCKETS_HTML_Select extends ROCKETS_HTML_Form {
	
  /**
     * Extension of parent:draw_select() - extended to allow the first row to be customizable.
     * 
     * Draw a listbox given an array of values.
     *
     * (array(
     * 	"name" => "",
     *  "options" => "",
     *  "checked" => "",
     *  "class" => ""
     * ));
     * 
     * Usage: Uses $_REQUEST to autofill some values
     * 
     * @global $_REQUEST
     * 
     * @param <type> $ar['class']
     * @param <type> $ar['first string']
     * @param <type> $ar['name'] INPUT name
     * @param <type> $ar['checked'] checked value
     * @param <type> $ar['options']
     * 
     */
    static public function draw($ar = array(null))
    {
        if (!isset($_REQUEST[$ar['name']]))
        { // prevent errors
            $_REQUEST[$ar['name']] = "";
        }

        if (empty($ar['checked']))
            $ar['checked'] = $_REQUEST[$ar['name']];

        /**
         * Optional class string - for custom styling
         */
        if (isset($ar['class']))
            $classStr = "class='{$ar['class']}'";
        else
            $classStr = "";

        if (isset($ar['first string']))
        {
            $ar['options'] = array('' => $ar['first string']) + $ar['options'];
        }

        echo "<select name='{$ar['name']}' {$classStr}>";
        foreach ($ar["options"] as $key => $val)
        {
            $selected = "";
            if ($key == $ar["checked"])
                $selected = " selected='selected'";
            echo "		<option value='{$key}' {$selected}>{$val}</option>" .PHP_EOL;
        }
        echo "</select>";
    }
}

?>
