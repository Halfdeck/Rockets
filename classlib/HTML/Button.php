<?php

/**
 * Description of Textbox
 *
 * @author Aaron
 */
class ROCKETS_HTML_Button extends ROCKETS_HTML_Form 
{	
    /**
     * Auto-draws a button
     * 
	 * Required: $options['name'],$options['value']
	 * 
     * @param type $name name of the INPUT
     * @param type $obj object used to retrieve value
	 * @param boolean $options['read only'] activate read only 
	 * @param int $options['size'] custom input size
     */
    static public function draw($options = array(null))
    {
		$id = (isset($options['id']) && $options['id'] == TRUE) ? "id='{$options['id']}'" : "";
		$class = (isset($options['class']) && $options['class'] == TRUE) ? "class='{$options['class']}'" : "";
		$type = (isset($options['type'])) ? "type='{$options['type']}'" : "type='button'";
		$name = "name='{$options['name']}'";
		$element = 'button';
		$value = $options['value'];
		
        $html = "<{$element} {$id} {$class} {$type} {$name}>{$value}</{$element}>";
		
		return self::dl_wrap($html, $options);
    }
}

?>
