<?php

/**
 * Description of Textbox
 *
 * @author Aaron
 */
class ROCKETS_HTML_Password extends ROCKETS_HTML_Form 
{
	static $input_type = self::TYPE_INPUT_PASSWORD;
	const STR_READ_ONLY = "readonly='readonly'";
	
    /**
     * Auto-draws a textbox INPUT, using a class object
     * 
     * @param type $name name of the INPUT
     * @param type $obj object used to retrieve value
	 * @param boolean $options['read only'] activate read only 
	 * @param int $options['size'] custom input size
     */
    static public function draw_obj($name, $obj, $options = array(null))
    {
		$readonly = (isset($options['read only']) && $options['read only'] == TRUE) ? self::STR_READ_ONLY : "";
		$size = (isset($options['size']) && $options['size'] == TRUE) ? "size='{$options['size']}'" : "";
		$id = (isset($options['id']) && $options['id'] == TRUE) ? "id='{$options['id']}'" : "";
		$class = (isset($options['class']) && $options['class'] == TRUE) ? "class='{$options['class']}'" : "";
		
        return "<input type='" .self::$input_type ."' name='{$name}' value=\"{$obj->$name}\" {$readonly} {$size} {$id} {$class}/>";
    }
}

?>
