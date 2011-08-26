<?php

/**
 * MYSQL table controller.
 * Uses $_REQUEST to build queries
 */
abstract class ROCKETS_MYSQLTable extends ROCKETS_ConfigurableObject
{
    /**
     * @global BOOL_EXECUTE
     * @global BOOL_DEBUG
     * @global DB_NAME
     */
    // config - override these in child classes

    /**
     * name of the database
     */
    const DB_NAME = DB_NAME;
    const DEFAULT_LIMIT = 12;   // default MYSQL LIMIT value

    /**
     * BETWEEN query type
     * e.g WHERE usertable.age BETWEEN '1' AND '56'
     */
    const QUERY_TYPE_BETWEEN = 1;
    /**
     * IN query type
     * e.g. WHERE usertable.age IN ('1','2','4','5')
     */
    const QUERY_TYPE_IN = 2;

    /** name of this table */
    protected $tbl;
    /** name of primary key field */
    protected $primary_key_fieldname;
	/**
	 * Used to maintain primary key(s) in cases where more than one key is used
	 * @var Array
	 */
	protected $primary_keys = array();
    /** fieldnames */
    protected $fieldnames = array();
    /** currently loaded mysql row data - saved to issue get_field calls */
    protected $row = false;
    // show query regardless of $DEBUG value
    public static $SHOW_QUERY = true;
    public static $limit = 12;     // limit value, defaults to DEFAULT_LIMIT
    protected $rowCount = 0;     // number of rows returned by MYSQL

    /**
     * Instantiates a MYSQL controller object
     * for a custom primary key, set $this->primary_key_field after calling parent::__construct();
     *
     * @param int $ar['limit'] If no limit is specified, limit defaults to 12.
     * @param boolean $ar['debug'] true/false: If no query is specified, $_REQUEST is used to load values.
     * @param boolean $ar['execute'] true/false: if false, queries are not executed.
     */
    function __construct($ar = array(null))
    {

        self::$limit = ROCKETS_MYSQLTable::DEFAULT_LIMIT;

        if (isset($ar['limit']))
        {
            self::$limit = $ar['limit'];
        }

        $this->loadQueryValues($_REQUEST);
        if (isset($this->tbl))
            $this->fieldnames = $this->getTableFields($this->tbl);
        parent::__construct($ar);
    }

    // load queryvalues
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
     * count total number of rows returned by a MYSQL query
     *
     * @param
     *
     * $verbose - set to true to see number of rows returned by a select statement
     */
    protected function countRows($verbose = false)
    {
        $countResult = mysql_fetch_assoc(mysql_query("SELECT FOUND_ROWS()"));
        $this->rowCount = $countResult['FOUND_ROWS()'];
        if ($verbose)
        {
            echo "Found rows: {$this->rowCount}<br>";
        }
    }

    /**
     * <p>Delete a record using primary index value.
     * Usage:
     *
     * ->delete(array(
     * 	'index_value'=>...
     * ));
     *
     * </p>
     * @param <type> $ar
     * @return <type>
     */
    public function delete($ar = array(null))
    {
        if (array_key_exists("index_value", $ar))
            $index_value = $ar['index_value'];
        if (!$index_value)
            return false;

        $query = "DELETE FROM {$this->tbl} WHERE {$this->primary_key_fieldname}='{$index_value}'";
        mysql_query($query);
    }

    public function get_row($ar = array(null))
    {
        $query = $this->createQuery();
        $result = mysql_query($query);
        if (!$result)
            return false;
        $row = mysql_fetch_assoc($result);
        $this->row = $row;
    }

    /*
      Return number of returned rows
     */

    public function get_rowCount()
    {
        return $this->rowCount;
    }

    /**
      Wrapper for accessing MYSQL return values. Wrapper is used to protect scripts from change.
      Default behavior is keep field names unchanged. When handling custom fields, override this function with (example):

      case "username":return $this->row['custom_username'];
     * */
    public function get_field($fn)
    {
        switch ($fn) {
            default:return $this->row[$fn];
        }
    }

