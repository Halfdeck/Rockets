<?php

/**
 * Description of Textbox
 *
 * @author Aaron
 */
class ROCKETS_HTML_Textbox extends ROCKETS_HTML_Form 
{
	static $input_type = self::TYPE_INPUT_TEXT;
	
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
		
        $html = "<input type='" .self::$input_type ."' name='{$name}' value=\"{$obj->$name}\" {$readonly} {$size} {$id} {$class}/>";
		
		if(isset($options['dl'])) {
			return self::dl_wrap($html, $options);
		}
		else if(isset($options['li'])) {
			return self::li_label_wrap($html, $options);
		}
		else return $html;
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
    static public function draw_searchbox($options = array(null))
    {
		$id = (isset($options['id']) && $options['id'] == TRUE) ? "id='{$options['id']}'" : "";
		$class = (isset($options['class']) && $options['class'] == TRUE) ? "class='{$options['class']}'" : "";
		
        return "<input {$id} {$class} type='" .self::$input_type ."' name='{$options['name']}' size='{$options['size']}' value='" .ROCKETS_Request::get($options['name']) ."'>";
    }
}

?>
