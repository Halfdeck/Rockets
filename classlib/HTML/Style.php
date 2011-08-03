<?php

// used to calculate dimensions, position, margin, and other layout elements

class ROCKETS_HTML_Style {

	public $id;
	public $width;
	public $height;
	public $padding;
	
	function __construct($id) {
		$this->id = $id;
	}
	
	function display() {
		$html = $this->id ."{";
		$html .= "width: " .$this->width .";";
	
	}
}