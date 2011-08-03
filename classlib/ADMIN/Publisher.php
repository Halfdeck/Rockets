<?php

/**
 * Used to take a template file and publish it (for example, an .htaccess template file).
 */
class ROCKETS_ADMIN_Publisher extends ROCKETS_ADMIN_File {

    /**
     *
     * @var string Carriage return string
     */
    protected static $CR = "\r\n";

    /**
     * <p>ROCKETS_ADMIN_Publisher
     *
     * Used to take a template file and publish it (for example, an .htaccess template file).
     *
     * INSTRUCTIONS
     *
     * Set debug/execute info since this is a ConfigurableObject</p>
     *
     * @param <type> $ar
     */
    function __construct($ar = null) {
	parent::__construct($ar);
    }

    /**
     * Publish file. Compile path and target paths are configurable
     *
     * @param string $ar['compile_path'] path of the template file to be used to generate content
     * @param string $ar['target_path'] target path (e.g. PATH_ROOT)
     */
    public function publish($ar = null) {

	if (!isset($ar['compile_path']) || !isset($ar['target_path'])) {
	    die("ROCKETS_ADMIN_Publisher:publish requires compile_path and target_path");
	}

	$content = self::patch($ar['compile_path']);
	$this->write(array(
	    'targetPath' => $ar['target_path'],
	    'content' => $content
	));
    }

    /**
     * Use EVAL to process included data
     * Set $GLOBALS['publisher_row'] to feed data into templates. You can access it via $row variable.
     *
     * @param <type> $path
     * @param array $v Global variable used during patching - in templates, use $v to access data.
     * @return string returns content to publish
     */
    public static function patch($path, $v=null) {
	if(isset($GLOBALS['publisher_row'])) $row = $GLOBALS['publisher_row'];
	ob_start(); // eval needs to get captured in a string - regular $message = eval assignment won't work.
	eval('?>' . file_get_contents($path) . '<?');
	$s = ob_get_contents();
	ob_end_clean();
	return $s;
    }

    /**
     * Set global var, so a publisher template has access to data.
     * @param <type> $row
     */
    public static function setGlobal($row) {
	$GLOBALS['publisher_row'] = $row;
    }

    /**
     * <p>Mass replace placeholders in a template with real values. For example,
     * massReplace(array("{firstName}"=>'John',"{lastName}=>'Hancock',"template"=>'Hi {firstName}, ...))
     * will return 'Hi John,...'</p>
     *
     * @param string $ar['template'] Template text
     * @return string modified template text
     */
    public static function massReplace($template, $ar) {
	foreach ($ar as $key => $value) {
	    $template = str_replace($key, $value, $template);
	}
	return $template;
    }

}

?>
