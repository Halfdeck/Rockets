<?php

/**
 * File class, used to do things like read from file and write to file
 *
 * $ar['deleteAfterMove'] - if true, deletes file after move
 */
class ROCKETS_ADMIN_File extends ROCKETS_ConfigurableObject
{
    /** traversal method */
    const METHOD_MOVE_FILES = 1;

    /** show directories within a directory */
    const METHOD_SCAN_DIRECTORIES = 2;

    /** delete file after move - default: false */
    protected static $deleteAfterMove = false;

    /**
     * File class, used to do things like read from file and write to file
     * File is listed under ADMIN because its not intended to run when a user visits a page.
     *
     * @param <type> $ar['deleteAfterMove'] (optional) if set, deletes files after move.
     */
    public function __construct($ar = null)
    {
        if (isset($ar['deleteAfterMove']))
            self::$deleteAfterMove = $ar['deleteAfterMove'];
        parent::__construct($ar);
    }

    /**
     * Given a directory, loop through each file and process using a callback function.
     *
     * @param string $dir directory name
     * @param callback $callback
     * @param array $arg additional arguments to send to callback function
     */
    public static function parseFilesInDirCallback($dir, $callback, $arg=NULL)
    {

        if (!is_callable($callback))
            trigger_error(__METHOD__ . " requires a callable function in \$callback", E_USER_ERROR);
        $dir_handle = @opendir($dir) or trigger_error(__METHOD__ . ": unable to open " . $dir, E_USER_ERROR);

        self::echoDebug("Directory Listing of " . $dir . "<br/>");

        /**
         * Counter
         */
        $c = 0;

        while ($file = readdir($dir_handle)) {
            if (is_dir($file))
            {
                /**
                 * Directory, not a file, so skip
                 */
                continue;
            }

            self::echoDebug("PROCESSING $file<br>" . PHP_EOL);

            call_user_func($callback, $file, array(
                "args" => $arg,
                "dir" => $dir));
        }
        closedir($dir_handle);
    }

    /**
     * Read a file line by line.
     * You can apply a callback to each line by sending it in $callback
     *
     * @param string $path file pathj
     * @param <type> $callback
     * @param <type> $callbackData - any extra data to be fed into callback
     *
     * @todo warning: this function will NOT behave correctly without a callback function.
     */
    public static function readLineByLine($path, $callback = NULL, $callbackData = NULL)
    {

        flush();
        $fh = fopen($path, "r");

        while (!feof($fh)) {
            $buffer = fgets($fh);
            if (is_callable($callback))
                call_user_func($callback, $buffer, $callbackData);
        }
        fclose($fh);
    }

    /**
     * Read from file
     *
     * @param string $ar['file'] filepath
     * @param string $ar['callback'] optional callback
     * @return <type>
     */
    public function read($ar = null)
    {
        self::checkArrayParam($ar, __METHOD__, array("file"));

        if (self::$DEBUG)
            echo $ar['file'];
        $fp = fopen($ar['file'], "r");
        self::errorHandler($fp);
        $content = "";

        while ($buffer = fread($fp, 1024)) {
            $content .= $buffer;
        }
        fclose($fp);
        return $content;
    }

    /**
     * Write to file
     *
     * @param <type> $ar['targetPath'] path of file to write to
     * @param string $ar['content'] content to write
     */
    public function write($ar = null)
    {
        $fp = fopen($ar['targetPath'], "w");
        self::errorHandler($fp);
        fwrite($fp, $ar['content'], strlen($ar['content']));
        fclose($fp);
    }

    /**
     * Delete files in a directory
     *
     * @param String $path Directory path
     */
    function deleteDirectoryFiles($path)
    {
        $ar['callback'] = array("ROCKETS_ADMIN_File", "delete");
        $ar['path'] = $path;
        if (self::$DEBUG)
            echo "<h2>Deleting files from path: {$path}</h2>\r\n";
        self::traverseDirCallback($ar);
    }

    /**
     * Delete a file
     * @param string $ar['path']
     * @param string $ar['file']
     */
    public function delete($ar)
    {
        if (!is_dir($ar['path'] . $ar['file']))
        {
            unlink($ar['path'] . $ar['file']);
            if (self::$DEBUG)
                echo "deleted {$ar['path']}{$ar['file']}<br>";
        }
        else if (self::$DEBUG)
            echo "looks like {$ar['path']}{$ar['file']} is a directory, skipping...<br>";
    }

    /**
     * Make directory
     * 
     * 	 * WARNING: Do not send paths that end with / - that'll trigger an error
     * 
     * @param <type> $path - name of path to create
     * @return <type> true if success, false if failed or directory already exists
     */
    static public function makeDir($path)
    {
        if (!is_dir($path))
            return mkdir($path);
        else
            return false;
    }

    /**
     * Scan a directory for a list of subdirectories.
     * @param <type> $ar
     */
    public function scanForDirectories($ar)
    {
        $ar['callback'] = array("ROCKETS_ADMIN_File", "isDir");

        $this->traverseDirCallback($ar);
    }

    /**
     * Get a list of files in a directory.
     * @param <type> $directory
     * @return <type>
     */
    public static function getDirectoryList($directory)
    {

        // create an array to hold directory list
        $results = array();

        // create a handler for the directory
        $handler = opendir($directory);

        // open directory and walk through the filenames
        while ($file = readdir($handler)) {

            // if file isn't this directory or its parent, add it to the results
            if ($file != "." && $file != "..")
            {
                $results[] = $file;
            }
        }

        // tidy up: close the handler
        closedir($handler);

        // done!
        return $results;
    }

