<?php

/*
 * Generic FTP Class
 *
 * Limitations
 *
 * - Downloads files from the root directory
 *
 * @param <type> $ar['server'] FTP Server
 * @param <type> $ar['username'] FTP username
 * @param <type> $ar['password'] FTP password
 * @param <type> $ar['dir_downloadParent'] download directory
 * @param <type> $ar['cronTimer'] if true, script dies after timer runs out
 * @param <type> $ar['download_mode'] either one pass or multi pass
 */

abstract class ROCKETS_FTP extends ROCKETS_ConfigurableObject {

    /**
     * @global FTP_SERVER
     * @global FTP_USER
     * @global FTP_PASS
     * @global PATH_FTP_PARENT_ZIP_FOLDER
     * @global CRON_MAX_SECONDS
     */

    /** FTP server name **/
    protected $server = FTP_SERVER;
    /** FTP username **/
    protected $username = FTP_USER;
    /** FTP password */
    protected $password = FTP_PASS;
    /** Download Directory **/
    protected $dir_downloadParent = PATH_FTP_PARENT_ZIP_FOLDER;
    /** if true, script times out after X seconds */
    protected $cronTimer = true;
    /** download mode - either MODE_ONEPASS or MODE_MULTIPASS */
    protected $download_mode = 2;
    /** download file with one shot */
    /** FTP directory - set this with a child class */
    protected $FTPDir;
    const MODE_ONEPASS = 1;
    /** download file in bits and pieces */
    const MODE_MULTIPASS = 2;
    /** Max number of seconds this script is allowed to run */
    const CRON_LIMIT = CRON_MAX_SECONDS;

    public function __construct($ar = null) {
	parent::__construct($ar);
    }
    
    /**
     * Connect to FTP
     * 
     * @return int Connection ID 
     */
    private function connect($ar = null) {
	$conn_id = ftp_connect($ar['server']) or die("Couldn't connect to {$ar['server']}");
	if (@ftp_login($conn_id, $ar['username'], $ar['password']) && self::$DEBUG) echo "Connected to FTP Server<br><br>";
	return $conn_id;
    }

    /**
     * Do an FTP download
     * @param <type> $ar
     */
    public function download() {

	$conn_id = self::connect(array( // Make an FTP connection
	    "server" => $this->server,
	    "username" => $this->username,
	    "password" => $this->password
	));
	
	$dirList = ftp_nlist($conn_id, $this->FTPDir); // FTP directory scan
	foreach($dirList as $file) {
	    /** skip directories - just process files */
	    if(is_dir($file)) continue;
	    
	    $filename = basename($file);
	    if(self::$DEBUG) echo $filename ."<br>";

	    if($this->checkFilename($filename) == FALSE) continue;

	    $path = $this->dir_downloadParent .$filename;

	    if(self::$DEBUG) echo "Downloading {$filename} into {$path}<br>\r\n";

	    switch($this->download_mode) {
		case self::MODE_ONEPASS:
		    self::oneShotDownload(array(
			"path" => $path,
			"conn_id" => &$conn_id,
			"file" => $file
		    ));
		    break;
		case self::MODE_MULTIPASS:
		    self::resumeDownload(array(
			"path" => $path,
			"conn_id" => &$conn_id,
			"file" => $file
		    ));
		    break;
		default:
		    die("No FTP download mode specified!");
		    break;
	    }
	}
	ftp_close($conn_id);
    }

    /**
     * Abstract class
     *
     * <p>Check filename to see if the file is what we want. For example, if you have files with multiple dates in their
     * filenames, then pull the one with the right date. This functionality is possibly client-specific, so its abstract.</p>
     * @param string $filename - File name
     */
    abstract protected function checkFilename($filename);

    /**
     * Download the entire file in one go.
     *
     * @param str $ar['path'] - write file path
     * @param int $ar['conn_id'] - FTP connection ID
     * @param int $ar[filename'] - FTP file path
     */
    private function oneShotDownload($ar = null) {
	$fp = fopen($ar['path'], "w");
	ftp_fget($ar['conn_id'], $fp, $ar['file'], FTP_BINARY, 0);
	fclose($fp);
    }

    /**
     * Download a file in bits and pieces - to prevent server cpu overload.
     * 
     * @param string $ar['path'] - file path on local server
     * @param int $ar['conn_id'] - FTP connection ID
     * @param string $ar['filename'] - filename
     */
    private function resumeDownload($ar = null) {

	if(self::$DEBUG) print_r($ar);
	/** Initialize cron timer */
	$cron = new ROCKETS_ADMIN_Cron();
	/** open file for append instead of write */
	$fp = fopen($ar['path'], "a");

	$ret = ftp_nb_get($ar['conn_id'], $ar['path'], $ar['file'], FTP_BINARY, filesize($ar['path']));

	while ($ret == FTP_MOREDATA) {
	    $ret = ftp_nb_continue($ar['conn_id']);
	    $cron->checkScriptTime();
	}
	if ($ret != FTP_FINISHED) {
	    die("There was an error downloading the file...");
	}
	fclose($fp);
    }
}

?>