    /**
     * Using $this->fieldnames and $_REQUEST, generate field => value pairs array to feed to insert()
     * @param array $obj send a class object as an array to scan instead of $_REQUEST. if $obj is null, then $_REQUEST is used.
     * @return array an array of table-relevant fields.
     */
    private function generate_fieldValue($obj = null)
    {
        $ar = array();
        if (!$obj)
            $obj = $_REQUEST;
        print_r($obj);
        foreach ($this->fieldnames as $field)
        {
            if (isset($obj[$field]))
                $ar[$field] = $obj[$field];
        }
        return $ar;
    }

    private function generate_fieldValueInternal($obj = null)
    {

        $ar = array();
        if (!$obj)
        {
            $obj = $_REQUEST;
        }
        $fieldnames = $this->retrieveTableFields($this->tbl);

        foreach ($fieldnames as $field)
        {
            if (isset($obj[$field]))
                $ar[$field] = $obj[$field];
        }
        return $ar;
    }

    /**
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
            $ar = $this->generate_fieldValueInternal($ar['row']); // automatically fetch table fieldnames and link with MYSQL row array data
        }
        else
        {
            //    $ar = $this->generate_fieldValue(); // load $_REQUEST
            $ar = $this->generate_fieldValueInternal(); // automatically fetch table fieldnames and link with $_REQUEST
        }

        $mysql_fields = "";
        $mysql_vals = "";
        $mysql_update = "";
        $c = 0; // counter for insert
        $d = 0; // counter for update

        foreach ($ar as $key => $value)
        {

            if (!$value)
                continue;

            $value = mysql_real_escape_string($value); // add slashes to prevent MYSQL errors

            if (self::$DEBUG)
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
     * <p>Magically inserts a new record, given $this->fieldnames
     *
     * input values are rows of fields instead of a single row
     *
     * This method doesn't do updates, only inserts.
     *
     * input array should contain a list of field/value pairs:
     *  array(
     * 	user_id => 1,
     * 	username => john...
     *  );<p>
     *
     * @param MYSQLresult $result contains field/value pairs; otherwise generate_fieldValue() is used to autogenerate $ar from $_REQUEST
     */
    public function massInsert($result)
    {

        $mysql_fields = "";
        $mysql_vals = "";
        $d = 0; // number of records

        while ($row = mysql_fetch_assoc($result))
        {
            $c = 0; // counter for insert
            $ar = $this->generate_fieldValueInternal($row); // automatically fetch table fieldnames and link with $_REQUEST
            // generate $fields string

            if ($d == 0)
            {
                foreach ($ar as $key => $value)
                {
                    if ($c > 0)
                    {
                        $mysql_fields .= ", ";
                    }
                    $mysql_fields .= "{$key}";
                    $c++;
                }
            }

            $c = 0; // reset counter
            // generate $value string
            if ($d > 0)
                $mysql_vals .= ",";
            $mysql_vals .= "(";
            foreach ($ar as $key => $value)
            {
                if (BOOL_DEBUG)
                    echo "[$c] KEY [{$key}] INDEX: [{$this->primary_key_fieldname}]<br>";

                if ($c > 0)
                {
                    $mysql_vals .= ", ";
                }
                $mysql_vals .= "\"" . addslashes($value) . "\"";
                $c++;
            }
            $mysql_vals .= ")";
            $d++;
        }

        $query = "INSERT INTO {$this->tbl} ({$mysql_fields}) VALUES {$mysql_vals}";
        self::exec($query);
    }

    /**
     * Batch function that creates tables or modifies them - used during Installation.
     *
     * - Create an array $tables: $tables[$tableName] = $fields;
     * - then send it: array("tables" => $tables)
     *
     * @Param
     * $ar['tables'] - array of tables, see config.tables.php for examples
     * $ar['execute'] - true: execute MYSQL queries (default: true)
     * $ar['debug'] - true: verbose feedback
     *
     * @return
     * No return value
     * */
    static public function createOrModifyTables($ar = null)
    {

        foreach ($ar['tables'] as $key => $table)
        {
            ob_flush(); // prevent PHP output freezing browser
            flush();
            $tableName = $key;
            if (self::$DEBUG)
                echo "<h2>Found key: {$tableName}</h2>";
            if (self::checkIfTableExists($tableName))
                self::modifyTable($tableName, $table);
            else
                self::createTable(array(
                    'tableName' => $tableName,
                    'fields' => $table
                ));
        }
    }

