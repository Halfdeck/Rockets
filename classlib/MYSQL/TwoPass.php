<?php

/**
 * Handles relational MYSQL using a Two-Pass method
 *
 * @author Aaron
 */
class ROCKETS_MYSQL_TwoPass extends ROCKETS_MYSQL_Query {
	/**
	 * Do a regular count(*) - used when making a new JOIN using primary key
	 */
	const MODE_COUNT = 1;

	/**
	 * Do a SUM(COUNT(*) - used when making a JOIN not using a primary key
	 */
	const MODE_SUMCOUNT = 2;

	/**
	 * Group concat
	 */
	const MODE_GROUPCONCAT = 3;

	/**
	 * $GLOBALS array key used to maintain FROM clauses
	 */
	const GLOBAL_KEY_FROM_CLAUSES = 'from_clauses';

	/**
	 * $GLOBALS key used to maintain SELECT clauses
	 */
	const GLOBAL_KEY_SELECT_CLAUSES = 'select_clauses';

	public $foreign_keys = array();
	public $parent = "";

	/**
	 * Fields meta array used to map result fields when we use multiple tables
	 * and field name collisions are possible
	 * 
	 * @var Array
	 */
	static public $fieldsMeta = array();

	/**
	 * This construct blocks normal construct operation of parent classes
	 */
	public function __construct($ar = array(null))
	{
		if (BOOL_DEBUG)
			echo "Model born!" . get_class($this) . "<bR>";
		parent::__construct($ar);
	}

	public function set_parent(ROCKETS_Model $parent = null)
	{
		$this->parent = $parent;
	}

	/**
	 * Auto-create Data Element filename used for two-pass scanning
	 */
	static private function get_eval_filename()
	{
		$return_str = self::constructTableNameByClassName() . ".php"; // 'RoleTask' => 'role_tasks.php'
		$return_str = str_replace('_', '-', $return_str); // 'role_tasks.php' => 'role-tasks.php'
		return $return_str;
	}

	/**
	 * First pass.. scan the Element Data file and load data into $GLOBALS
	 * 
	 * @param string $filename 
	 */
	static public function load_global($filename = null, $options = array(null))
	{
		if (empty($filename))
		{
			$filename = self::get_eval_filename();
		}
		if (BOOL_DEBUG)
			echo "GOT FILENAME: {$filename}<bR>";
		/**
		 * Supress output here .. just get $GLOBALS['tables'], an array of JOIN clauses
		 */
		ob_start(); // eval needs to get captured in a string - regular $message = eval assignment won't work.
		eval('?>' . file_get_contents(PATH_ELEMENTS_DATA . "/{$filename}") . '<?');
		$s = ob_get_contents();
		ob_end_clean();
	}

	/**
	 * Create FROM clause using $GLOBALS
	 * 
	 * @return string 
	 */
	public function get_from_clause_two_pass()
	{
		$from_clause = "FROM {$this->tbl} as {$this->alias}";

		foreach ($GLOBALS[self::GLOBAL_KEY_FROM_CLAUSES] as $clause)
		{
			$from_clause .= $clause;
		}
		return $from_clause;
	}

	public function get_select_clause_two_pass()
	{
		$select_clause = "*";
		foreach ($GLOBALS[self::GLOBAL_KEY_SELECT_CLAUSES] as $clause)
		{
			$select_clause .= ",{$clause}";
		}
		return $select_clause;
	}

	public function get_group_by_clause_two_pass($select_clause)
	{
		/**
		 * if select clause has a Count, you need to group by
		 */
		$group_by_clause = "";

		if ($select_clause != "*")
		{
			$group_by_clause = "GROUP BY {$this->alias}.id";
		}
		return $group_by_clause;
	}

