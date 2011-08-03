<?php

/**
 * Google Geocoder abstract class.
 * @version 1.0
 */
abstract class ROCKETS_GoogleGeocoder extends IDX_MODEL_IDX {

    /**
     * @global GMAP_KEY
     */

    const GOOGLE_USLEEP = 200000, // number of milliseconds to wait - to avoid hitting Google too hard and getting IP banned
	GOOGLE_DELAY = 100000, // delay if we get 620 error, in milliseconds (100000 = 100 seconds)
	GOOGLE_MAX_DELAY = 700000, // max delay - if script exceeds this, it dies.
	GOOGLE_FLD_OFFSET_STATUS_CODE = 0,
	GOOGLE_FLD_OFFSET_LATITUDE = 2,
	GOOGLE_FLD_OFFSET_LONGITUDE = 3,
	GOOGLE_GMAP_KEY = GMAP_KEY,
	STATUS_CODE_TOO_FAST = 620; // Gmaps geocoder error code - slow down

    public static $DEBUG = false,
	$EXECUTE = true, // set this to FALSE to prevent mysql writes
	$TIMERSET = true,
	$GOOGLE_DELAY = 0; // set to true to turn on debugging
    protected $tbl; /** @abstract override this tablename field **/

    /**
     * Google Geocoder
     *
     * <p>Generic Google Geocoder abstract class. </p>
     *
     * @param <type> $ar['timerSet'] if false, script runs indefinitely
     * @param boolean $ar['execute'] if false, script doesn't write to MYSQL. Default to FALSE
     * @param boolean $ar['debug'] if true, script displays feedback on the screen
     *
     * @return <type>
     */
    function __construct($ar = null) {
	if(!$ar) return null;
	else {
	    if(array_key_exists("debug", $ar)) self::$DEBUG = $ar['debug'];
	    if(array_key_exists("execute", $ar)) self::$EXECUTE = $ar['execute'];
	    if(array_key_exists("timerSet", $ar)) self::$TIMERSET = $ar['timerSet'];
	}
	return true;
    }

    /**
     * get addresses to query Google with. This is abstract because the MYSQL table structure is application-dependent.
     * @param <type> $ar
     * @return returns a MYSQL result data
     */
    abstract protected function getAddresses($ar = null);

    /**
     * get MYSQL query that updates database with lat/lng info.
     * This is abstract because MYSQL table structure is application-dependent.
     *
     * @param <type> $ar
     * @return query string
     */
    abstract protected function getUpdateQuery($ar = null);

    /**
     * Get geocode URL, like http://maps.google.com/maps/geo?q=.....
     * This version requests CSV output.
     *
     * @param string $ar['address'] Address to geocode
     * @return string returns the geocode URL
     */
    protected function getGeocodeURL($ar = null) {
	$address = $ar['address'];
	$address = self::cleanPropertyAddress($address);

	if(self::$DEBUG) echo "<strong>Address:</strong> {$address}<br>";

	// get geolocation using address

	$geocodeURL = "http://maps.google.com/maps/geo?q=" .$address ."&output=csv&sensor=false&key=" .GMAP_KEY;
	if(self::$DEBUG) echo $geocodeURL ."<br>";
	return $geocodeURL;
    }

    /**
     * Given geocode URL, get data from Google.
     * Data format is CSV.
     *
     * @param string $ar['geocodeURL']
     * @return array returns an array of geocode data.
     */
    protected function getData($ar = null) {
	$s = file_get_contents($ar['geocodeURL']);
	if(self::$DEBUG) echo "{$ar['counter']}) {$ar['address']} {$s}<br>";
	$data = explode(",",$s);
	return $data;
    }

    /**
     * Cleans address to send to gmaps geocoder
     * This function is specific to Google geocoder.
     * @param string $address
     * @return string Returns a cleaned up address string.
     */
    private function cleanPropertyAddress($address) {
	$address = preg_replace("/\((.*?)\)/"," ",$address); // get rid of stuff in parenthesis, like "(private lane)"
	$address = str_replace(array(" "),"+",$address); // replace space with + (part of url encoding)
	$address = str_replace("#","",$address); // get rid of #
	$address = str_replace("&","",$address); // get rid of &
	$address = preg_replace("/[+]+/","+",$address); // reduce multiple ++ down to a single +
	return $address;
    }

    /*
     * Check if too many hits were sent to Google (they have a query/day limit). If true, kill the script.
     */
    private function expiredGoogleDelay($ar = null) {
	
	self::$GOOGLE_DELAY += self::GOOGLE_DELAY;
	usleep(self::$GOOGLE_DELAY);
	if(self::$DEBUG) echo "<h4>TOO FAST - waiting " .self::$GOOGLE_DELAY ." milliseconds</h4>";
	if(self::$GOOGLE_DELAY > self::GOOGLE_MAX_DELAY) {
	    die("You sent too many hits to Google - Try again later<br>");
	}
    }

   /**
    * Run this function to activate cron job. This is the main function of this class.
    * 
    * @param <type> $ar
    * @return <type>
    */
    public function cron($ar = null) {

	$cron = new ROCKETS_ADMIN_Cron;
	$c = 0; // address counter
	
	$result = $this->getAddresses();

	if(self::$DEBUG) {
	    $this->countRows($verbose = true); // display number of addresses to process
	}

	if(!$result || mysql_num_rows($result)==0) return null;

	while($row = mysql_fetch_assoc($result)) {
	    $c++;
	    if(self::$DEBUG) print_r($row);

	    $geocodeURL = self::getGeocodeURL(array("address"=>$row['address']));

	    usleep(self::GOOGLE_USLEEP); // regular delay
	    self::$GOOGLE_DELAY = 0;
	    while(true) {
		if(self::$TIMERSET) $cron->checkScriptTime();
		$data = self::getData(array(
			"geocodeURL"=>$geocodeURL,
			"counter" => $c,
			"address" => $row['address']
		));

		if($data[self::GOOGLE_FLD_OFFSET_STATUS_CODE]==self::STATUS_CODE_TOO_FAST) {
		    self::expiredGoogleDelay();
		}
		else break; // received valid status code so break out of loop
	    }

	    if($data[self::GOOGLE_FLD_OFFSET_LATITUDE]!=0) {
		$query = $this->getUpdateQuery(array(
			"googleResponse" => $data,
			"row" => $row
		));
		if(self::$EXECUTE) ROCKETS_MYSQLTable::exec($query); // update lat,lng
	    }
	}
    }

}

?>
