<?php

/**
 * Fundamental MYSQL methods - A portion of ROCKETS_MYSQLTable
 *
 * @author Halfdeck
 */
class ROCKETS_MYSQL_Base {
	/**
	 * @DUPLICATE Duplicates ROCKETS_MYSQLTable
	 * 
	 * Fieldnames are all duplicates
	 * 
	 */

	/**
	 * @MOD 
	 * Referenced by View 
	 * Used to auto-generate mutli-table queries
	 * 
	 * @var type 
	 */
	public $alias;

	/** name of this table */
	protected $tbl;
	public $foreign_keys;
	public $parent;

	/**
	 * @MOD set to private to force lazy-loading during insert/delete, etc
	 *  
	 * name of primary key field */
	private $primary_key_fieldname;

	/**
	 * table META data (fieldnames)
	 * @MOD force lazy load
	 */
	private $fieldnames = array();

	/**
	 * Used to maintain primary key(s) in cases where more than one key is used
	 * @var Array
	 */
	private $primary_keys = array();

	/**
	 * An array of foreign keys.
	 * Structure
	 * [foreign_key] => array(
	 * 	 'table'=> [tablename],
	 *   'key' => [foreign_key]
	 *
	 * These keys are static so we can instantly access them without instantiating an object. Since keys of multiple tables
	 * must be looked up in a single query, creating objects could be very slow.
	 *
	 * @var <type>
	 */
	static $keys_foreign = array();

	/**
	 * @MODIFIED 
	 * prevented auto-loading
	 */
	public function __construct($ar = array(null))
	{
		
	}

	/**
	 * @DUPLICATE Duplicates ROCKETS_MYSQLTable
	 * @param type $ar 
	 */
	protected function loadQueryValues($ar = array(null))
	{
		foreach ($ar as $key => $value)
		{
			if (is_array($value))
			{
				$this->$key = $value;
			}
			else
			{
				$this->$key = strtolower($value); // URL parameters are all lower case, so form values need to be set to lower case also
			}
		}
	}

	/**
	 * @DUPLICATE Duplicates ROCKETS_MYSQLTable
	 * 
	 * A non-static version of retrieveTableFields() - for accessing primary key
	 * @param <type> $tableName
	 * @return <type>
	 */
	protected function getTableFields($tableName)
	{

		$tableFields = self::read("SHOW COLUMNS FROM {$tableName}");

		while ($field = mysql_fetch_assoc($tableFields))
		{

			$field_array[] = $field['Field'];
			if ($field['Key'] == 'PRI')
			{
				$this->primary_key_fieldname = $field['Field']; // set primary key
				$this->primary_keys[] = $field['Field'];
			}
		}
		return $field_array;
	}

	/**
	 * Retrieve result Meta data
	 * This is useful for finding out the table name of fields when
	 * you pull results from more than one table at a time.
	 * 
	 * @param <type> $result
	 * @return Array
	 */
	static public function getResultFields($result)
	{
		$ar = array();
		while ($row = mysql_fetch_field($result))
		{
			$ar[] = array(
				'name' => $row->name, // field name
				'table' => $row->table); // table name
		}
		return $ar;
	}

	/**
	 * A generic array generation code
	 * returns an associative array of key=>value pairs, given a mysql result
	 * 
	 * Example: make_array($result, "name") => array('bob','joe','mary'..)
	 * 	make_array($result, "name", "user_id") => array('12311' => 'bob', '1221' => 'joe'...)
	 * 
	 * @param type $key_name
	 * @param type $value_name
	 * @param type $result
	 * @return type 
	 */
	static public function make_array($result, $key_name, $value_name = null)
	{
		$ar = array();
		/**
		 * Reset result pointer (in case this method is called more than once)
		 */
		mysql_data_seek($result, 0);
		while ($row = mysql_fetch_array($result))
		{
			if (empty($value_name))
			{
				/**
				 * creates array(1,2,3,4,5)
				 */
				array_push($ar, $row[$key_name]);
			}
			else
			{
				/**
				 * creates associative: array("1"=>"bob","2"=>"joe"...)
				 */
				$ar[$row[$key_name]] = $row[$value_name];
			}
		}
		return $ar;
	}