	/**
	 * Generates a result set using a two-pass method.
	 * 
	 * @param type $ar
	 * @return type 
	 */
	public function relational_two_pass($ar = array(null))
	{
		/**
		 * GLOBALS['tables'] tracks an array of JOIN clauses
		 * Select clauses are also partially auto-generated in cases where count()
		 * need to be generated
		 */
		$GLOBALS[self::GLOBAL_KEY_FROM_CLAUSES] = array();
		$GLOBALS[self::GLOBAL_KEY_SELECT_CLAUSES] = array();

		if (isset($ar['filename']))
			$filename = $ar['filename'];
		else
			$filename = null;

		self::load_global($filename, $ar);

		$select_clause = $this->get_select_clause_two_pass();
		$from_clause = $this->get_from_clause_two_pass();
		$where_clause = $this->get_where_clause($ar);
		$group_by_clause = $this->get_group_by_clause_two_pass($select_clause);
		$sort_order = $this->get_sort_clause($ar);
		$limit_clause = $this->get_limit_clause($ar);

		/**
		 * Aliased tbl_users as u to do a nested select, to fish out company_name
		 */
		$result = self::read("SELECT SQL_CALC_FOUND_ROWS {$select_clause}
			{$from_clause}
			WHERE 1 {$where_clause}
			{$group_by_clause}
			{$sort_order}
			{$limit_clause}");

		$this->countRows();
		self::$fieldsMeta = self::getResultFields($result);

		return $result;
	}

	/**
	 * Auto-generate Count select subquery
	 * USAGE: in an element/data/ file, evoke:
	 * 
	 * $x = new JOB_TEST_TestSQL;
	 * $x->User->Invoice->count;
	 * 
	 * this will count the number of invoices associated with a user.
	 * @param int $mode either self::MODE_COUNT or MODE_SUMCOUNT 
	 */
	public function create_select_count($mode = self::MODE_COUNT)
	{
		if (isset($this->parent->foreign_keys))
		{
			$parent_fkeys = $this->parent->foreign_keys;
		}
		else
		{
			return null;
		}

		if (isset($parent_fkeys[$this->alias]))
		{
			$parent_key = $parent_fkeys[$this->alias]['key'];
			if (isset($this->parent->alias))
				$parent_alias = $this->parent->alias;
			else
				$parent_alias = $this->parent->tbl;

			$alias_clause = $this->construct_alias($this, self::MODE_COUNT, null);

			$subquery = "(SELECT count(*) 
				FROM {$this->alias} 
				WHERE {$parent_alias}.{$parent_key} = {$this->alias}.{$parent_fkeys[$this->alias]['fkey']})";

			switch ($mode) {
				case self::MODE_COUNT:
					$query = $subquery . " AS {$alias_clause}";
					break;
				case self::MODE_SUMCOUNT:
					$query = "SUM({$subquery}) AS {$alias_clause}";
					break;
				default:
					trigger_error("WRONG MODE - either MODE COUNT OR MODE SUMCOUNT", E_USER_ERROR);
					break;
			}

			if (!in_array($query, $GLOBALS[self::GLOBAL_KEY_SELECT_CLAUSES]))
			{
				$GLOBALS[self::GLOBAL_KEY_SELECT_CLAUSES][] = $query;
			}
		}
	}

	public function create_query()
	{
		if (isset($this->parent->foreign_keys))
		{
			$parent_fkeys = $this->parent->foreign_keys;
		}
		else
		{
			/**
			 * @todo the following IF clause never triggers - rewrite this to be relevant
			 */
			if (get_parent_class() == "JOB_TEST_TestSQL" && __CLASS__ != "JOB_TEST_TestSQL")
			{
				$GLOBALS[self::GLOBAL_KEY_FROM_CLAUSES][] = "FROM {$this->tbl}";
			}
			return null;
		}

		if (isset($parent_fkeys[$this->alias]))
		{
			$parent_key = $parent_fkeys[$this->alias]['key'];
			if (isset($this->parent->alias))
				$parent_alias = $this->parent->alias;
			else
				$parent_alias = $this->parent->tbl;

			/**
			 * Check for duplicate subqueries
			 */
			$subquery = " LEFT JOIN {$this->tbl} AS {$this->alias}";
			$query = " LEFT JOIN {$this->tbl} AS {$this->alias} ON {$parent_alias}.{$parent_key} = {$this->alias}.{$parent_fkeys[$this->alias]['fkey']} ";

			if (self::is_duplicate_query($subquery, $query) == FALSE)
			{

				$GLOBALS[self::GLOBAL_KEY_FROM_CLAUSES][] = $query;
			}
		}
	}

