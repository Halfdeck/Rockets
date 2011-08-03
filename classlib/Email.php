<?php

/**
 * An Email object, used to send emails.
 *
 * An email is made up of: 1) header 2) body 3) destination email address 4) subject line
 * 2) body is further divided into content / signiture
 * 3) content has several data elements, including Contact details and product results(optional) or a customized automated message
 * using customer data.
 *
 * @author Halfdeck
 */
abstract class ROCKETS_Email extends ROCKETS_ConfigurableObject {

    /**
     * Email "To:" address
     * @var string
     */
    protected $to_addr;

    /**
     * From email address
     * @var String 
     */
    protected $addr_from;
    /**
     * Reply-to email address. The reply-to address sometimes may be different
     * from the From address, e.g. when a person with email xyz@yahoo.com registers
     * and the registration alert email goes to web admin from abc@yahoo.com, who
     * wants the reply email to go to xyz@yahoo.com instead of abc@yahoo.com
     * 
     * @var String 
     */
    protected $addr_reply;
    /**
     * full internal path to the email message template file
     * @var String 
     */
    protected $templateFilePath;
    /**
     * full internal path to sig templat file
     * @var String 
     */
   // private $sigFilePath;

    protected $msgTitle;
    protected $headerFromName;
    /**
     * data to fill email template besides $_REQUEST e.g. MYSQL result
     * @var <type>
     */
    protected $data = null;
    /**
     * Send copy to admin - default: true
     * @var <type> 
     */
    protected $sendCopyToAdmin = true;

    /**
     * Admin email address
     */
    const EMAIL_ADMIN = EMAIL_ADMIN;

    /**
     * <p>
     * Array parameter template:
     * <br>
     * array(<br>
     *	"templateFilePath"=>"",
     *  "addr_reply"=>"",
     *  "addr_from"=>"",
     *  "to_addr"=>"",
     *  "msgTitle"=>"",
     *  "headerFromName"=>"",
     *  "data" => "",
     *  "sendCopyToAdmin" => ""
     * )
     * </p>
     */
    public function __construct(array $ar = null) {

	$this->loadRequest();
	
	if($ar['data']) {
	    ROCKETS_ADMIN_Publisher::setGlobal($ar['data']);
	}

	parent::__construct($ar);
    }

    public function  __get($name) {
    }

    /**
     * prevent any external tampering of property vales
     * @param <type> $name
     * @param <type> $value
     * @return <type>
     */
    public function  __set($name, $value) {
	return false;
    }
    /**
     * Get email address - how this is implemented depends
     * on who is getting emailed
     */
    //abstract protected function getEmailAddress();

    /**
     * Get Subject line
     */
    //abstract protected function getSubject();

    /**
     * Contruct email header.
     * This is the default header constructor. Other header formats are possible
     * depending on type of email being sent out.
     * @param array $ar
     * @return string
     */
    protected function getHeader($ar = null) {
	$header = "From: {$this->headerFromName} <{$this->addr_from}>" .PHP_EOL
		. "Reply-To: {$this->addr_reply}" .PHP_EOL
		. "Content-Type: text/html" .PHP_EOL;
	return $header;
    }

    /**
     * Get email body using a Publisher eval()
     */
    private function getBody($ar = null) {
	return ROCKETS_ADMIN_Publisher::patch($this->templateFilePath);
    }

    /**
     * Send email
     * @param <type> $ar
     */
    private function email($ar = array(null)) {
	mail($ar['email'], $ar["subject"], $ar['body'], $ar['header']);
    }

    /**
     * Display email message on the web, for testing purposes
     * @param <type> $ar
     */
    private function displayEmail($ar = array(null)) {
	echo "<hr>";
	echo "<strong>Email Address:</strong> {$ar['email']}<br>";
	echo "<strong>Subject:</strong> {$ar['subject']}<br>";
	echo "<strong>header:</strong> {$ar['header']}<br>";
	echo "<hr>{$ar['body']}<br>";
    }

    /**
     * Create email sig using a sig template and a MYSQL row.
     * @param MYSQL row $row
     * @return String sig HTML
     */
    protected function createSig($row) {
	return ROCKETS_ADMIN_Publisher::patch($this->sigFilePath);
    }

    /**
     * Main function, sends mail given parameters.
     * This method assumes one email may use a set of MYSQL results .. other alternatives are possible.
     * @param array $ar['emailData'] MYSQL results row containing recipient info (email address, name, etc)
     * @param MYSQLResults $ar['results'] MYSQL results containing results for a single recipient
     */
    public function sendEmail() {

	/**
	 * Stuff content in an array so it can be re-rendered for testing in self::displayEmail();
	 */
	$emailContent = array(
	    "email" => $this->to_addr,
	    "subject" => $this->msgTitle,
	    "body" => $this->getBody(),
	    "header" => $this->getHeader()
	);

	if (self::$EXECUTE)
	    self::email($emailContent);
	else {
	    trigger_error("Execution deactivated.");
	}

	if (self::$DEBUG)
	    self::displayEmail($emailContent); // just echo the message to see result

	if ($this->sendCopyToAdmin) {
	    $emailContent['email'] = self::EMAIL_ADMIN;
	    $emailContent['subject'] = "Admin Copy: " .$emailContent['subject'];
	    self::email($emailContent); // this gets sent even if execution is deactivated.
	}
	else {
	    echo "NO ADMIN COPY SENT";
	}
    }

    /**
     * <p>Get validation URL. Email validation URLs are constructed by URL_BASE
     * (e.g. http://www.example.com) + validation URL (e.g. /validate.php?) +unique
     * validation string</p>
     * 
     * @return string
     */
    protected function getValidationURL($validation_url_fragment) {
	return URL_BASE .$validation_url_fragment .self::getValidationString();
    }

    /**
     * Get unique email validation code
     * @return string
     */
    public static function getValidationString() {
	$seed = time();
	return md5($seed);
    }

}

?>