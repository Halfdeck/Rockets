<?php

/*
	Test Template Engine
	
	Should also deal with if blocks
*/

class ROCKETS_HTML_KillerTemplate {

	private $paths = array(); 			// tries to cache content in case it repeats
	private $replacements = array();	// array of values
	const MODE_CONSTANTS = 1;	// mode values used to either load constant values or assigned values
	const MODE_VARS = 2;
	
	/*
		Class constructor.
		Set import_constants to true to load defined constants
	*/
	function __construct($ar = array(null)) {
		if(array_key_exists('import_constants',$ar) && $ar['import_constants']==true) {
			$c = get_defined_constants(true);
			$this->assign($c['user'],ROCKETS_HTML_KillerTemplate::MODE_CONSTANTS);
		}
	}

	public function initialize($ar) {
		
		$path = $ar['template_path'];
		$replacements = $ar['replacements'];
		
		$this->assign($replacements, ROCKETS_HTML_KillerTemplate::MODE_VARS);
		
		if(array_key_exists($path,$this->paths)) $content = $this->paths[$path];
		else { // cache page content to avoid getting file content multiple times for looping sections
			$content = file_get_contents($path);
			$this->paths[$path] = $content;
		}
		$content = $this->conditionalBlocks($content,$replacements);
		$content = $this->replace($content, $replacements);

		return $content;
	}
	
	/*
		Compiles template into PHP file
	*/
	public function compilePHP($ar) {
		
		ob_start(); // eval needs to get captured in a string - regular $message = eval assignment won't work.
		eval('?>' . file_get_contents($ar['template_path']) . '<?');
		$message = ob_get_contents();
		ob_end_clean();
		echo $message;
	}
	

	function parseTemplate($ar) {
		$template = $ar['template_path'];
		$params = $ar['replacements'];
		
		foreach ($params as $k=>$v) {
		 $$k = $v;
		}
		ob_start();
		/*	  eval("?>" . implode("", file($template)) . "<?");
		$c = ob_get_contents();
		ob_end_flush();*/
		eval('?>' . file_get_contents($template) . '<?');
		$message = ob_get_contents();
		ob_end_clean();
		return $message;
	}

	
	/*
		Assign replacement values.
		
		$mode - either MODE_CONSTANTS or MODE_VARS - mode constants load user constants defined in config.php.
		Defaults to MODE_VARS
	*/
	public function assign($replacements, $mode=ROCKETS_HTML_KillerTemplate::MODE_VARS) {
		foreach($replacements as $key => $value) {
			//if($mode == ROCKETS_HTML_KillerTemplate::MODE_CONSTANTS) $this->replacements["[{" .$key ."}]"] = $value;
			//else $this->replacements[$key] = $value;
			$this->replacements[$key] = $value;
		}	
	}
	
	
	
	private function conditionalBlocks($text) {
		//$regexp = "\[IF\](.*?)\[ENDIF\]";
		$regexp = "\[IF (.*?)\](.*?)\[ENDIF\]";
		preg_match_all("/$regexp/i",$text,$matches);
		if(count($matches[0])==0) return $text; // no match
		
		echo "CONDITIONAL BLOG BEGIN";
		print_r($matches);
		// turn code into PHP here
		
		//foreach($matches as $match) {
			$originalText = $matches[0][0];
			echo "Original text: {$originalText}<br>";
			
			$this->phpReplace($matches[0][1]);
			
			//$php = preg_replace("/\[[^{]/i","<?php",$originalText);
			//echo "PHP: {$php}<br>";
		//}
		
		echo "CONDITIONAL BLOCK END";
		return $text;
		
/*		$str = "if({$this->replace($matches[1][0])}) return true;";
		echo $str ."<br>";
		if(eval($str)) $text = str_replace($matches[0][0],$matches[2][0],$text);
		return $text;*/
		
/*		foreach($matches as $match) {
			
		}*/
	}
	
	/*
		replace variable names in IF / ELSE blocks
		For example, [IF $AGE>10] would turn into [IF $age>10]
	*/
	private function phpReplace($condition) {
		$regexp = "($[a-zA-Z]+)";
		preg_match_all("/$regexp/i",$condition,$matches);
		print_r($matches);
	}
	
	/*
		Replace placeholders
		For example, [{AGE}] would turn into "10" if AGE was set to 10
	*/
	private function replace($text) {
		foreach($this->replacements as $key => $value) {
			$text = str_replace("[[" .$key ."]]",$value,$text);
		}	
		return $text;
	}

}

?>