	/**
	 * Check to see if the $query we constructed is new, or is already in the query array.
	 * We check both subquery and $query. Subquery is "JOIN x as xx", Query is
	 * the whole string "JOIN x as XX ON ....."
	 * We check subquery because we can have multiple queries with identical subqueries
	 * and those queries can be reduced to one.
	 * 
	 * @param type $subquery
	 * @param type $query
	 * @return boolean true if its duplicate entry 
	 */
	static protected function is_duplicate_query($subquery, $query)
	{
		foreach ($GLOBALS[self::GLOBAL_KEY_FROM_CLAUSES] as $aquery)
		{
			if (strstr($aquery, $subquery) != FALSE)
			{
				return true;
			}
		}
		if (in_array($query, $GLOBALS[self::GLOBAL_KEY_FROM_CLAUSES]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method that deals with situations where I want to add a conditional to pull the count
	 * @NOTE This function is used when the count() generation is more complicated.
	 * 
	 * Example:
	 * 
	 * $x->Job->Upload->count(array('file_type' => 'data_file'))
	 * 
	 * would create (SELECT count(*) from uploads where file_type = 'data_file' AND uploads.job_id = jobs.id) in the SELECT clause
	 * 
	 * @global $row
	 * @param array $ar
	 */
	public function count($ar = array(null))
	{
		/**
		 * Create where clause using $ar
		 */
		$where_clause = "1 ";
		$alias_clause = "{$this->alias}_count";

		if (!empty($ar))
		{
			foreach ($ar as $fieldname => $value)
			{
				$where_clause .= " AND {$fieldname} = '{$value}'";
				$alias_clause = $this->construct_alias($this, self::MODE_COUNT, $value);
			}
		}

		if (isset($this->parent->foreign_keys))
		{
			$parent_fkeys = $this->parent->foreign_keys;
		}
		else
		{
			return null;
		}


		if (isset($parent_fkeys[$this->alias]))
		{
			$parent_key = $parent_fkeys[$this->alias]['key'];
			if (isset($this->parent->alias))
				$parent_alias = $this->parent->alias;
			else
				$parent_alias = $this->parent->tbl;

			$query = "(SELECT count(*) 
				FROM {$this->alias} 
				WHERE {$where_clause} 
				AND {$parent_alias}.{$parent_key} = {$this->alias}.{$parent_fkeys[$this->alias]['fkey']}) 
				AS {$alias_clause}";

			if (!in_array($query, $GLOBALS[self::GLOBAL_KEY_SELECT_CLAUSES]))
			{
				$GLOBALS[self::GLOBAL_KEY_SELECT_CLAUSES][] = $query;
			}
		}
	}

	public function group_concat($name = null)
	{
		$alias_clause = $this->construct_alias($this, self::MODE_GROUPCONCAT);

		if (isset($this->parent->foreign_keys))
		{
			$parent_fkeys = $this->parent->foreign_keys;
		}
		else
		{
			return null;
		}

		if (isset($parent_fkeys[$this->alias]))
		{
			$parent_key = $parent_fkeys[$this->alias]['key'];
			if (isset($this->parent->alias))
				$parent_alias = $this->parent->alias;
			else
				$parent_alias = $this->parent->tbl;

			$query = "group_concat({$this->alias}.{$name} SEPARATOR ', ') as {$alias_clause}";

			if (!in_array($query, $GLOBALS[self::GLOBAL_KEY_SELECT_CLAUSES]))
			{
				$GLOBALS[self::GLOBAL_KEY_SELECT_CLAUSES][] = $query;
			}
		}
	}

	/**
	 * Creates a count alias used in MYSQL SELECT statement.
	 * This uses parent alias, this table's alias, and optional value(if getting multiple counts from the same table)
	 * 
	 * @notes Modularizing this so it can potentially be customized.
	 * 
	 * @param type $fieldname
	 * @param type $value
	 * @param string $parent_alias may be null, if top-level object in an object chain
	 * @return type 
	 */
	static public function construct_alias(ROCKETS_Model $model, $mode = self::MODE_COUNT, $value = null)
	{
		$mode_str = self::get_mode_string($mode);

		$return_str = "";
		if (isset($model->parent->alias))
			$return_str = "{$model->parent->alias}_{$model->alias}_{$mode_str}";
		else
			$return_str = "{$model->alias}_{$mode_str}";
		if ($value)
			$return_str .= "_{$value}";
		return $return_str;
	}

	/**
	 * Mode string is used to construct the MYSQL field alias for count/sum/group_concat
	 * related field data which doesn't have a tablename associated with the data in
	 * fields META data - so we embed the table name in the fields name
	 * 
	 * @param type $mode 
	 * @return string Returns the mode string
	 */
	static public function get_mode_string($mode)
	{
		switch ($mode) {
			case self::MODE_COUNT:
				return "count";
			case self::MODE_SUMCOUNT:
				return "count";
			case self::MODE_GROUPCONCAT:
				return "groupconcat";
		}
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

}

?>