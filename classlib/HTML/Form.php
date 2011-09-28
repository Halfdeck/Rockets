<?php

/**
 * HTML ROCKETS_HTML_Form object
 */
class ROCKETS_HTML_Form {
	/**
	 * input types
	 */
	const IT_CHECKBOX = 0;
	const IT_TEXT = 1;
	
	const TYPE_INPUT_CHECKBOX = 'checkbox';
	const TYPE_OPTION = 'option';
    const TYPE_INPUT_TEXT = 'text';
	const TYPE_INPUT_PASSWORD = 'password';

	/**
	 * Draw a listbox given an array of values.
	 *
	 * (array(
	 * 	"name" => "",
	 *  "options" => "",
	 *  "checked" => "",
	 *  "class" => ""
	 * ));
	 * 
	 * @param <type> $ar
	 */
	static public function draw_select($ar = array(null))
	{
		/**
		 * Optional class string - for custom styling
		 */
		if (isset($ar['class']))
			$classStr = "class='{$ar['class']}'";
		else
			$classStr = "";

		echo "<select name='{$ar['name']}' {$classStr}>";
		foreach ($ar["options"] as $key => $val)
		{
			$selected = "";
			if ($key == $ar["checked"])
				$selected = " selected=\"selected\"";
			echo "		<option value='" . $key . "' {$selected}>" . $val . "</option>\n";
		}
		echo "</select>";
	}

	/** Draw search textbox

	  params:

	  size
	  name
	  value
	 * */
	static public function draw_searchbox($ar = array(null))
	{
		echo "<input type='text' name='{$ar['name']}' size='{$ar['size']}' value='{$ar['value']}'>";
	}

	/**
	 * Returns "checked=checked" type string depending on input type
	 * I created this to abstract other methods more
	 * 
	 * @param type $input_type
	 * @return type 
	 */
	static protected function get_selected_string($input_type)
	{
		switch ($input_type) {
			case self::TYPE_INPUT_CHECKBOX:
				return "checked='checked'";
				break;
			case self::TYPE_OPTION:
				return "selected='selected'";
				break;
			default:
				return null;
				break;
		}
	}
	
	/**
     * 
     * Returns an array between min, max numbers (like age), which you can
     * plug into a Form method to draw UI
     * 
     * @param type $min
     * @param type $max
	 * @param float $increment if 2, then array returns 2,4,6,8..., if .5, then 2, 2.5, 3, 3.5....
     * @return type 
     */
    static public function get_numbers($min, $max, $options = array(null))
    {
		$options['increment'] = (isset($options['increment'])) ? $options['increment'] : 1;
		$options['decimals'] = (isset($options['decimals'])) ? $options['decimals'] : 0;

        for ($i = $min; $i <= $max; $i = $i + $options['increment'])
        {
			/**
			 * We use quotes here to capture floats correctly
			 */
            $ar["{$i}"] = number_format($i, $options['decimals']);
        }
		
        return $ar;
    }

}

?>
