<?php

/**
 * ROCKETS_HTML_Cron is used to generate HTML output of CPU time and peak usage.
 *
 * @param boolean $ar['debug'] - set to false to turn off feedback.
 * @param pointer $ar['cron'] - REQUIRED - pointer to a ROCKETS_ADMIN_Cron object, e.g. &$cron
 * @param int $ar['mode'] - display mode, either MODE_TIME_ELAPSED or MODE_TIME_DIFF
 * @param int $ar['precision'] - precision (i.e. 4 would return values like .0001. 5 would return .00012)
 * @param boolean $ar['suppression'] - if true, suppress feedback on code blocks that takes 0 seconds (based on precision)
 * @param boolean $ar['invis'] - if true, wrap in HTML comment
 * @param string $ar['color'] - text color, defaults to 'white'
 *
 * @author Halfdeck
 * @version 1.0
 */
class ROCKETS_HTML_Cron extends ROCKETS_ConfigurableObject {

	/** Pointer to a ROCKETS_ADMIN_Cron object */
	protected $cron;

	/** Display mode - either shows time elapsed or time elapsed since the last code block */
	protected $mode;

	/**
	 * @param int Float output precision - used to cut off numbers where the value is negligible.
	 * Default value is 10
	 */
	protected $precision = 10;

	/**
	 *
	 * @var boolean Suppress 0 time values, so you can focus on code blocks that are taking significant amount of time.
	 */
	protected $suppression = false;

	/**
	 *
	 * @var boolean if true, wrap in comments code: <!-- ... -->
	 */
	protected $invis = false;

	/**
	 *
	 * @var boolean if true, mem usage information is shown. Default to true.
	 */
	protected $show_mem_usage = true;

	/**
	 * @var text color
	 */
	protected $color = "white";

	/**
	 * @var string CSS Styling of the feedback message
	 */
	protected $css_style = "font-family:arial;font-size:11px";

	const MODE_TIME_ELAPSED = 0;
	const MODE_TIME_DIFF = 1;
	/** Display both time elapsed and time difference */
	const MODE_TIME_DUAL = 2;

	public function __construct($ar = null)
	{
		$this->mode = self::MODE_TIME_ELAPSED;
		if (array_key_exists('cron', $ar))
		{
			$this->cron = &$ar['cron'];
		}
		else
		{
			die("ERROR: ROCKETS_HTML_Cron requires 'cron' variable in the initalization array.<br>");
		}
		if (isset($ar['mode']))
			$this->mode = $ar['mode'];
		if (isset($ar['precision']))
			$this->precision = $ar['precision'];
		if (isset($ar['suppression']))
			$this->suppression = $ar['suppression'];
		if (isset($ar['invis']))
			$this->invis = $ar['invis'];
		if (isset($ar['show_mem_usage']))
			$this->show_mem_usage = $ar['show_mem_usage'];
		if (isset($ar['color']))
			$this->color = $ar['color'];
		parent::__construct($ar);
	}

	/**
	 * Cron display function. Assume time, peak usage, and msg header all exist.
	 * @param string $msg page section text e.g. "header"
	 * @param pointer pointer to a ROCKETS_ADMIN_Cron object
	 * @param int $ar['memUsage'] peak memory usage
	 */
	public function show($msg)
	{
		$html = "";
		
		if (self::$DEBUG)
		{
			if ($this->invis)
				$html .= "<!--";
			else
				$html .= "<span style='color:{$this->color};{$this->css_style}'>";

			$html .= "[{$msg}] ";
			$diff = number_format($this->cron->getInterim(), $this->precision);

			if ($this->mode == self::MODE_TIME_ELAPSED)
			{
				$html .= "Execution time: " . number_format($this->cron->getTime(), $this->precision) . " seconds\n ";
				if ($this->show_mem_usage)
					$html .= "Memory Usage: " . $this->cron->getPeakUsage() . " bytes\n";
			}
			else if ($this->mode == self::MODE_TIME_DIFF)
			{
				if ($diff > 0 || $this->suppression == false)
				{
					$html .= "Time Diff: {$diff} seconds\n ";
					if ($this->show_mem_usage)
						$html .= "Memory Usage: " . $this->cron->getPeakUsage() . " bytes\n";
				}
			} else
			{
				if ($diff > 0 || $this->suppression == false)
				{
					$html .= "Execution time: " . number_format($this->cron->getTime(), $this->precision) . " seconds\n "
					. "Time Diff: {$diff} seconds\n ";
					if ($this->show_mem_usage)
						$html .= "Memory Usage: " . $this->cron->getPeakUsage() . " bytes\n";
				}
			}

			if ($this->invis)
				$html .= "-->";
			else
				$html .= "</span><br>";
		}
		
		return $html;
	}

}

?>
