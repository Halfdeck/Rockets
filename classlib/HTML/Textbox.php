<?php

/**
 * Description of Textbox
 *
 * @author Aaron
 */
class ROCKETS_HTML_Textbox extends ROCKETS_HTML_Form 
{
	static $input_type = self::TYPE_INPUT_TEXT;
	const STR_READ_ONLY = "readonly='readonly'";
	
    /**
     * Auto-draws a textbox INPUT, using a class object
     * 
     * @param type $name name of the INPUT
     * @param type $obj object used to retrieve value
	 * @param boolean $options['read only'] activate read only 
     */
    static public function draw_obj($name, $obj, $options = array(null))
    {
		$readonly = (isset($options['read only']) && $options['read only'] == TRUE) ? self::STR_READ_ONLY : "";
		
        echo "<input type='" .self::$input_type ."' name='{$name}' value='{$obj->$name}' {$readonly}/>";
    }
	
	/**
     * @package JOBBOARD
     * Draw a search textbox
	 * usage:
	 * 
	 * draw_searchbox(array(
	 *	  'name' => ...,
	 *	  'size' => ....,
	 * );
	 * 
	 * $_REQUEST
	 * 
     * @param type $ar size, name, value
     */
    static public function draw_searchbox($ar = array(null))
    {
        echo "<input type='" .self::$input_type ."' name='{$ar['name']}' size='{$ar['size']}' value='" .ROCKETS_Request::get($ar['name']) ."'>";
    }
}

?>