    /**
     * Takes a table array and tries to create a table. If it already exists, alter table. Function is used during Installation.
     *
     * @param <string> $ar['tableName'] Table name
     * @param <type> $ar['fields'] An array of fields, defined by $key => $value pairs (e.g. "field1" => "varchar(255)"
     * 
     */
    public function createTable($ar = null)
    {
        $query = "CREATE TABLE IF NOT EXISTS {$ar['tableName']} (";
        $c = 0;
        foreach ($ar['fields'] as $key => $value)
        {
            if ($c > 0)
                $query .= ", ";
            $query .= "{$key} {$value}";
            $c++;
        }
        $query .= ")";
        self::exec($query);
    }

    /**
     * MYSQL write operation wrapper function, used to handle debugging. Write operations include
     * INSERT, UPDATE, CREATE TABLE, etc.
     *
     * @param string $query - query string
     */
    public static function exec($query)
    {
        if (BOOL_DEBUG)
			echo ROCKETS_String::mysql_prettify ($query);
        if (BOOL_EXECUTE)
        {
            mysql_query($query);
            self::issueError(array("continue" => false, "query" => $query));
        }
    }

    /**
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
     * Check if table exists.
     *
     * Return - true if table exists; false otherwise.
     */
    function checkIfTableExists($tableName)
    {

        $query = "SELECT COUNT(*) as count
			FROM information_schema.tables 
			WHERE table_schema = '" . self::DB_NAME . "'
			AND table_name = '{$tableName}'";

        if (self::$DEBUG || self::$SHOW_QUERY)
            echo "Checking if table exists: " . $query . "<br><br>";
        $row = mysql_fetch_assoc(mysql_query($query));
        if ($row['count'] == 0)
            return false;
        else
            return true;
    }

    /**
     * Checks MYSQL schema to see if an index exists.
     * Assumes constraint name is field name, unless the field is primary, in which case the constraint name is PRIMARY.
     *
     * @param string $table
     * @param string $constraint
     * @return boolean true if constraint exists
     */
    public static function existsConstraint($table, $constraint)
    {
        $query = "SELECT * FROM information_schema.`TABLE_CONSTRAINTS` WHERE TABLE_CONSTRAINTS.TABLE_NAME = '{$table}' and CONSTRAINT_NAME = '{$constraint}'";
        $result = mysql_query($query);
        echo $query . "<br>";
        if ($result && mysql_num_rows($result) > 0)
            return true;
        else
            return false;
    }

    /**
     * Given a database and a table, retrieves fields, then returns them in an array.
     * @param string $tableName MYSQL tablename
     * @return <type>
     */
    public static function retrieveTableFields($tableName)
    {

        $tableFields = mysql_query("SHOW COLUMNS FROM {$tableName}");

        while ($field = mysql_fetch_assoc($tableFields))
        {

            $field_array[] = $field['Field'];
            if ($field['Key'] == 'PRI' && isset($this)) {
				$this->primary_key_fieldname = $field['Field']; // set primary key
			}
               
        }
        if (BOOL_DEBUG)
        {
            print_r($field_array);
        }
        return $field_array;
    }

