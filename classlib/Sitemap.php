<?php

/*
 * Generic Sitemap XML class
 *
 */

abstract class ROCKETS_Sitemap {

    /**
     * @global URL_BASE
     * @global PATH_SITEMAP_XML
     */

    /**
     *
     * @var <type>
     */

    static protected $xml = "",
	$lastMod,
	$urlbase = URL_BASE,
	$DEBUG = false,
	$freq = "daily"; // default frequency
    const path_xml = PATH_SITEMAP_XML;

    /*
     * @param
     *
     * $ar['debug'] - set to true to turn on debug mode
     */
    function __construct($ar = null) {
	self::$lastMod = date("Y-m-d"); // last modified date
	if(array_key_exists("debug", $ar)) self::$DEBUG = $ar['debug'];
    }

    /*
     * echo XML header
     */
    protected function echo_header() {
	self::$xml = "";
	self::$xml .= "<?xml version='1.0' encoding='UTF-8'?>\n";
	self::$xml .=	"<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";
    }

    /*
     * echo XML footer
     */
    protected function echo_footer() {
	self::$xml .= "</urlset>\n";
    }

    protected function write_xml() {

	if(self::$DEBUG) {
	    echo self::$xml;
	    return null;
	}
	else {
	    $fh = fopen(self::path_xml, "w");
	    fwrite($fh,self::$xml);
	    fclose($fh);
	}
    }

    /** main sitemap function **/
    abstract public function makeSitemap();

    protected function displayURL($urlbase, $lastmod, $freq, $priority) {
	self::$xml .= "	<url>\n";
	self::$xml .= "		<loc>$urlbase</loc>\n";
	self::$xml .= "		<lastmod>$lastmod</lastmod>\n";
	self::$xml .= "		<changefreq>$freq</changefreq>\n";
	self::$xml .= "		<priority>$priority</priority>\n";
	self::$xml .= "	</url>\n";
    }
}

?>
