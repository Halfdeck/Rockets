<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CheckboxGroup
 *
 * @author Aaron
 */
class ROCKETS_HTML_CheckboxGroup extends ROCKETS_HTML_Form {

	static $input_type = self::TYPE_INPUT_CHECKBOX;
	/**
	 * Draw a checkbox group, given an array of available values, and an array of checked
	 * 
	 * implementation should be similar to drawing a SELECT group
	 * 
	 * Main difference is that CHECKED value is an ARRAY, not a single value
	 * 
	 * @note Hidden checkbox isn't used here - instead, the entire set is wiped and we do a new insert for the
	 * entire set
	 */
	static public function draw(Array $ar)
	{
		if (!isset($_REQUEST[$ar['name']]))
		{ // prevent errors
			$_REQUEST[$ar['name']] = "";
		}

		/**
		 * Load checked value from $_REQUEST, if any
		 * if $ar['checked'] isn't empty, use that value
		 */
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

		foreach ($ar["options"] as $key => $value)
		{
			$selected = "";
			if (isset($ar["checked"][$key]))
				$selected = self::get_selected_string(self::$input_type);
			echo "<input {$classStr} type='" .self::$input_type ."' name='{$ar['name']}[{$key}]' value='{$key}' {$selected}>{$value}" . PHP_EOL;
		}
	}

}

?>