    /**
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
            if ($field['Key'] == 'PRI') {
                $this->primary_key_fieldname = $field['Field']; // set primary key
				$this->primary_keys[] = $field['Field'];
			}
        }
        return $field_array;
    }

    /**
     * Modify table if table already exists
     * @param string $tableName
     * @param array $newFields
     */
    public function modifyTable($tableName, $newFields)
    {

        if (self::$DEBUG)
        {
            echo "<h1>Table configuration : {$tableName} </h1>";
        }
        $fields = self::retrieveTableFields($tableName);
        if (self::$DEBUG)
        {
            echo "<h1>Retrieved MYSQL fields</h1>";
            print_r($fields);
            echo PHP_EOL;
        }

        /**
         * Track auto increment field to avoid modifying it - mysql doesn't let you modify and will issue an error
         */
        $auto_increment = "";

        /**
         * @todo [mvc] check the field value to make sure something really needs to be modified.
         */
        foreach ($newFields as $field => $setting)
        {
            echo PHP_EOL;
            echo "[{$field}] [{$setting}]";

            if (stristr($setting, "auto_increment"))
            {
                $auto_increment = $field;
                echo "SAVING AUTO INCREMENT FIELD [{$field}]";
            }

            $modifyQuery = "ALTER TABLE {$tableName} MODIFY {$field} {$setting}";
            $addQuery = "ALTER TABLE {$tableName} ADD {$field} {$setting}";

            /**
             * Unique Index - prevent adding dupes
             */
            if (strcasecmp($field, "unique") == 0)
            {
                $query = $addQuery;
                self::dropIndex(array(
                    'tableName' => $tableName,
                    'fieldName' => str_replace(array("(", ")"), "", $setting) // remove parenthesis, if any
                ));
            }
            else if (strcasecmp($field, "primary key") == 0)
            {
                echo "PRIMARY KEY SETTING: {$setting}" . PHP_EOL;
                /**
                 * if its an auto_increment field, do nothing.
                 */
                if (str_replace(array("(", ")"), "", $setting) != $auto_increment)
                {
                    $query = $addQuery;
                    // drop primary key first
                    self::dropPrimaryKey($tableName);
                }
            }
            else if (!in_array($field, $fields))
                $query = $addQuery;
            else
                $query = $modifyQuery;
            self::exec($query);
        }
    }

    /**
     * Drop primary key. This must be done before primary key can be modified
     * @param string $ar['table name'] name of MYSQL table
     */
    public function dropPrimaryKey($tableName)
    {
        $tableName = $tableName;
        $query = "ALTER TABLE {$tableName} drop primary key";
        self::exec($query);
    }

    /**
     * Drop primary key. This must be done before primary key can be modified
     * @param string $ar['table name'] name of MYSQL table
     */
    public function dropIndex($ar = null)
    {
        $tableName = $ar['tableName'];
        $fieldName = $ar['fieldName'];
        /**
         * Check to see if constraint exists to prevent "index doesn't exist" error
         */
        if (!self::existsConstraint($tableName, $fieldName))
        {
            echo "{$fieldName} doesn't exist!" . PHP_EOL;
            return;
        }
        $query = "ALTER TABLE {$tableName} drop index {$fieldName}";
        self::exec($query);
    }

    /**
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
     * Converts a MYSQL result into an array
     * @param MYSQLresult $result
     * @return array
     */
    public static function convertResultToArray($result)
    {
        $ar = array();

        if (mysql_num_rows($result) == 0)
            return null;

        while ($row = mysql_fetch_assoc($result))
        {
            $ar[] = $row;
        }
        return $ar;
    }

    public function remoteCopyTableStructure($sourceDBName, $targetDBName, $sourceTable, $targetTable, $sourceDBLink, $targetDBLink)
    {

        $results = mysql_query('DESCRIBE ' . $sourceDBName . '.' . $sourceTable, $sourceDBLink);
        $query = 'DROP TABLE IF EXISTS ' . $targetDBName . '.' . $targetTable;

        mysql_query($query, $targetDBLink); // write to remote DB
        if (mysql_errno())
            echo "<h3>" . mysql_error($targetDB) . "</h3>";

        $query = 'CREATE TABLE ' . $targetDBName . '.' . $targetTable . ' (';

        $tmp = '';
        while ($row = @mysql_fetch_assoc($results))
        {
            $query .= '`' . $row['Field'] . '` ' . $row['Type'];

            if ($row['Null'] != 'YES')
            {
                $query .= ' NOT NULL';
            }
            if ($row['Default'] != '')
            {
                if ($row['Type'] != 'timestamp')
                    $query .= " DEFAULT '" . $row['Default'] . "' ";
            }
            if ($row['Extra'])
            {
                $query .= ' ' . strtoupper($row['Extra']);
            }
            if ($row['Key'] == 'PRI')
            {
                $tmp = 'primary key(' . $row['Field'] . ')';
            }
            $query .= ',';
        }

        $query .= $tmp . ')';
        mysql_query($query, $targetDBLink); // write to remote DB
        if (mysql_errno())
            echo "<h3>" . mysql_error($targetDBLink) . "</h3>";

        echo $query . "<br>";
    }

