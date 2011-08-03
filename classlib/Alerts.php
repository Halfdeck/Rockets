<?php

/*
 * Email Alerts / newsletter class - defines commonly shared functions that sends emails alerts.
 * Alerts is a special kind of emails. So it *should* be built on top of an email class object.
 *
 * This abstract class can be used to generate email blasts, saved search emails, etc.
 *
 * Required MYSQL tables: mailing list table, product database
 *
 * Basic steps:
 *
 * 1. Generate a list of emails (e.g. save searches, wish list alerts)
 * 2. for each email, create an email (generating a property list if necessary)
 * 3. send email, update cron time.
 *
 * @todo [MVC] to make this class usable for Email a Friend alerts, make getMailingList accept an array, instead of a MYSQL results object. To
 * do that, create a MYSQLTable method that transforms $result arrays into $arrays. Then modify the Alerts code to handle arrays instead.
 * That adds flexibility to this class.
 */

abstract class ROCKETS_Alerts extends ROCKETS_ConfigurableObject {

    /**
     * @global EMAIL_ADMIN
     */
    
    /**
     * Set to true to activate cron job time limit
     * @var boolean 
     */
    private static $UPDATE_CRONTIME = true;

    /**
     * Send copy of an email to webmaster (for debugging)
     * @var boolean
     */
    private static $SEND_COPY_TO_ADMIN = true;
    /**
     * filename of template file. Define this in the extension class.
     * @var string
     */
    protected $templateFilename = null;

    public function __construct($ar = null) {
	if (array_key_exists("updateCronTime", $ar))
	    self::$UPDATE_CRONTIME = $ar['updateCronTime'];
	if (array_key_exists("sendCopyToAdmin", $ar))
	    self::$SEND_COPY_TO_ADMIN = $ar['sendCopyToAdmin'];
	parent::__construct($ar);

    }

    /*
     * Generate an email list. How this is implemented depends on the extension Class
     *
     * returns a list of MYSQL rows in an ARRAY, containing email items.
     */
    abstract protected function getMailingList($ar = null);

    abstract protected function generateResults($row);
    abstract protected function updateCronTimestamp($row);

    /**
     * <p>Sends out alerts based on a mailing list and a product database.<p>
     * @param array $ar
     * @return <type>
     */
    public function sendAlerts($ar = null) {

	$cron = new ROCKETS_ADMIN_Cron();

	$c = 0; // counter

	$resultArray = $this->getMailingList(); // get mailing list

	if(count($resultArray)==0) {
	    trigger_error("Empty mailing list returned!");
	    return null;
	}

	foreach ($resultArray as $row) { // repeat for each email recipient...
	    self::checkCounter(&$c); // if counter limit is set, abort when limit is reached
	    $cron->checkScriptTime(); // if cron script time limit is set and is reached, abort

	    $pResult = $this->generateResults($row); // get product items / content for this person

	    if (mysql_num_rows($pResult) == 0) {
		if (self::$DEBUG)
		    echo "No results!";
		continue; // try the next person on the mailing list
	    }
	    else {
		$emailContents = $this->constructEmailBody(array(
			    "productResults" => $pResult, // product listing
			    "contactData" => $row // a person's contact information
			));
		/**
		 * Send email
		 */
		self::email($emailContent);

		/**
		 * Cron job timestamp is used to prevent double-sending to the same person if
		 * the alert cron job is done in multiple passes.
		 */
		if (!self::$DEBUG && self::$UPDATE_CRONTIME) { // disable this feature during testing
		    self::updateCronTime($row); // update cron time so email doesn't go out repeatedly
		}

		/**
		 * Send copy to admin
		 */
		if (self::$SEND_COPY_TO_ADMIN) {
		    $emailContent['email'] = EMAIL_ADMIN; // send a slightly modified copy to Admin
		    $emailContent['subject'] = $vars["subject"]["admin"];

		    self::email($emailContent);
		}

		ROCKETS_HTML_Alerts::displayEmail($emailContent); // just echo the message to see result
	    }
	}
    }

 

    private function email($ar = null) {
	if(!self::$DEBUG && self::$EXECUTE) { // disable this feature during testing
	    mail($ar['email'],$ar["subject"],$ar['message'],$ar['header']);
	}
    }

    /**
     * Construct an email message and send it.
     * @param array $ar['contactData'] Format of the data depends on the template being used.
     * @param MYSQLresult $ar['productResults']
     * @return array email body
     */
    private function constructEmailBody($ar = null) {

	/**
	 * Construct email
	 */
	$row = $ar['contactData']; // contact data
	$pResult = $ar['productResults']; // list of products

	$message = ROCKETS_HTML_Alerts::getMessage();
	$header = ROCKETS_HTML_Alerts::getHeader();

	/////////////////////

	$emailContent = array( // email lead
	    "email" => $row['email'],
	    "subject" => $vars['subject']['member'],
	    "message" => $message,
	    "header" => $header
	);

	return $emailContent;
    }
}

?>
