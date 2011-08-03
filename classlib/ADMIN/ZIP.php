<?php

/**
 * Zip file handling class
 * 
 * Currently only handles .GZ extensions
 */
class ROCKETS_ADMIN_ZIP {

    static $DEBUG = true;

    function __construct($ar = array(null)) {
	if(array_key_exists("debug", $ar)) self::$DEBUG = $ar['debug'];
    }

    /**
     * Extract ZIP files
     * @param string $ar['parentDir'] source directory
     * @param string $ar['childDir'] Target directory
     */
    public static function extractZIPs($ar = array(null)) {

	$dir = $ar["parentDir"];
	$targetDir = $ar["childDir"];

	$dir_handle = @opendir($dir) or die("Unable to open " .$dir);
	if(self::$DEBUG) echo "Directory Listing of " .$dir ."<br/>";
	$c = 0;
	while ($file = readdir($dir_handle)) {
	    if(is_dir($file)) continue;
	    if(self::$DEBUG) echo $file ."<br>";

	    self::unzip($dir.$file, $targetDir);
	    $c++;
	    if(self::$DEBUG) echo "<h1>FILE # $c - writing to directory: {$targetDir}</h1>";
	    flush();
	}

    }

    private function unzip($originalPath,$targetDir) {
	$path_parts = pathinfo($originalPath);
	$ext = $path_parts['extension'];

	if($ext == 'gz') {
	    if(self::$DEBUG) echo "Unpacking $originalPath to $targetDir";
	    $gzp = gzopen($originalPath,"r");
	    $gzfile=fopen($targetDir .$path_parts['filename'],"w");
	    while($buffer = gzread($gzp, 1024)) {
		fwrite($gzfile, $buffer);
	    }
	    fclose($gzfile);
	    gzclose($gzp);
	}
	else if($ext =='zip') {
	    if(self::$DEBUG) echo "<h1>extractiong $originalPath</h1>";
	    $zip = zip_open($originalPath);
	    if(!is_resource($zip)) {
		echo "error code " .$zip ."<br>";
		return null;
	    }
	    while($zip_entry=zip_read($zip)) {
		$zname=zip_entry_name($zip_entry);
		$zip_fs=zip_entry_filesize($zip_entry);
		if(self::$DEBUG) echo $zname ."<br>";
		if(!zip_entry_open($zip,$zip_entry,"r")) {echo "Unable to proccess file '{$zname}'";continue;}
		$zfile=fopen($targetDir .$zname,"w");
		// try to conserve memory by writing in chunks
		while($buffer = zip_entry_read($zip_entry)) {
		    fwrite($zfile, $buffer);
		}
		fclose($zfile);
		zip_entry_close($zip_entry);

	    }
	    zip_close($zip);
	}
    }
}

?>