	/**
	 * Modified mysql_fetch_object - that uses metadata to bind fields to properties
	 * when results come from multiple tables
	 * 
	 * @param <type> $result
	 * @param String $classname
	 * @param array $fieldsMetaData
	 * @return Object or FALSE on end of result
	 */
	static public function bindToObject($result, $classname, Array $fieldsMetaData)
	{
		/**
		 * Put results in an array.
		 * You can't use mysql_fetch_assoc here because assoc uses array key values, and if key
		 * values collide (due to pulling from multiple tables) then some values will be lost.
		 */
		$row = mysql_fetch_array($result);

		/**
		 * we're looping through each record. If mysql_fetch_array returns FALSE,
		 * we're at the end of result set, so stop.
		 */
		if ($row === false)
		{
			return false;
		}

		$length = count($fieldsMetaData);

		$o = new $classname; // instantiate a new object
		$tablename = self::constructTableNameByClassName($classname);

		/**
		 * Loop through each field => [id],[username],[first_name].....
		 */
		for ($i = 0; $i < $length; $i++)
		{
			$fieldvalue = $row[$i];
			/**
			 * When values come from multiple fields, the primary table values will be "user_id","zip_code"..
			 * While secondary table values will be "tablename_user_id", "tablename_zip_code"...
			 */
			if ($fieldsMetaData[$i]['table'] == $tablename)
			{
				$fieldname = $fieldsMetaData[$i]['name'];
			}
			else
			{
				$fieldname = $fieldsMetaData[$i]['table'] . "_" . $fieldsMetaData[$i]['name'];
			}
			$o->$fieldname = $fieldvalue;
		}
		return $o;
	}

	/**
	 * @DUPLICATE Duplicates ROCKETS_MYSQLTable
	 * 
	 * Select statement wrapper - used to generate feedback AND possibly sanitize MYSQL queries for security
	 * @param <type> $query
	 * @return <type>
	 */
	static public function read($query)
	{
		if (BOOL_DEBUG)
		{
			echo ROCKETS_String::mysql_prettify($query);
		}
		$result = mysql_query($query);
		self::issueError(array("continue" => true, "query" => $query));

		return $result;
	}

	/**
	 * @NEW
	 * 
	 * get method, used for lazy loading properties, like table METAs.
	 * Do not use this to fish for table data
	 * 
	 * @param type $name 
	 */
	public function __get($name)
	{

		switch ($name) {
			case "primary_keys":
			case "primary_key_fieldname":
				if (empty($this->fieldnames) && $this->$name == null)
				{
					$this->getTableFields($this->tbl);
					return $this->$name;
				}
				else
					return $this->$name;
				break;
			case "tbl":
				if ($this->tbl == null)
				{
					trigger_error('set MYSQL table name in $tbl', E_USER_ERROR);
				}
				else
				{
					return $this->tbl;
				}
				break;
			case "fieldnames":
				if (empty($this->fieldnames))
				{
					$this->fieldnames = $this->getTableFields($this->tbl);
					return $this->fieldnames;
				}
				else
				{
					return $this->fieldnames;
				}
				break;
			case "foreign_keys":
				return $this->$name;
				break;
			default:
				if (isset($this->$name))
					return $this->name;
				else
					return null;
				break;
		}
	}

	/**
	 * This allows child classes to set private member values
	 * 
	 * @param type $name
	 * @param type $value 
	 */
	public function __set($name, $value)
	{
		$this->$name = $value;
	}

	/**
	 * @DUPLICATE Duplicates ROCKETS_MYSQLTable
	 * 
	 * MYSQL write operation wrapper function, used to handle debugging. Write operations include
	 * INSERT, UPDATE, CREATE TABLE, etc.
	 *
	 * @param string $query - query string
	 */
	public static function exec($query)
	{
		if (BOOL_DEBUG)
			echo ROCKETS_String::mysql_prettify($query);
		if (BOOL_EXECUTE)
		{
			mysql_query($query);
			self::issueError(array("continue" => false, "query" => $query));
		}
	}

