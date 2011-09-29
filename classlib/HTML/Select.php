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
	 * @param <type> $ar['display_condition'] if false, don't display - used for dynamically displaying listboxes
     * 
     */
    static public function draw($ar = array(null))
    {
		$select_clause = "";
		
		if(isset($ar['display_condition']) && $ar['display_condition'] == FALSE) return;
		
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
			if($ar['options'] == null) $ar['options'] = array('' => $ar['first string']); // prevent error when input array is empty
            else $ar['options'] = array('' => $ar['first string']) + $ar['options'];
        }
		
		$select_clause .=  "<select name='{$ar['name']}' {$classStr}>";
        foreach ($ar["options"] as $key => $val)
        {
            $selected = "";
            if ($key == $ar["checked"])
                $selected = " selected='selected'";
            $select_clause .= "		<option value='{$key}' {$selected}>{$val}</option>" .PHP_EOL;
        }
        $select_clause .= "</select>";
		
		echo self::dl_wrap($select_clause, $ar);
    }
}

?>
