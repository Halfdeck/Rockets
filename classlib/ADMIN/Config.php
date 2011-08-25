<?php

/**
 * ROCKETS_ADMIN_Config object
 *
 * <p>This class is used to indirectly modify config files to minimize the need
 * for manual modifications. This class will not
 * handle modifications of calculated define values (e.g. define("SUBPATH",
 * ROOTPATH ."/subpath/") but most of those
 * are assumed unnecessary.</p>
 *
 * <p>This class extends ROCKETS_ConfigurableObject, which accepts additional
 * parameters listed below.</p>
 *
 * @param string $ar['filePath'] custom path of configuration file. It should be
 * <i>relative</i> e.g. <b>/subdir/configuration.php</b>
 * @param string $ar['execute']
 * @param string $ar['debug']
 * @param string $ar['request']
 *
 * @version 1.0
 */

class ROCKETS_ADMIN_Config extends ROCKETS_ConfigurableObject {

	/**
	 * @global PATH_ROOT
	 */

	/** Default config file path */
	private static $file_config = null;

	public function __construct($ar = null)
	{
		if (array_key_exists("filePath", $ar))
			self::$file_config = $ar['filePath'];
		else
			die("Please specify relative config file path in ar['filePath'] e.g. /subdir/path.php ");
		parent::__construct($ar);
	}

	/**
	 *
	 * <p>This function is used to modify a configuration file. The idea is to write
	 * to config file to save
	 * user modifications. Loading values to a UI is simple - just use
	 * get_defined_constants. Then to save, send the form values to code like below:
	 * </p>
	 * <br>
	 * <code>
	 * $s = array(
	 *	    "SITE_NAME" => "My Cool Real Estate Site",
	 *	    "STR_DEFAULT_TITLE" => "Homes for Sale in Vegas",
	 *	    "STATE" => "NV"
	 * );
	 * <br>
	 * ADMIN_CONFIG->modifyFile(array(
	 *    defines => $s
	 * ));
	 * </code>
	 * <br>
	 * <p>Note: This function is written to be used on an instantiated object so
	 * extra parameters like
	 * config file paths etc can be set up.</p>
	 * <p><b>Warning:</b> Use " instead of single quotes in the config file, e.g.
	 * define("CONSTANT", "value")
	 * not define('CONSTANT','value')</p>
	 * @param array $ar['defines'] constantName => value pairs
	 */
	public function modifyFile($ar)
	{
		$defines = $ar['defines'];
		$filepath = PATH_ROOT.self::$file_config;
		if (self::$DEBUG)
			echo "FILEPATH: [{$filepath}]";
		$f = new ROCKETS_ADMIN_File();

		$content = $f->read(array('file' => $filepath));

		if (self::$DEBUG)
			echo "<h2>Defines</h2>";
		foreach ($defines as $constantName => $value)
		{
			if (self::$DEBUG)
				echo "$constantName => $value<br>";
			$content = self::modifyVar(array('constantName' => $constantName, 'value' => $value, 'content' => $content));
		}
		if (self::$EXECUTE)
		{
			if (self::$DEBUG)
				echo "<p>Writing to file: {$filepath}</p>";
			$f->write(array('targetPath' => $filepath, 'content' => $content));
		}
		if (self::$DEBUG)
			echo $content;
	}

	/**
	 * Modifies content of a config file using regexp
	 *
	 * <p>This function assumes there is only one match for a particular constant
	 * name string.</p>
	 *
	 * @param string $ar['constantName'] name of constant, e.g.
	 * define(<b>"EMAIL_ADDY"</b>...
	 * @param string $ar['value'] value of constant, e.g.
	 * define("EMAIL_ADDY",<b>"xyz@yahoo.com"</b>)
	 * @return string Returns modified content of a config file
	 */
	private function modifyVar($ar)
	{

		$pattern = "(define\(\"{$ar['constantName']}\",\"(.*?)\"\))";

		$pattern = "/{$pattern}/i";
		if (self::$DEBUG)
			echo $pattern."<br>";
		$replacement = "define(\"{$ar['constantName']}\",\"{$ar['value']}\")";
		if (self::$DEBUG)
			echo "Replacing with: {$replacement}<br>";

		$ar['content'] = preg_replace($pattern, $replacement, $ar['content']);
		return $ar['content'];
	}

	/**
	 * get defined constants
	 *
	 * @param <type> $ar
	 * @return array An array of constants
	 */
	public function getDefinedConstants($ar = null)
	{
		$c = get_defined_constants(true);
		return $c['user'];
	}

}
?>