	/**
	 * Default get_array() class
	 * Return an array containing a list of items
	 * This is used primarily for building a listbox
	 *
	 * Usage: The field names should be "name" and "id" - otherwise this won't work.
	 *
	 * @return array associative array, "user_id"=>"full_name"
	 */
	public static function get_array($options = array(null))
	{
		$name = "name";
		
		/**
		 * 'max_length' option for lengthy names, to keep a listbox from getting too wide
		 */
		if(isset($options['max_length'])) {
			$name = "IF(LENGTH(name)<{$options['max_length']},name,CONCAT(LEFT(name,{$options['max_length']}),'...')) as name";
		}
		
		$result = self::read("SELECT DISTINCT {$name}, id  FROM " . self::constructTableNameByClassName() . " WHERE 1 ORDER BY name ASC");

		if (!$result || mysql_num_rows($result) == 0)
			return null;

		while ($row = mysql_fetch_assoc($result))
		{
			$ar[$row['id']] = $row['name'];
		}
		return $ar;
	}

	/**
	 * Creates a MYSQL table name, given a class name.
	 * This is used for classes calling static methods
	 *
	 * Usage: 
	 * 
	 * This method is called internally - it will NOT work if its moved to a String class, for example.
	 * 
	 * cases:
	 *
	 * MODEL_Job -> jobs
	 * MODEL_Company -> companies
	 * MODEL_PostageAccount -> postage_accounts
	 *
	 * @param $class_name (optional) class name
	 * @return type
	 */
	static protected function constructTableNameByClassName($class_name = null)
	{
		if (empty($class_name))
			$class_name = get_called_class(); // get class name "JOB_MODEL_PostageAccount"

		$parts = explode("_", $class_name);
		$table_name = $parts[count($parts) - 1]; // get the table name singular, like "PostageAccount"
		$table_name = ROCKETS_String::unCamelCase($table_name); // uncamel case it -> "Postage Account"
		$table_name = trim($table_name); // " Postage Account" -> "Postage Account";
		$table_name = str_replace(" ", "_", $table_name); // put separator -> "Postage_Account"
		$table_name = ROCKETS_String::makePlural($table_name); // make it plural -> "Postage_Accounts"
		$table_name = strtolower($table_name); // "postage_accounts"

		return $table_name;
	}

	/**
	 * 
	 * @DUPLICATE Duplicates ROCKETS_MYSQLTable
	 * Issue an error and exit
	 *
	 * @param string $ar['query'] REQUIRED - MYSQL query string
	 * @param boolean $ar['continue'] OPTIONAL - if false, die on error.
	 *
	 * $continue - if false, die.
	 */
	static protected function issueError($ar = null)
	{
		if (mysql_errno())
		{
			if (isset($ar['query']))
				echo "<p><strong>Error in query:</strong>{$ar['query']}</p>";
			echo mysql_error() . "<br>";
			if (array_key_exists("continue", $ar) && $ar['continue'] == false)
				die();
		}
	}

	/**
	 * @DUPLICATE ROCKETS_MYSQLTable
	 * @MODIFIED (simplified code)
	 * 
	 * count total number of rows returned by a MYSQL query
	 *
	 * @param
	 *
	 * $verbose - set to true to see number of rows returned by a select statement
	 */
	protected function countRows($verbose = false)
	{
		$result = mysql_fetch_assoc(self::read("SELECT FOUND_ROWS()"));
		$this->rowCount = $result['FOUND_ROWS()'];
	}

	/**
	 * Return number of returned rows
	 * 
	 * @return int number of rows in MYSQL result
	 */
	public function get_rowCount()
	{
		return $this->rowCount;
	}

	/**
	 * Alias for mysql_insert_id();
	 * @return type
	 */
	static public function getLastInsertedID()
	{
		return mysql_insert_id();
	}

}

?>
