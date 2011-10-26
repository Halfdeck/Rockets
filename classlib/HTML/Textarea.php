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
		
		echo "<textarea name='{$name}' cols='{$col}' rows='{$row}' {$id} {$class}>{$obj->$name}</textarea>";
	}

}

?>
