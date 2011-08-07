<?php

/**
 * HTML ROCKETS_HTML_Form object
 */
class ROCKETS_HTML_Form
{
    /**
     * input types
     */
    const IT_CHECKBOX = 0;
    const IT_TEXT = 1;

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

}

?>
