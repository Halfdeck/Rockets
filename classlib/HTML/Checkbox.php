<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Checkbox
 *
 * @author Aaron
 */
class ROCKETS_HTML_Checkbox extends ROCKETS_HTML_Form {

	const TYPE_INPUT_CHECKBOX = 'checkbox';
	
	/**
	 * Auto-draws a checkbox input, using an object
	 * 
	 * Usage: JOB_HTML_Form::draw_checkbox_obj('input_name', $user) where $user is a class JOB_MODEL_User
	 * 
	 * @param type $checked if 1, this auto-sets to checked
	 * @param type $name  name of the checkbox
	 */
	static public function draw_obj($name, $obj, $options = array(null))
	{
		$input_type = self::TYPE_INPUT_CHECKBOX;
		$selected = ($obj->$name == true) ? $selected = self::get_selected_string($input_type) : "";
		
		$html = "<input type='hidden' name='{$name}' value='false' />" . PHP_EOL;
		$html .= "<input type='{$input_type}' value='1' name='{$name}' {$selected} />" . PHP_EOL;
		
		echo self::dl_wrap($html, $options);
	}

}

?>
