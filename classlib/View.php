<?php

/**
 * ROCKETS_View - base View class, used primarily to print class properties
 * 
 * ROCKETS_View doesn't extend ROCKETS_ConfigureObject because doing so makes auto-loading
 * property values very difficult.
 * 
 *
 * @author Halfdeck
 */
class ROCKETS_View extends ROCKETS_MVC
{
	/**
	 * @todo fix mixed use of $row and $GLOBALS['row']
	 */

		/**
	 * Pointer to this object's Model object
	 * @var JOB_EXTENSION_TwoPassMYSQL
	 */
	public $model = null;
	public $parent = null;

	/**
	 * Property name that triggers Count()
	 * @todo translate this into a method
	 */
	const STR_COUNT = 'count';
	/**
	 * Property name that triggers SUM(COUNT(*))
	 * @todo translate this into a method
	 */
	const STR_SUMCOUNT = 'sumcount';
	/**
	 * Use MYSQL group_concat() to return multiple items in a list string, e.g.
	 * $x->Role->groupstring => 'admin,sales rep,coordinator'
	 * @todo translate this into a method
	 */
	const STR_GROUPCONCAT = 'groupstring';
	
	const GLOBAL_KEY_ROW = 'row';

	/**
	 * Array of counts - used when result includes a count(*) of mysql data 
	 * and in cases where there are multiple counts from the same mysql table
	 * 
	 * example: If we're asking for counts of data files, invoices, and proofs from a job's uploads table, all those
	 * counts would be stored in $this->counts array
	 * 
	 * @note named _ar to avoid collision with method name ->count() or with possible database tablename called "counts"
	 * 
	 * @var type 
	 */
	public $counts_ar = array();

	/**
	 * group_concat() result string that will be displayed
	 * 
	 * @var type 
	 */
	public $group_concat_str = "";
	static $row = null;

	public function __construct(ROCKETS_View $parent = null)
	{
		global $row;
		$row = (isset($GLOBALS[self::GLOBAL_KEY_ROW])) ? $GLOBALS[self::GLOBAL_KEY_ROW] : null;
		if (BOOL_DEBUG)
			echo "Im born!" . get_class($this) . "<br>";
		/**
		 * Save parent
		 */
		$this->parent = $parent;
		$this->set_model($parent);

		/**
		 * Create query if we're on our first pass
		 */
		if (empty($row) && $this->model != NULL)
		{
			$this->model->create_query();
		}
	}

	public function set_row($row)
	{
		if (isset($row))
		{
			$this->row = $row;
			print_r($row);
		}
	}

	/**
	 * Retrieves count value
	 * 
	 * @global type $row
	 * @param type $ar
	 * @return type 
	 */
	public function count($ar = array(null))
	{

		if (isset($GLOBALS[self::GLOBAL_KEY_ROW]))
		{
			/**
			 * If $ar is set, get specified data instead of generic count
			 * This method assumes $ar contains only one pair
			 * 
			 * e.g. if $ar = array("file_type" => "data_file"), then
			 * return $this>counts_ar('data_file') instead of $this->count;
			 */
			if ($this->count)
				return $this->count;
			else if (!empty($ar))
			{
				/**
				 * usage: $x->User->count(array("company_id" => 2)),
				 * then $key = "company_id" and $value = 2
				 */
				list($key, $value) = each($ar); // assume $ar contains only one pair

				return $this->counts_ar[$value];
			}
		}
		/**
		 * If @global $row isn't set, we assume it's first pass, so we create
		 * queries instead of retrieving values
		 */
		else
		{
			return $this->model->count($ar);
		}
	}

	/**
	 * Use MYSQL group_concat() to return multiple items in a list string, e.g.
	 * $x->Role->group_concat('name') => 'admin,sales rep,coordinator'
	 *
	 * @global type $row
	 * @param type $fieldname
	 * @return type 
	 */
	public function group_concat($fieldname)
	{
		global $row;

		if (isset($row))
		{
			if ($this->group_concat_str)
				return $this->group_concat_str;
			else
				return null;
		}
		/**
		 * If @global $row isn't set, we assume it's first pass, so we create
		 * queries instead of retrieving values
		 */
		else
		{
			return $this->model->group_concat($fieldname);
		}
	}

	protected function get_model_name_from_view_classname()
	{
		$view_classname = get_class($this);
		return str_replace(self::PREFIX_VIEW, self::PREFIX_MODEL, $view_classname);
	}

	/**
	 * VALID name is if the model name doesn't equal the View name
	 * @notes Not sure what this is checking.. but it works!?
	 * 
	 * @param string $model_classname
	 * @return type 
	 */
	protected function is_model_classname_valid($model_classname)
	{
		if ($model_classname == get_class($this))
			return false;
		else
			return true;
	}

