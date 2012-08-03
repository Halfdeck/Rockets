<?php

/**
 * Used to manage cron job related functions. Also useful when timing scripts.
 *
 * @param boolean $ar['timerSet'] true activates cron job timer
 * @param int cronMaxSeconds number of seconds before cron job dies
 * @version 1.0
 */
class ROCKETS_ADMIN_Cron extends ROCKETS_ConfigurableObject {

    /**
     * @global CRON_MAX_SECONDS
     */

    /** true/false: set this to TRUE to activate cron job timer */
    private static $timerSet = true;
    /** number of seconds before cron job dies */
    private static $cronMaxSeconds = CRON_MAX_SECONDS;
    /** $time_start is the execution time when this class was instantiated. */
    private $time_start;
    /** Timer bookmark - used for calculating time used in sections of a page */
    private $time_last;
    
    public function __construct($ar = null) {
	$this->time_start = microtime(true); // time PHP execution;
	$this->time_last = $this->time_start;

	if ($ar) {
	    if (array_key_exists("timerSet", $ar))
		$this->timerSet = $ar['timerSet'];
	    if (array_key_exists("cronMaxSeconds", $ar))
		$this->cronMaxSeconds = $ar['cronMaxSeconds'];
	}
	parent::__construct($ar);
    }

    /**
     * Execution time must not exceed $cronMaxSeconds seconds, else the script is forced to die().
     *
     */
    public function checkScriptTime() {
	$time = $this->getTime();
	if($time > self::$cronMaxSeconds) {
	    die("Script ran $time seconds - now terminating<br>");
	}
    }

    /**
     * Get the time elapsed since this class object was instantiated.
     * @return float Number of CPU seconds elapsed since script started.
     * @version 1.0
     */
    public function getTime() {
	$time_end = microtime(true);
	$time = $time_end - $this->time_start;
	return $time;
    }

    /**
     * Get the time spent in sections of code
     * @return float CPU time
     */
    public function getInterim() {
	$time_end = microtime(true);
	$time = $time_end - $this->time_last;

	$this->time_last = $time_end; // remember the last time
	return $time;
    }

    /**
	 * Get peak usage
	 * @return float peak usage
	 */
	public function getPeakUsage($options = array())
	{
		if(isset($options['raw']) && $options['raw'] == true)
		{
			return memory_get_peak_usage(true);
		}
		else {
			return number_format(memory_get_peak_usage(true));
		}
	}
}

?>
