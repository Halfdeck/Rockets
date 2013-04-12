<?php

/**
 * Description of Textarea
 *
 * @author Aaron
 */
class ROCKETS_HTML_Textarea extends ROCKETS_HTML_Form {

	/**
	 * Auto-draws a textbox INPUT, using a class object
	 * 
	 * @param type $name name of the INPUT
	 * @param type $obj object used to retrieve value
	 */
	static public function draw($name, $obj, $col, $row, $options = array(null))
	{
		$id = (isset($options['id'])) ? "id='{$options['id']}'" : null;
		$class = (isset($options['class'])) ? "class='{$options['class']}'" : null;
		$readonly = (isset($options['read only']) && $options['read only'] == TRUE) ? self::STR_READ_ONLY : "";
		$html = (isset($obj->$name)) ? $obj->$name : null;
		
		return "<textarea name='{$name}' cols='{$col}' rows='{$row}' {$id} {$class} {$readonly}>{$html}</textarea>";
	}
	
	static public function draw_value($name, $value, $col, $row, $options = array(null))
	{
		$id = (isset($options['id'])) ? "id='{$options['id']}'" : null;
		$class = (isset($options['class'])) ? "class='{$options['class']}'" : null;
		$readonly = (isset($options['read only']) && $options['read only'] == TRUE) ? self::STR_READ_ONLY : "";
		$html = (isset($value)) ? $value : null;
		
		return "<textarea name='{$name}' cols='{$col}' rows='{$row}' {$id} {$class} {$readonly}>{$html}</textarea>";
	}

}

?>