    /**
     * Copy data from one DB table to another remotely.
     * This assumes the db connections are open.
     * 
     * @param <type> $sourceDBName
     * @param <type> $targetDBName
     * @param <type> $sourceTable
     * @param <type> $targetTable
     * @param <type> $sourceDBLink
     * @param <type> $targetDBLink
     */
    public static function remoteCopyTableData($ar)
    {
        // $sourceDBName, $targetDBName, $sourceTable, $targetTable, $sourceDBLink, $targetDBLink) {

        $results = mysql_query('SELECT * FROM ' . $ar['source']['dbname'] . '.' . $ar['source']['table'], $ar['source']['link']);
        while ($row = @mysql_fetch_assoc($results))
        {
            //print_r($row);
            //echo "<br>" .PHP_EOL;
            $query = 'INSERT INTO ' . $ar['target']['dbname'] . '.' . $ar['target']['table'] . ' (';
            $data = Array();
            while (list($key, $value) = @each($row))
            {
                $data['keys'][] = $key;
                $data['values'][] = addslashes($value);
            }
            $query .= join($data['keys'], ', ') . ')' . 'VALUES (\'' . join($data['values'], '\', \'') . '\')';

            mysql_query($query, $ar['target']['link']); // writing to remoteDB
            if (mysql_errno())
                echo "<h3>" . mysql_error($ar['target']['link']) . "</h3>";
        }
    }

    /**
     * Connect to ANY mysql database (instead of default)
     */
    public static function connect($username, $password, $dbname, $host='localhost')
    {
        if (!$username || !$password || !$dbname)
        {
            throw new Exception();
            return null;
        }
        $link = mysql_connect($host, $username, $password);
        mysql_select_db($dbname, $link);
        if (mysql_errno())
            echo "<h3>" . mysql_error($link) . "</h3>";
        return $link;
    }

    /**
     * Drop MYSQL table
     */
    public function dropTable()
    {
        $query = "DROP TABLE IF EXISTS {$this->tbl}";
        $this->exec($query);
    }

    /**
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
                if(empty($_REQUEST[$filter_name[0]]) || empty($_REQUEST[$filter_name[1]])) return null;
                
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
                
               // if(BOOL_DEBUG) ROCKETS_String::echo_array_formatted($_REQUEST[$filter_name], "Item Content");
                foreach ($_REQUEST[$filter_name] as $item)
                {
                 //   if(BOOL_DEBUG) ROCKETS_String::echo_array_formatted($item, "Item Content");
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
            /**
             * if filter value is an array, create an "IN (x,y,z)" mysql clause
             * e.g. $_REQUEST['zip_codes'] = array(1,2,3,4,5....)
             */
            if (is_array($_REQUEST[$filter_name]))
            {
                $str = "(" . ROCKETS_String::mysql_get_in_list($_REQUEST[$filter_name]) . ")";
            }
        }
        else
        {
            /**
             * Regular query
             */
            $str = $_REQUEST[$filter_name];
        }

        $str = str_replace("[[filter_value]]", $str, $condition);
        $str = str_replace("[[table_name]]", $this->tbl, $str);
        return $str;
    }

    /**
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
                ROCKETS_String::echo_array_formatted($conditionset, "conditionset");
            /**
             * Check if $conditionset['name'] is an array in case BETWEEN statement is getting sent in
             * if its an array !empty(.. will break.
             */
            if (is_array($conditionset['name']) || !empty($_REQUEST[$conditionset['name']]) || isset($conditionset['checked']))
            {
                $checked = (empty($conditionset['checked'])) ? null : $conditionset['checked']; // make sure value is set in array
                $auto_condition = $this->auto_construct_condition($conditionset['name'], $conditionset['condition'], $checked);
                if ($auto_condition)
                    $where_clause .= " AND {$auto_condition}";
            }
        }
        return $where_clause;
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