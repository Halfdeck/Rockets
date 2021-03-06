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
		$disabled = "";
		$matched = false; // this variable tracks if value matches an item in the listbox
		
		if(isset($ar['display_condition']) && $ar['display_condition'] == FALSE) return;
		
		if(isset($ar['disabled'])) {
			$disabled = "disabled=disabled";
		}
		
		$multiple = (isset($ar['multiple'])) ? "multiple" : null;
		
        if (!isset($_REQUEST[$ar['name']]))
        { // prevent errors
            $_REQUEST[$ar['name']] = "";
        }
		
		/**
		 * If there's no checked value, reference request
		 * Checking using isset() instead of empty() because empty()
		 * loses 0 values.
		 */
        if (!isset($ar['checked']))
		{
			/**
			 * If REQUEST is empty but there's a default value, use that
			 */
			if(empty($_REQUEST[$ar['name']]) && isset($ar['default']))
			{
				$ar['checked'] =$ar['default'];
			}
			else {
				$ar['checked'] = $_REQUEST[$ar['name']];
			}
		}

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
		
		/**
		 * Unset option: used to add -1 value to a list of options, for checking unset values
		 * when default unset value is -1, instead of NULL (which requires a different query structure
		 * to check.
		 * 
		 * Example: 'unset option' => array(-1, 'Unassigned User')
		 */
		if (isset($ar['unset option']))
		{
			list($unset_key,$unset_value) = $ar['unset option'];
			
			$GLOBALS['unitTest']::logResult(array(
				'message' => "{$unset_key} {$unset_value}",
			));

			$ar['options'][$unset_key] = $unset_value;
		}
		
		$title = (isset($ar['title'])) ? "title=\"" .htmlspecialchars($ar['title']) ."\"" : null;
		
		$options_clause = "";
		
		if(isset($ar["options"]))
		{
			foreach ($ar["options"] as $key => $val)
			{
				$selected = "";
				
				/**
				 * Make sure comparisons are accurate. === somehow doesn't
				 * work with some of the non-empty items
				 */
				if($key == "0" && $ar['checked'] != "0")
				{
					
				}
				else if ($key == $ar["checked"])
				{
					$selected = " selected='selected'";
					$matched = true;
				}

				$options_clause .= "		<option value='{$key}' {$selected}>{$val}</option>" .PHP_EOL;
			}
		}
		
		if(isset($ar['disable_if_no_match']))
		{
			if($matched == false)
			{
				$readonly_value = (isset($ar['readonly_value'])) ? $ar['readonly_value'] : "---";
				$disabled = "disabled=disabled";
				$options_clause .= "		<option value='-1' selected='selected'>{$readonly_value}</option>" .PHP_EOL;
			}
		}
		
		$select_clause .=  "<select name='{$ar['name']}' {$classStr} {$disabled} {$multiple} {$title}>{$options_clause}</select>";
		
		///////////////////////////////////////

		if(isset($ar['dl'])) {
			return self::dl_wrap($select_clause, $ar);
		}
		else if(isset($ar['li'])) {
			return self::li_label_wrap($select_clause, $ar);
		}
		else {
			return $select_clause;
		}
    }
}

?>
