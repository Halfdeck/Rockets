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
	static public function draw($name, $obj, $col, $row)
	{
		echo "<textarea name='{$name}' cols='{$col}' rows='{$row}'/>{$obj->$name}</textarea>";
	}

}

?>
