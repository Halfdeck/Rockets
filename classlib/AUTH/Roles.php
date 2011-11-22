<?php

/**
 * Description of Roles
 * 
 * @abstraction - need to detach from application-specific model classes
 *
 * @author Tetsuto
 */
abstract class ROCKETS_AUTH_Roles extends ROCKETS_AUTH_Core {

	/**
	 * A list of roles (string names, not IDs)
	 * Used to map role names to string names, needed to dynamically display listboxes
	 * depending on role
	 * 
	 * @var type 
	 */
	public $access_rights = array();
	static public $permissions = array(
		'read' => 'read',
		'write' => 'create',
		'create' => 'create',
		'update' => 'update'
	);

	/*
	 * Permission constants
	 */
	const PERMISSION_READ = 'read';
	const PERMISSION_WRITE = 'create';
	const PERMISSION_CREATE = 'create';
	const PERMISSION_UPDATE = 'update';
	
	public function __construct()
	{
		
	}


	/**
	 * If [role name] is in an array of roles for this user, return true
	 * else return false
	 * 
	 * @param string $role_string
	 * @return boolean
	 */
	public function is_role_name($role_string)
	{
		if (in_array($role_string, $this->role_names))
			return true;
		else
			return false;
	}
	
	/**
	 * Default __get
	 * @param type $name
	 * @return type
	 */
	public function __get($name)
	{
		switch ($name) {
			case "roles":
				if (isset($this->$name))
					return $this->$name;
				else
				{
					$this->load_roles();
					return $this->roles;
				}
				break;
			case "role_names":
				if (isset($this->role_names))
					return $this->role_names;
				else
				{
					$this->load_roles();
					return $this->role_names;
				}
				break;
			default:
				parent::__get($name);
				break;
		};
	}
	
	/**
	 * Get roles and role names - specific implementation is app specific
	 */
	abstract public function load_roles();

	/**
	 * Checks if the user has Read access to the resource, identified by $task_name
	 * 
	 * @param type $task_name
	 * @param string $type
	 * @param string $field_name
	 * @return type 
	 */
	public function check_permission($name, $type = JOB_MODEL_Task::TYPE_PAGE, $field_name = null, $permission_type, $options = array(null))
	{
		/**
		 * access_rights contain an array of available task descriptions. If item
		 * isn't in this array, don't give the user access.
		 * This should be a whitelist - otherwise logoff exposes hidden UI
		 */
		if (empty($this->access_rights[$name][$type][$field_name]))
		{
			return false;
		}
		
		/**
		 * Default permission type is READ
		 */
		if($permission_type == NULL) $permission_type = self::$permissions['read'];
		
		//echo $name ." " .$field_name ." " .$this->access_rights[$name][$type][$field_name] ." {$permission_type}<br>";
		/**
		 * Is readable
		 */
		if (strstr($this->access_rights[$name][$type][$field_name], $permission_type))
		{
			return true;
		}
		else
		/**
		 * No match 
		 */
		{
			return false;
		}
	}

	/**
	 * Given a rights matrix array and a mysql row, add the row data to the matrix.
	 * If there's already an entry with the same task ID, merge the rights
	 * 
	 * @param string $rightsMatrix structure: array("name" => array('type' => array(field_name => ...) = rights
	 * @param string $row
	 */
	static function add_rights(Array $rightsMatrix, Array $row)
	{
		/**
		 * If task already exists in the Rights Matrix, merge.
		 */
		if (isset($rightsMatrix[$row['name']][$row['type']][$row['field_name']]))
		{
			$rightsMatrix[$row['name']][$row['type']][$row['field_name']] = self::merge_rights_strings($rightsMatrix[$row['name']][$row['type']][$row['field_name']], $row['rights']);
		}
		/**
		 * New task, so just add to the Rights Matrix
		 */
		else
		{
			$rightsMatrix[$row['name']][$row['type']][$row['field_name']] = $row['rights'];
		}
		return $rightsMatrix;
	}

	/**
	 * Merge two strings and return a combined string
	 * Example: $string1 = "create,delete,edit"; $string2 = "create"; return value => "create,delete,edit"
	 * 
	 * @param type $string1
	 * @param type $string2
	 * @return type 
	 */
	static function merge_rights_strings($string1, $string2)
	{
		return ROCKETS_String::merge_comma_delimited_strings($string1, $string2);
	}
}

?>
