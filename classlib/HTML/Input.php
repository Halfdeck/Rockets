<?php

/**
 * HTML INPUT generator. Particularly method draw() is useful when auto-filling input values from $_REQUEST and
 * accessing $_REQUEST in a way that doesn't trigger null-value errors.
 *
 * @author Halfdeck
 */
class ROCKETS_HTML_Input {
    public $name;
    public $value;
    public $size;
    public $condition;

    /**
     * Construct a form filter - used for listboxes etc that auto loads and sets
     * depending on values in $_REQUEST.
     * 
     * @param <type> $name
     * @param <type> $options
     * @param <type> $condition
     * @param <type> $default
     */
    public function  __construct($name, $default = "") {
	$this->name = $name;
	if(!isset($_REQUEST[$this->name])) $_REQUEST[$this->name] = $default;
	$this->value = $_REQUEST[$this->name];
    }

    /**
     * Input view class that auto-sets $_REQUEST valuable while drawing the INPUT html
     * so PHP doesn't issue an error
     * 
     * @param <type> $name
     * @param <type> $size
     * @param <type> $default
     */
    public static function draw($name, $size=30, $default = "") {
	if(!isset($_REQUEST[$name])) $_REQUEST[$name] = $default;
	echo "<input type='text' size='{$size}' name='{$name}' value='{$_REQUEST[$name]}'>";
    }
}
?>