    /**
     * Function that traverses through a directory using a callback function.
     *
     * <p>Callback was used so we can write multiple functions that injected code inside
     * the while loop. </p>
     * @param <type> $ar['path']
     * @param <type> $ar['file']
     * @param <type> $ar['targetDir']
     */
    protected function traverseDirCallback($ar)
    {

        if (self::$DEBUG)
        {
            $limit = 10;
            echo "<h2>TraverseDirCallback called</h2>";
        }
        $c = 0;

        $cron = new ROCKETS_ADMIN_Cron();

        $dir_handle = @opendir($ar['path']) or die("Unable to open " . $ar['path']);
        while ($file = readdir($dir_handle)) {
            // if(self::$DEBUG && $c > $limit) break;
            //echo $c ." $file<br>";
            $ar['file'] = $file;

            if (is_callable($ar['callback']))
            {
                $ret = call_user_func($ar['callback'], $ar);
                if ($ret)
                {
                    $c++;
                    if (self::$DEBUG)
                        echo " $c ";
                }
            }
            else
                echo "callback not callable!<br>";

            if (self::$TIMERSET)
                $cron->checkScriptTime();
        }
        closedir($dir_handle);
    }

    /**
     * Check if a path is a directory
     * @param <type> $ar['path'] Path
     * @param <type> $ar['file'] file
     * @return bool returns true if its a directory; returns false otherwise
     */
    public function isDir($ar)
    {
        if (is_dir($ar['path'] . $ar['file']))
        {
            echo "Is DIR: {$ar['path']}{$ar['file']}<br>\r\n";
            return true;
        }
        return false;
    }

    /**
     * Check if a path+filename is a valid file.
     * @param <type> $ar['path'] Path
     * @param <type> $ar['file'] file
     * @return boolean
     */
    public function isFile($ar)
    {
        if (is_dir($ar['path'] . $ar['file']))
        {
            echo "Is File: {$ar['path']}{$ar['file']}<br>\r\n";
            return true;
        }
        return false;
    }

    /**
     * Check if file exists
     * @param string $ar["targetDir"] target directory
     * @param string $ar['file'] filename
     * @return bool true if file exists, false if otherwise
     */
    public function fileExists($ar)
    {
        if (file_exists($ar["targetDir"] . $ar['file']))
        {
            if (self::$DEBUG)
                echo "<strong>FILE EXISTS - {$ar["targetDir"]}{$ar['file']}<br></strong>\r\n";
            return true; // file already exists
        }
        else
            return false;
    }

    /**
     * Wrapper View function for getDirectorySize
     *
     * @todo [MVC] this View function HAS To get moved to a View class.
     *
     * @param <type> $path
     */
    public function getDirectoryStats($path)
    {
        $result = self::getDirectorySize($path);
        echo "<h2>Directory Information for <u>{$path}</u></h2>";
        echo "<b>Directory size:</b> " . self::sizeFormat($result['size']) . "<br>";
        echo "<b>Files:</b> {$result['count']}<br><b>Directories:</b> {$result['dircount']}<br>";
    }

    /**
     * Get directory size (recursive)
     *
     * <p>Another possible implementation:<br><br>
     *
     * $result=explode("\t",exec("du -hs ".$path),2);<br>
     * $result[1]==$path ? $result[0] : "error";<br>
     * echo $result[1];<br>
     * </p>
     * @param <type> $path
     * @return array (totalsize, totalcount, dircount)
     */
    private function getDirectorySize($path)
    {
        $totalsize = 0;
        $totalcount = 0;
        $dircount = 0;
        $fp = opendir($path);
        if ($fp)
        {
            while ($file = readdir($fp)) {
                $nextpath = $path . '/' . $file;
                if ($file != '.' && $file != '..' && !is_link($nextpath))
                {
                    if (is_dir($nextpath))
                    {
                        $dircount++;
                        $result = self::getDirectorySize($nextpath);
                        $totalsize += $result['size'];
                        $totalcount += $result['count'];
                        $dircount += $result['dircount'];
                    } elseif (is_file($nextpath))
                    {
                        $totalsize += filesize($nextpath);
                        $totalcount++;
                    }
                }
            }
        }
        closedir($fp);
        $total['size'] = $totalsize;
        $total['count'] = $totalcount;
        $total['dircount'] = $dircount;
        return $total;
    }

    /**
     * Given a number of bytes, returns a string signifying filesize
     *
     * @todo [MVC] this function is more Numerical than File-related. Move it.
     * 
     * @param int $size Number of bytes
     * @return string file size
     */
    private function sizeFormat($size)
    {
        if ($size < 1024)
        {
            return $size . " bytes";
        } else if ($size < (1024 * 1024))
        {
            $size = round($size / 1024, 1);
            return $size . " KB";
        } else if ($size < (1024 * 1024 * 1024))
        {
            $size = round($size / (1024 * 1024), 1);
            return $size . " MB";
        } else
        {
            $size = round($size / (1024 * 1024 * 1024), 1);
            return $size . " GB";
        }
    }
	
	/**
	 * Clean up filename so it doesn't break anything
	 * 
	 * @param type $filename
	 * @return type 
	 */
	static public function sanitize_filename($filename)
	{
		/**
		 * remove #, which breaks urls when someone wants to load it in a browser.
		 * example: .../uploads/65233 proof_#34.pdf -> browser truncates to /uploads/65233 proof_
		 */
		$filename = str_replace("#"," ",$filename);
		/**
		 * Remove starting/trailing spaces
		 */
		$filename = trim($filename);
		return $filename;
	}
}

?>