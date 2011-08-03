<?php

/**
 * Description of Filter
 *
 * @author Halfdeck
 */
class ROCKETS_HTML_Filter {
    public $name;
    public $options;
    public $checked;
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
    public function  __construct($name,$options,$condition,$default = "") {
	$this->name = $name;
	$this->options = $options;
	$this->condition = $condition;
	if(!isset($_REQUEST[$this->name])) $_REQUEST[$this->name] = $default;
	$this->checked = $_REQUEST[$this->name];
    }
}
?>