	/**
	 * Create a reference to the Model, so we can retrieve relational data
	 * when creating the model, we also track the parent model
	 * 
	 * @param type $name 
	 */
	public function set_model(ROCKETS_View $parent = null)
	{
		$classname = $this->get_model_name_from_view_classname();

		if ($this->is_model_classname_valid($classname))
		{
			if ($parent)
			{
				$model = $parent->model;
			}

			else
				$model = null;

			$this->model = new $classname();
			$this->model->set_parent($model);
		}
	}

	public function __get($name)
	{
		/**
		 * if this class object/property already set, return it.
		 */
		if (!empty($this->$name))
		{
			return $this->$name;
		}

		/**
		 * construct class object, if class exists
		 */
		$classname = PREFIX_APPLICATION_CLASSLIB .self::PREFIX_VIEW . "{$name}";

		if (class_exists($classname))
		{
			$this->$name = new $classname($this);
			$this->$name = $this->fill_result($this->$name);

			return $this->$name;
		}
		else if ($name == self::STR_COUNT)
		{
			$this->model->create_select_count(ROCKETS_Model::MODE_COUNT);
		}
		else if ($name == self::STR_SUMCOUNT)
		{
			$this->model->create_select_count(ROCKETS_Model::MODE_SUMCOUNT);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Fill View object with MYSQL result
	 * 
	 * @global type $row
	 * @param ROCKETS_View $o
	 * @return ROCKETS_View 
	 */
	public function fill_result(ROCKETS_View $o)
	{
		/**
		 * BETA - these variables should not be global. they should be class properties set on Construct
		 */
		global $row; // results row

		if (empty($row))
		{
			return $o; // do nothing
		}

		$fields = ROCKETS_MYSQL_TwoPass::$fieldsMeta;

		/**
		 * Translate $row into class properties
		 * We use $this->alias to translate - currently there's no fallback to $this->tablename
		 */
		$length = count($fields);
		for ($i = 0; $i < $length; $i++)
		{
			$fieldvalue = $row[$i];
			/**
			 * When values come from multiple fields, the primary table values will be "user_id","zip_code"..
			 * While secondary table values will be "tablename_user_id", "tablename_zip_code"...
			 */
			if ($fields[$i]['table'] == $o->model->alias)
			{
				$fieldname = $fields[$i]['name'];
				$o->$fieldname = $fieldvalue;
			}
		}
		/**
		 * if {$alias}_count has a value, assign that too. We don't retrieve this value the "normal"
		 * way because mysql doesn't assign a table to this value
		 * 
		 * count fields are returned, aliased in the form {alias}_count_{fieldname}, where fieldname is the
		 * one used in the where clause.
		 */
		/**
		 * Construct the count(*) alias used in mysql statement
		 */
		$count_alias = ROCKETS_Model::construct_alias($o->model, ROCKETS_Model::MODE_COUNT);
		$group_concat_alias = ROCKETS_Model::construct_alias($o->model, ROCKETS_Model::MODE_GROUPCONCAT);

		/**
		 * create an array of counts that match the alias
		 * This may be an array IF there are multiple counts from the same table
		 */
		$count_ar = self::get_partial_array_key_match($row, $count_alias);
		$groupconcat_ar = self::get_partial_array_key_match($row, $group_concat_alias);

		/**
		 * $count_alias e.g. "jobs_notes_count" 
		 */
		foreach ($count_ar as $item)
		{
			list($mysql_alias, $field_value) = each($item);

			$key = self::get_count_fieldname($o->model, $mysql_alias);

			$o->counts_ar[$key] = $field_value;
		}

		if (isset($row[$count_alias]))
		{
			$o->count = $row[$count_alias];
		}

		/**
		 * 
		 * Group concat
		 * 
		 * @todo abstract this
		 */
		if (isset($row[$group_concat_alias]))
		{
			$o->group_concat_str = $row[$group_concat_alias];
		}

		return $o;
	}

	/**
	 * Usage: $x->User->Role->is_member('sales rep') => true
	 * 
	 * $fieldname defaults to the group_concat_str property generated using ->group_concat()
	 * 
	 * @warning: Must run group_concat() for is_member() to work
	 * 
	 * @param type $fieldname
	 * @param type $value
	 * @return type 
	 */
	public function is_member($value, $fieldname = 'group_concat_str')
	{
		if (strstr($this->$fieldname, $value))
			return true;
		else
			return false;
	}

	/**
	 * Get fieldname used in the Element Data - used when value is sent in 
	 * Used for $this->fill_result
	 * 
	 * Example: "uploads_count_data_file" => "data_file"
	 * 
	 * @param type $table_alias
	 * @param type $mysql_alias
	 * @return type 
	 */
	static protected function get_count_fieldname(ROCKETS_Model $model, $mysql_alias, $mode = ROCKETS_Model::MODE_COUNT)
	{

		$mode_str = ROCKETS_Model::get_mode_string($mode);

		if (isset($model->parent))
			$replacement = "{$model->parent->alias}_{$model->alias}_{$mode_str}_";
		else
			$replacement = "{$model->alias}_{$mode_str}_";

		return str_replace($replacement, "", $mysql_alias);
	}

	/**
	 * Given an array and a key string, find key => value pairs where
	 * the key partially matches $key_target
	 * 
	 * This method is useful when MYSQL returns a set of results that we need
	 * to bind to a MODEL object and the fieldname isn't in the tablename schema (e.g. because it's aliased)
	 * 
	 * Used in $this->fill_result();
	 * 
	 * @param array $ar
	 * @param type $key_target
	 * @return type 
	 */
	static protected function get_partial_array_key_match(Array $ar, $key_target)
	{
		$result = array();

		foreach ($ar as $key => $value)
		{

			if (strstr($key, $key_target))
			{
				$result[] = array($key => $value);
			}
		}
		return $result;
	}

    /**
     * Allows me to set protected property
     * This method is necessary when loading protected property values from MYSQL
     * 
     * @param <type> $name
     * @param <type> $value
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * Get the Model class name of an HTML class. For example,
     * if this class is JOB_HTML_Letter, then it returns JOB_MODEL_Letter
     * We use this to run $this->loadObject
     * 
     * @return type 
     */
    static protected function getModelClassName()
    {
        $thisclass = get_called_class();
        $class = str_replace("HTML", "MODEL", $thisclass);
        return $class;
    }
	
	/**
	 * Takes an array and associates with a View class object. For example,
	 * given an array('id'=>20, 'name'=>'John Smith'...),
	 * $o = ...HTML_User::load_array($row) will create $o, and $o->name will
	 * return 'John Smith.'. Purpose of this method is to be able to apply
	 * transmuters to raw data, so $o->birth_date will return a formatted
	 * date.
	 */
	static public function load_array(Array $row) 
	{
		$classname = get_called_class();
		$o = new $classname;
		foreach($row as $key => $value) {
			$o->$key = $value;
		}
		return $o;
	}

    /**
     * <p>Load record and populate properties.
     * For example, given a JOB_HTML_Letter class, return an JOB_HTML_Letter
     * object with pre-filled properties based on $id and data retrieved via 
     * JOB_MODEL_Letter. The actual handling of properties are in __get(),
     * so the template page isn't littered with extraneous code.
     * 
     * <b>Usage</b>
     * <code>
     * $user = JOB_HTML_User::load_object($id);
     * echo $user->full_name;
     * </code>
     * </p>
     * 
     * @param <type> $id
     * @return object if $id is null, return a blank HTML object; otherwise, return the HTML object with values filled in
     */
    static public function load_object($id)
    {
        if($id == null) {
			$classname = get_class();
			return new $classname;
		}
        /**
         * Find the MODEL classname for an HTML class object
         */
        $classname = self::getModelClassName();
        /**
         * Create a new MODEL object, to retrieve data
         */
        $o = new $classname;

        /**
         * Get a record, using $id
         * this is a JOB_MYSQLTable method
         */
        $result = $o->get_record_by_id($id);
		if(!$result || mysql_num_rows($result) == 0) {
			$classname = get_class();
			return new $classname;
		}
        /**
         * Return the record wrapped in this HTML class, so properties can be manipulated
         */
        return mysql_fetch_object($result, get_called_class());
    }
	
	/**
	 * Function that converts numbers into money format
	 * 
	 * @param type $name
	 * @return type 
	 */
	public function money($name)
	{
		if(empty($this->$name))
		{
			return null;
		}
		else {
			return ROCKETS_Number::getMoney($this->$name, true);
		}
	}
	
	public function number($name)
	{
		if($this->$name == 0) return null;
		else {
			return ROCKETS_Number::numberFormat($this->$name);
		}
	}
	
	/**
	 *
	 * @param type $name
	 * @param type $format ROCKETS_Date::FRMT_...
	 * @return type 
	 */
	public function formatted_date($name, $format)
	{
		return ROCKETS_Date::createDateStrFromMYSQL($this->$name, $format);
	}
}

?>
