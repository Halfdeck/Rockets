<?php

/**
 * ConfigurableObject is used to do things like turn debugging on/off, prevent MYSQL queries from execution, put a timer
 * on a script to abort after X seconds - useful for managing cron jobs with limited cpu time.
 *
 * @global BOOL_DEBUG
 * @global BOOL_EXECUTE
 */
abstract class ROCKETS_ConfigurableObject
{

    /**
     * If true, feedback will be displayed. Default: false
     * @var boolean
     */
    protected static $DEBUG = BOOL_DEBUG;
    /**
     * If false, file writes etc will be disabled. Default: false
     * @var boolean
     */
    protected static $EXECUTE = BOOL_EXECUTE;
    /**
     * Array of values that can later be processed
     * @var array
     */
    protected static $REQUEST = array();
    /** If false, script runs indefinitely
     * @var boolean */
    protected static $TIMERSET = true;
    /**
     * set this to a number to kill execution at X iterations. Useful mainly for debugging purposes
     * @var int
     */
    protected static $LIMIT = null;

    /**
     * ROCKETS_ConfigurableObject
     *
     * <p>base class that lets you configure basic variables, like DEBUG, and EXECUTE</p>
     *
     * @param boolean $ar['debug'] if true, activates debug mode: displays feedback
     * @param boolean $ar['execute'] if true, executes MYSQL queries; if false, MYSQL queries do not execute.
     * @param boolean $ar['timerset'] if true, cron job timer is set, and script dies after X seconds
     * @param Array $ar['request'] request array
     */
    function __construct($ar = array(null))
    {
        set_error_handler(array("ROCKETS_ConfigurableObject", "errorHandler"), E_ALL);
        $this->loadClassProperties($ar);

        if ($ar)
        {
            /** static properties must be set manually */
            if (array_key_exists("debug", $ar))
                self::$DEBUG = $ar['debug'];
            if (array_key_exists("execute", $ar))
                self::$EXECUTE = $ar['execute'];
            if (array_key_exists("timerset", $ar))
                self::$TIMERSET = $ar['timerset'];
            if (array_key_exists("request", $ar))
                $this->loadQueryValues($ar['request']);
        }
        $this->checkUnsetProperties();
    }

    /**
     * autoload array sent to _construct as class properties.
     * For example, $x = new CAT(array("color"=>"red")); will auto-assign $x->color = "red"
     * NOTICE: Variable names are case-sensitive
     * NOTICE: Static variables CAN'T be set using this method.
     *
     * @param array $ar
     */
    protected function loadClassProperties($ar = array(null))
    {
        //var_dump(get_object_vars(get_class($this)));
        if (!$ar)
            return false;
        foreach ($ar as $key => $value) {

            $this->$key = $value;
        }
    }

    /**
     * Load values into protected static array $REQUEST. Constant names and values will be later processed
     * using $defines.
     *
     * @param <type> $ar
     */
    private function loadQueryValues($ar = array(null))
    {
        foreach ($ar as $key => $value) {
            if (is_array($value))
                self::$REQUEST[$key] = $value;
            else
                self::$REQUEST[$key] = strtolower($value); // URL parameters are all lower case, so form values need to be set to lower case also
        }
    }

    /**
     * checks to see if any class properties are unset during instantiation. If it is, trigger an error.
     * We make sure class properties aren't null if an object is constructed to prevent methods that depend on those
     * properties from crashing.
     */
    protected function checkUnsetProperties()
    {
        $ar = get_object_vars($this);
        if (self::$DEBUG)
        {
            //echo "Object Vars:" .PHP_EOL;
            //  var_dump(get_object_vars($this));
        }
        foreach ($ar as $key => $val) {
            if (!isset($ar[$key]))
                trigger_error("{$key} not set in " . get_class($this) . ". Try setting the value in __construct method.");
        }
    }

    /**
     * Loads $_REQUEST into class as class properties. Autoloading simplifies data handling.
     * @param pointerToClassObject &$obj if null, properties will be assigned to the class itself. $obj is useful for classes that manipulates other classes
     */
    protected function loadRequest($obj=null)
    {
        if (!$obj)
            $target = &$this;
        else
            $target = &$obj;

        foreach ($_REQUEST as $key => $value) { // load query values
            $target->$key = $value;
        }
    }

    /**
     * Check counter against loop limit. If loop repeats more than X times, exit. - used mainly for debugging.
     * For example, when debugging code that loops 1000 times, halt execution at 10 loops to check behavior
     * 
     * @param pointer $c (e.g. &$c)
     */
    protected function checkCounter($c)
    {
        $c++;
        if (self::$LIMIT && $c > self::$LIMIT)
            die("counter exceeded " . self::$LIMIT);
    }

    /**
     * Error handler
     * 
     * prints a backtrace of errors
     * 
     * @param <type> $errno
     * @param <type> $errstr
     * @param <type> $errfile
     * @param <type> $errline
     */
    public function errorHandler()
    {
		/**
		 * changelog
		 * 
		 * Modified to print a readable debug backtrace
		 * @todo use a template to encapsulate this
		 * @todo the debug backtrace filepath could be installation specific ?!
		 * @todo committing this will break other apps until their template file is set up correctly.
		 */
		
		$ar = debug_backtrace();
		/**
		 * Capture error output
		 */
		ob_start();
		eval('?>' . file_get_contents(PATH_TEMPLATES ."/" .FILE_TEMPLATE_DEBUG_BACKTRACE) . '<?');
		$return_str = ob_get_contents();
		ob_clean();
		
		$return_str .= print_r($GLOBALS, true); // append environment
		mail(EMAIL_ADMIN, "Script Error on " .$_SERVER['HTTP_HOST'] ." " .$_SERVER['QUERY_STRING'], $return_str);
		//die($return_str);
    }

    /**
     * Checks array parameters to a class method to make sure required fields exist.
     * When sending an array to a method, it becomes hard to figure out which parameters are available,
     * and which ones are required for the method to run. This method makes sure that at least
     * the required parameters are present. Otherwise it triggers an error.
     *
     * @param Array $ar a list of actual parameters sent to the method
     * @param <type> $requiredParamKeys a list of parameter names that are required
     * @param string $methodName usually __METHOD__ used only to display the method that requires the parameters
     *
     * @version 1.0 
     */
    protected static function checkArrayParam($ar, $methodName, $requiredParamKeys)
    {
        foreach ($requiredParamKeys as $key) {
            if (!isset($ar[$key]))
                trigger_error($methodName . " requires \$ar['{$key}']", E_USER_ERROR);
        }
    }

    /**
     * Echo debug message if DEBUG mode is on
     *
     * @param <type> $message 
     */
    protected static function echoDebug($message)
    {
        if (self::$DEBUG)
            echo $message;
    }

}

?>