<?php

/**
 * HTACCESS Publisher
 * 
 */
class ROCKETS_ADMIN_HTACCESS extends ROCKETS_ADMIN_Publisher {

    /**
     * @global USER_AGENT_DEV
     * @global FILE_503
     * @global FILE_404
     * @global RPATH_HTPASSWORD
     * @global RPATH_TEMPLATES_HTACCESS
     */

    /**
     *
     * @var string
     * User Agent string used to access the site when site is under maintenance.
     */
    public static $devUserAgent = USER_AGENT_DEV;
    /**
     *
     * @var array filetypes (e.g. js, png, gif...)
     */
    protected static $fileTypes = array('js', 'png', 'gif', 'css', 'jpg');
    public static $file_503 = null;

    /** Template paths */
    const FILE_503 = FILE_503,
	FILE_404 = FILE_404,
	PATH_HTPASSWORD = RPATH_HTPASSWORD,
	PATH_HTACCESS = RPATH_TEMPLATES_HTACCESS;
    
    /**
     * HTACCESS object
     *
     * <p>This is used to patch/publish .htaccess files. Publishing is necessary when dealing with client installations and you want
     * the installation to go quickly and smoothly without a lot of manual modifications
     * to the .htaccess file.</p>
     *
     * <p>This extends the ROCKETS_ADMIN_Publisher class, which is used to publish TOS information</p>
     * 
     * @param string $ar['devUserAgent'] user agent string, used while 503 is up.
     * @param string $ar['path_503'] path to 503 template file.
     */
    function __construct($ar = null) {
	if(isset($ar['devUserAgent'])) self::$devUserAgent = $ar['devUserAgent'];
	else trigger_error('Specify $ar[\'devUserAgent\'] when instantiating ROCKETS_HTACCESS', E_USER_ERROR);
	
	if(isset($ar['file_503'])) self::$file_503 = $ar['file_503'];
	else trigger_error('Specify $ar[\'file_503\'] when instantiating ROCKETS_HTACCESS', E_USER_ERROR);
    }

    /**
     * gets script's document root
     *
     * @param <type> $ar
     * @return <type> returns the document root string
     */
    public function getDocRoot($ar = null) {
	return $_SERVER['DOCUMENT_ROOT'];
    }

    /**
     * returns Cache Control .htaccess rules
     * 
     * @param <type> $ar
     * @return string cache control .htaccess rules
     */
    public function cacheControl($ar = null) {
	$weeks = 3;
	$filestr = "";
	$c = 0;

	foreach(self::$fileTypes as $type) {
	    $c++;
	    if($c>1) $filestr .= "|";
	    $filestr .= $type;
	}

	$html = "<FilesMatch \"\\.({$filestr})$\">" .self::$CR
	    ."ExpiresActive On" .self::$CR
	    ."ExpiresDefault \"access plus {$weeks} weeks\"" .self::$CR
	    ."Header append Cache-Control must-revalidate" .self::$CR
	    ."</FilesMatch>" .self::$CR;
	return $html;
    }

    /**
     * Disable ETags for faster page loads.
     * 
     * @param <type> $ar
     * @return <type> HTACCESS rule for disabling ETags
     */
    public function disableETags($ar = null) {
	$html = "Header unset ETag" .self::$CR .
	    "FileETag None" .self::$CR;
	return $html;
    }

    /**
     * get .htpassword path
     *
     * <p>This function is a shorthand</p>
     * 
     * @param <type> $ar
     * @return <type>
     */
    public function getHtpasswordPath($ar = null) {
	 return self::getDocRoot() .self::PATH_HTPASSWORD;
    }
}

?>
