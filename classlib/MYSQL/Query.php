<?php

/**
 * Description of MYSQLQuery
 * Basic query-generation-related methods
 *
 * @author Halfdeck
 */
class ROCKETS_MYSQL_Query extends ROCKETS_MYSQL_Base {
	/**
	 * @DUPLICATE ROCKETS_MYSQLTable
	 * 
	 * BETWEEN query type
	 * e.g WHERE usertable.age BETWEEN '1' AND '56'
	 */
	const QUERY_TYPE_BETWEEN = 1;
	/**
	 * @DUPLICATE ROCKETS_MYSQLTable
	 * 
	 * IN query type
	 * e.g. WHERE usertable.age IN ('1','2','4','5')
	 */
	const QUERY_TYPE_IN = 2;

	/**
	 * @DUPLICATE duplicates code from JOB_EXTENSION_MYSQLTable
	 * 
	 * Abstracted method that should apply to all Model objects
	 * Created so I don't custom code for every Model.
	 * This should also auto-create queries to retrieve relational table data
	 *
	 * @param array $ar
	 * @return type
	 */
	public function get_records(array $ar = null)
	{

		$select_clause = $this->get_select_clause($ar);
		$where_clause = $this->get_where_clause($ar);
		$sort_order = $this->get_sort_clause($ar);

		/**
		 * Aliased tbl_users as u to do a nested select, to fish out company_name
		 */
		$result = self::read("SELECT SQL_CALC_FOUND_ROWS {$select_clause}
			FROM {$this->tbl} 
			WHERE 1 {$where_clause}
			{$sort_order}");

		$this->countRows();
		return $result;
	}
	
	/**
	 * Get all records from this table
	 * 
	 * @return type 
	 */
	public function get_all_records() {
		return self::read("SELECT * FROM {$this->tbl}");
	}

	/**
	 * @PACKAGE JOB_EXTENSION_MYSQLTable
	 * Returns a record from mysql by ID -
	 * 
	 * Usage: $object = mysql_fetch_object($this->loadPropertiesByID, "View_Class_name");
	 * 
	 * @param type $id
	 */
	public function get_record_by_id($id = null)
	{
		if ($id == null)
			return false;
			
		return self::read("SELECT * FROM {$this->tbl}
			WHERE {$this->primary_key_fieldname} = '{$id}'");
	}
	
	/**
	 * Gets a record ID, given a field name and value.
	 * This method assumes that field is a unique field and returns one result
	 * 
	 * @param type $field_name
	 * @param type $value 
	 */
	public function get_primary_value_by_unique_field($field_name, $value) 
	{
		$result = self::read("SELECT {$this->primary_key_fieldname}
				FROM {$this->tbl} 
				WHERE {$field_name} = \"{$value}\"
				LIMIT 1
		");
		$row = mysql_fetch_assoc($result);
		return $row[$this->primary_key_fieldname];
	}
	
	/**
	 * Get count 
	 * e.g. Users::get_count('job_id', 4) => returns # of users associated with job # 4
	 * 
	 * @param type $field_name
	 * @param type $field_value
	 * @return type 
	 */
	static public function get_count($ar = array(null)) 
	{
		$tbl = self::constructTableNameByClassName();
		
		$where_clause = "";
		foreach($ar as $name => $value) {
			$where_clause .= " AND  {$name} = \"{$value}\"";
		}
		
		$result = self::read("SELECT count(*) as count from {$tbl} WHERE 1 {$where_clause}");
		$row = mysql_fetch_assoc($result);
		return $row['count'];
	}
	
	/**
	 * Get a single field value using ID
	 * e.g. grab a user's phone number using user ID
	 * 
	 * @param type $id
	 * @param type $field_name
	 * @return type 
	 */
	static public function get_field_value_by_id($id, $field_name, $options = array(null)) 
	{
		if($id == null) return null;
		
		$options['id_name'] = (isset($options['id_name'])) ? $options['id_name'] : "id";
		
		$tbl = self::constructTableNameByClassName();
		
		$result = self::read("SELECT {$field_name} FROM {$tbl} WHERE {$options['id_name']}={$id} LIMIT 1");
		if($result && mysql_num_rows($result)>0) {
			$row = mysql_fetch_assoc($result);
			return $row[$field_name];
		}
		else return null;
	}
	
	public function get_limit_clause($ar = array(null)) 
	{
		$clause = "";
		if(!isset($ar['limit']) || !isset($ar['limit']['max_results'])) return null;
		else {
			if(isset($ar['limit']['start'])) {
				$clause .= "LIMIT " .($ar['limit']['start'] * $ar['limit']['max_results']) .",";
			}
			else {
				$clause .= "LIMIT 0, ";
			}
			$clause .= "{$ar['limit']['max_results']}";
		}
		return $clause;
	}

	public function get_select_clause($ar = "")
	{
		$select_clause = "*"; // default select
		/**
		 * Add custom select clause
		 */
		if (isset($ar['custom_fields']))
			$select_clause .= "," . $ar['custom_fields'];
		/**
		 * Load fieldnames fro keys_foreign array
		 */
		$classname = get_called_class();
		foreach ($classname::$keys_foreign as $key)
		{
			if (isset($key['fields']))
			{
				foreach ($key['fields'] as $field_name => $alias_name)
				{
					/**
					 * Alias is used, if it exists. If not, use default table name.
					 * This creates: SELECT ..., table.fieldname as alias_name
					 */
					$alias = (!empty($key['alias'])) ? $key['alias'] : $key['table'];
					$select_clause .= ",{$alias}.{$field_name} as {$alias_name}";
				}
			}
		}
		return $select_clause;
	}

	/**
	 * @DUPLICATE ROCKETS_MYSQLTable
	 * 
	 * Construct WHERE clause using arrays
	 * @param type $ar
	 * @return string
	 */
	public function get_where_clause($ar)
	{
		$where_clause = "";

		if (BOOL_DEBUG)
			ROCKETS_String::echo_array_formatted($ar, "WHERE AR");


		foreach ($ar['conditionset'] as $conditionset)
		{
			if (BOOL_DEBUG)
				ROCKETS_String::echo_array_formatted($conditionset, "Conditionset");
			/**
			 * Check if $conditionset['name'] is an array in case BETWEEN statement is getting sent in
			 * if its an array !empty(.. will break.
			 */
			if (is_array($conditionset['name']) || !empty($_REQUEST[$conditionset['name']]) || isset($conditionset['checked']) || isset($conditionset['default']))
			{
				$checked = (empty($conditionset['checked'])) ? null : $conditionset['checked']; // make sure value is set in array
				/**
				 * If 'default' value is set and no dynamic value is sent in, use the
				 * default value
				 */
				if($checked == null && isset($conditionset['default']) && ROCKETS_Request::get($conditionset['name']) == NULL) {
					
					$checked = $conditionset['default'];
					//echo "generating checked<BR>{$checked}<br>";
				}

				$auto_condition = $this->auto_construct_condition($conditionset['name'], $conditionset['condition'], $checked);
				if ($auto_condition)
					$where_clause .= " AND {$auto_condition}";
			}
		}
		return $where_clause;
	}

	/**
	 * Automatically generates the ORDER BY {$clause}
	 * @param <type> $ar
	 * @return string
	 */
	public function get_sort_clause($ar)
	{
		//print_r($ar['sorter']['default']);
		/**
		 * if custom sorter is set, use that
		 */
		if (isset($ar['sorter']))
		{
			$sorter = self::auto_construct_condition('sorter');
			/**
			 * default sort clause can be set in 'default' => ...
			 */
			if (empty($sorter) AND isset($ar['sorter']['default']))
			{
				$sorter = $ar['sorter']['default'];
			}
		}

		if (empty($sorter))
		{
			return null;
			$sorter = "id ASC"; // default sorter value - order by ID
		}
		return "ORDER BY " . $sorter;
	}

	/**
	 * 
	 * @DUPLICATE ROCKETS_MYSQLTable
	 * Handles BETWEEN and IN statements
	 * @param type $filter_name String or array. Array structure:
	 * <code>
	 * $filter = array(
	 * 		'name' => array('minimum_age','maximum_age)
	 * )
	 * </code>
	 * 	like array("1","10") is used for a BETWEEN statement
	 * @param string $checked (optional) if this is set to a value, then use that instead of looking inside $_REQUEST
	 * <code>
	 * 'checked' => 1 // uses 1 instead of $_REQUEST value
	 * 'checked' => array(array(0,1),array(1,2)...) // uses array to generate BETWEEN statements separated by ORs
	 * </code>
	 * @param type $condition
	 * @return type 
	 */
	protected function auto_construct_condition($filter_name, $condition = "[[filter_value]]", $checked = null)
	{
		$str = "";

		/**
		 * Get query type
		 */
		$query_type = self::get_query_type($condition);
		/**
		 * if "checked" key is set, use that value and ignore $_REQUEST.
		 * This is useful when you want to force generate a WHERE clause even if $_REQUEST value isn't set
		 */
		if ($checked)
			$_REQUEST[$filter_name] = $checked;

		if ($query_type == self::QUERY_TYPE_BETWEEN)
		{
			if (BOOL_DEBUG)
				echo "<h1>ROCKETS BETWEEN CHECK</h1>";
			/**
			 * If $filter_name is an array, create a BETWEEEN statement
			 * for example, "name" => array("max_income","min_income")
			 */
			if (is_array($filter_name))
			{
				if (empty($_REQUEST[$filter_name[0]]) || empty($_REQUEST[$filter_name[1]]))
					return null;

				if (BOOL_DEBUG)
				{
					echo "REQUEST 0: [{$_REQUEST[$filter_name[0]]}]" . PHP_EOL;
					echo "REQUEST 0: [{$_REQUEST[$filter_name[1]]}]" . PHP_EOL;
				}
				$str = "'{$_REQUEST[$filter_name[0]]}' AND '{$_REQUEST[$filter_name[1]]}'";
			}
			/**
			 * The values are in $_REQUEST
			 * e.g. $_REQUEST['state_ranges'] = array(array(1,2),array(2,3)...)
			 */
			else if (!empty($_REQUEST[$filter_name]) && is_array($_REQUEST[$filter_name]))
			{
				$first = true;

				foreach ($_REQUEST[$filter_name] as $item)
				{
					$clause = "'{$item[0]}' AND '{$item[1]}'";

					$clause = str_replace("[[filter_value]]", $clause, $condition);

					if (!$first)
						$str .= "OR ";
					$str .= "({$clause})";
					$first = false;
				}
				/**
				 * in this case, we don't want to apply the final $str = str_replace("[[filter_value]]", $str, $condition);
				 * which creates something like  AND national_account_Asa.zip5 BETWEEN (national_account_Asa.zip5 BETWEEN
				 */
				$str = str_replace("[[table_name]]", $this->tbl, $str);
				return $str;
			}
		}

		/**
		 * $_REQUEST value is empty and nothing is "checked", so exit - don't generate a WHERE clause
		 */
		else if (empty($_REQUEST[$filter_name]))
		{
			return null;
		}
		else if ($query_type == self::QUERY_TYPE_IN)
		{
			if(BOOL_DEBUG) echo "<h1>processing in {$checked}</h1>";
			/**
			 * if filter value is an array, create an "IN (x,y,z)" mysql clause
			 * e.g. $_REQUEST['zip_codes'] = array(1,2,3,4,5....)
			 */
			if (is_array($_REQUEST[$filter_name]))
			{
				$str = "(" . ROCKETS_String::mysql_get_in_list($_REQUEST[$filter_name]) . ")";
			}
			else if(is_array($checked)) 
			{
				$str = "(" . ROCKETS_String::mysql_get_in_list($checked) . ")";
			}
			/**
			 * Single value sent in: e.g. (1)
			 */
			else if(isset($_REQUEST[$filter_name]))
			{
				$str = "({$_REQUEST[$filter_name]})";
			}
		}
		else
		{
			/**
			 * Regular query
			 */
			if ($filter_name == 'sorter')
			{
				/**
				 * Don't wrap the filter clause with quotes if sorter string is sent (e.g. companies.name ASC) 
				 * which should not be wrapped in quotes
				 */
				$str = $_REQUEST[$filter_name];
			}
			else
			{
				$str = "{$_REQUEST[$filter_name]}";
			}
		}

		$str = str_replace("[[filter_value]]", $str, $condition);
		$str = str_replace("[[table_name]]", $this->tbl, $str);
		return $str;
	}

	/**
	 * @DUPLICATE ROCKETS_MYSQLTable
	 * 
	 * Detect query type (between or in) given a condition in an conditionset, like
	 * "[[tablename]].userid BETWEEN [[filter_value]]"
	 * 
	 * @param type $condition
	 * @return type QUERY_TYPE or null if no match.
	 */
	static protected function get_query_type($condition)
	{
		if (strstr($condition, " BETWEEN "))
		{
			return self::QUERY_TYPE_BETWEEN;
		}
		else if (strstr($condition, " IN "))
		{
			return self::QUERY_TYPE_IN;
		}
		else
		{
			return null;
		}
	}

	/**
	 * @DUPLICATE Duplicates ROCKETS_MYSQLTable
	 * @MOD Changed $DEBUG to BOOL_DEBUG
	 * 
	 * <p>Magically inserts a new record, given $this->fieldnames
	 * If sending $_REQUEST, just call ->insert()
	 * 
	 * input array should contain a list of field/value pairs:
	 *  array(
	 * 	user_id => 1,
	 * 	username => john...
	 *  );<p>
	 *
	 * @param array $ar['data'] contains field/value pairs; otherwise generate_fieldValue() is used to autogenerate $ar from $_REQUEST
	 * @param array $ar['obj'] load an object (e.g. class object) instead of a data array
	 * @param array $ar['row'] automatically fetch table fieldnames and link with MYSQL row array data - data must be in exact order as in MYSQL
	 * @param array null - auto-fetch table fieldnames and link with $_REQUEST
	 *
	 * <p>Warning - if you send emtpy data, it will try to write an empty insert, which will fail.</p>
	 *
	 * @todo check for empty values and fail smoothly, so code calling this method can be dumber
	 */
	public function insert($ar = array(null))
	{

		if (isset($ar['data']))
		{
			$ar = $ar['data']; // load data array
		}
		else if (isset($ar['obj']))
		{
			$ar = $this->generate_fieldValue((array) $ar['obj']); // load an object instead of a data array
		}
		else if (isset($ar['row']))
		{
			$ar = $this->generate_fieldValue($ar['row']); // automatically fetch table fieldnames and link with MYSQL row array data
		}
		else
		{
			$ar = $this->generate_fieldValue(); // automatically fetch table fieldnames and link with $_REQUEST
		}

		$mysql_fields = "";
		$mysql_vals = "";
		$mysql_update = "";
		$c = 0; // counter for insert
		$d = 0; // counter for update

		foreach ($ar as $key => $value)
		{

			/**
			 * @MOD if empty string, push that into INSERT to overwrite previous value
			 */
//			if (!$value)
//				continue;

			$value = mysql_real_escape_string($value); // add slashes to prevent MYSQL errors

			if (BOOL_DEBUG)
				echo "[$c] KEY [{$key}] VALUE [{$value}] INDEX: [{$this->primary_key_fieldname}]<br>";

			if ($c > 0)
			{
				$mysql_fields .= ", ";
				$mysql_vals .= ", ";
			}
			if ($d > 0)
			{
				if ($key != $this->primary_key_fieldname)
				{
					$mysql_update .= ", ";
				}
			}

			$mysql_fields .= "{$key}";
			$mysql_vals .= "\"{$value}\"";
			$c++;

			if ($key != $this->primary_key_fieldname)
			{
				$mysql_update .= "{$key}=\"{$value}\"";
				$d++;
			}
		}
		$query = "INSERT INTO {$this->tbl} ({$mysql_fields}) VALUES ({$mysql_vals}) ON DUPLICATE KEY UPDATE {$mysql_update}";
		self::exec($query);
	}

	/**
	 * @OVERRIDE - extends DELETE by checking for multiple primary keys, if any
	 * 
	 * <p>Delete a record using primary index value.
	 * Usage:
	 *
	 * ->delete(array(
	 * 	'index_value'=>...
	 * ));
	 *
	 * If you just call $obj->delete() with no arguments, $_REQUEST is used. The $_REQUEST index must
	 * be the field name used in the table. For example, if the primary key is "id", then send data in $_REQUEST['id']
	 * </p>
	 * @param <type> $ar
	 * @return <type>
	 */
	public function delete($ar = array())
	{
		/**
		 * If $ar is empty, use $_REQUEST
		 * function empty() is used here because $ar is set to an array() if nothing is sent in, and it will never be null
		 */
		if (empty($ar))
		{
			/**
			 * Check if there are multiple primary keys.. in which case reference all
			 * There are cases where primary_keys array will be empty...
			 * 
			 */
			$where_clause = "";

			if ($this->primary_keys != null)
			{
				foreach ($this->primary_keys as $key)
				{
					$where_clause .= "AND {$key} = '{$_REQUEST[$key]}'";
				}
			}

			/**
			 * Default to non-array that was manually set if there's no primary key
			 */
			else
			{
				$where_clause .= "AND {$this->primary_key_fieldname}='{$_REQUEST[$this->primary_key_fieldname]}'";
			}
			self::exec("DELETE FROM {$this->tbl} WHERE 1 {$where_clause}");
			return;
		}
		else if (array_key_exists("index_value", $ar))
		{
			$index_value = $ar['index_value'];
		}

		if (!$index_value)
		{
			return false;
		}

		self::exec("DELETE FROM {$this->tbl} WHERE {$this->primary_key_fieldname}='{$index_value}'");
	}

	/**
	 * @MOD Changed scope from private to protected (since its called by MYSQLQuery)
	 * @DUPLICATE Duplicates ROCKETS_MYSQLTable
	 * 
	 * Using $this->fieldnames and $_REQUEST, generate field => value pairs array to feed to insert()
	 * @param array $obj send a class object as an array to scan instead of $_REQUEST. if $obj is null, then $_REQUEST is used.
	 * @return array an array of table-relevant fields.
	 */
	protected function generate_fieldValue($obj = null)
	{
		$ar = array();
		if (!$obj)
			$obj = $_REQUEST;

		foreach ($this->fieldnames as $field)
		{
			if (isset($obj[$field]))
				$ar[$field] = $obj[$field];
		}
		return $ar;
	}

}

?>