<?php
/* 
 * Presentation component for ROCKETS_Alerts
 * @category View class
 */

class ROCKETS_HTML_Alerts {

    /**
     * @global EMAIL_ADMIN
     * @global PATH_PARTS
     */

    /**
     *
     * @var string
     */

    private static $admin_email = EMAIL_ADMIN;
    /**
     * get email header string
     * @param <type> $ar
     * @return string
     */
    public static function getHeader($ar = null) {
	$header = "From: " .self::$admin_email ."\r\n"
	    ."Reply-To: " .self::$admin_email ."\r\n"
	    ."Content-Type: text/html\r\n";
	return $header;
    }
    /**
     * Display email data on a webpage for testing purposes.
     * @param string $ar['email']
     * @param string $ar['subject']
     * @param string $ar['header']
     * @param string $ar['message']
     */
    public static function displayEmail($ar = array(null)) {
	echo "<hr>";
	echo "<strong>Email Address:</strong> {$ar['email']}<br>";
	echo "<strong>Subject:</strong> {$ar['subject']}<br>";
	echo "<strong>header:</strong> {$ar['header']}<br>";
	echo "<strong>message:</strong><hr>{$ar['message']}<br>";
    }

    /**
     * Create sig from agent data
     *
     * $ar contains a mysql row
     */
    public static function createSig($row) {
	return ROCKETS_ADMIN_Publisher::patch(PATH_PARTS ."/email-templates/sig.php");
    }

    /**
     * construct email message using a template.
     * @param <type> $ar
     * @return <type>
     */
    public static function getMessage($ar = null) {
	return ROCKETS_ADMIN_Publisher::patch(PATH_PARTS ."/email-templates/" .$this->templateFilename);
    }
}

?>
