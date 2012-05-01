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
		$html = "";
		$input_type = self::TYPE_INPUT_CHECKBOX;
		
		$value = (isset($options['value'])) ? $options['value'] : 1;
		$null_value = (isset($options['null value'])) ? $options['null value'] : 'false';
		
		/**
		 * Modified this method so it can take objects OR a plain value
		 */
		if(is_object($obj))
		{
			$selected = ($obj->$name == $value) ? $selected = self::get_selected_string($input_type) : "";
		}
		else {
			$selected = ($obj == $value) ? $selected = self::get_selected_string($input_type) : "";
		}

		$title = (isset($options['title'])) ? "title='{$options['title']}'" : null;
		$disabled = (isset($options['disabled'])) ? "disabled='disabled'" : null;
		$title = (isset($options['title'])) ? "title='" .htmlspecialchars($options['title']) ."'" : null;
		
		/**
		 * Set no_hidden_input to true to not draw the hidden input - especially
		 * useful for displaying checkboxes on search forms.
		 */
		if(isset($options['no_hidden_input']) && $options['no_hidden_input'] == true) 
		{
			
		} else {
			$html .= "<input type='hidden' name='{$name}' value='{$null_value}' />" . PHP_EOL;
		}
		
		$html .= "<input type='{$input_type}' value='{$value}' name='{$name}' {$selected} {$title} {$disabled} {$title}/>" . PHP_EOL;
		
		return self::dl_wrap($html, $options);
	}
	
	/**
	 * Mod of draw_obj - for capturing $_REQUEST and auto filling the checkbox,
	 * like ROCKETS_HTML_Select::draw() - used in a search form instead of 
	 * object edit form
	 * 
	 * @param type $ar
	 * @return type 
	 */
	static public function draw($ar = array(null))
	{
		$html = "";
		
		/**
		 * Attach a label if specified
		 */
		if($ar['label']) {
			$html .= "<label>{$ar['label']}</label>";
		}
		
		$input_type = self::TYPE_INPUT_CHECKBOX;
		$element = 'INPUT';
		$name = "name='{$ar['name']}'";
		$value_hidden = "value='{$ar['options']['null value']}'";
		$value = "value='{$ar['options']['value']}'";
		$type_hidden = "type='hidden'";
		$type = "type='{$input_type}'";
		$selected = (ROCKETS_Request::get($ar['name']) == $ar['options']['value']) ? 
				$selected = self::get_selected_string($input_type) : "";
		
		$html .= "<{$element} {$type_hidden} {$name} {$value_hidden} />" . PHP_EOL;
		$html .= "<{$element} {$type} {$value} {$name} {$selected} />" . PHP_EOL;
		
		return self::dl_wrap($html, $ar);
	}
	
	/**
	 * @mod uses boolean value instead of object
	 * 
	 * Auto-draws a checkbox input, using an object
	 * 
	 * Usage: JOB_HTML_Form::draw_checkbox_obj('input_name', $user) where $user is a class JOB_MODEL_User
	 * 
	 * @param type $checked if 1, this auto-sets to checked
	 * @param type $name  name of the checkbox
	 */
	static public function draw_boolean($name, $boolean, $options = array(null))
	{
		$input_type = self::TYPE_INPUT_CHECKBOX;
		
		$disabled = (isset($options['disabled'])) ? "disabled='disabled'" : null;
		$value = (isset($options['value'])) ? $options['value'] : 1;
		$null_value = (isset($options['null value'])) ? $options['null value'] : 'false';
		$selected = ($boolean) ? $selected = self::get_selected_string($input_type) : "";
		$title = (isset($options['title'])) ? "title='" .htmlspecialchars($options['title']) ."'" : null;
		
		$html = "<input type='hidden' name='{$name}' value='{$null_value}' />" . PHP_EOL;
		$html .= "<input type='{$input_type}' value='{$value}' name='{$name}' {$selected} {$disabled} {$title}/>" . PHP_EOL;
		
		return self::dl_wrap($html, $options);
	}

}

?>
