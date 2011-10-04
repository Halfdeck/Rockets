<?php

/**
 * Parent class that holds methods shared by Views, Models, and Controllers
 *
 * @author Halfdeck
 */
class ROCKETS_MVC {
	
	const PREFIX_MODEL = "_MODEL_";
	const PREFIX_VIEW = "_HTML_";
	const PREFIX_CONTROLLER = "_CONTROLLER_";
	
	const TYPE_MODEL = 1;
	const TYPE_VIEW = 2;
	const TYPE_CONTROLLER = 3;
	
	private $model;
	
	public function __get($name)
	{
		switch($name) {
			case "model":
				if($this->model == null) {
					$classname = self::get_classname($this->directory_name, self::TYPE_MODEL);
					$this->model = new $classname;
					return $this->model;
				}
				else {
					return $this->model;
				}
				break;
			default:
				return $this->$name;
				break;
		}
	}
	
	/**
	 * Get singular model name
	 * 
	 * Assumes Rockets path convention: singular table names, words divided by -
	 * e.g. client-detail/insert/, employee/list/
	 * 
	 * @param type $directory_name
	 * @return type 
	 */
	static public function get_model_name_singular($directory_name) 
	{
		$result = str_replace("/", "", $directory_name); // "/client-detail/" => "client-detail"
		return str_replace("-", " ", $result); // "client-detail" => "client detail"
	}
	
	/**
	 * Get class name
	 * 
	 * @param type $directory_name
	 * @param type $type
	 * @return type 
	 */
	static public function get_classname($directory_name, $type = self::TYPE_CONTROLLER) 
	{
		$directory_name = self::get_model_name_singular($directory_name);
		$directory_name = ROCKETS_String::camelCase($directory_name); // "client detail" => "ClientDetail"
		
		switch($type) {
			case self::TYPE_CONTROLLER:
				return PREFIX_APPLICATION_CLASSLIB .self::PREFIX_CONTROLLER .ucfirst($directory_name);
			case self::TYPE_MODEL:
				return PREFIX_APPLICATION_CLASSLIB .self::PREFIX_MODEL .ucfirst($directory_name);
			case self::TYPE_VIEW:
				return PREFIX_APPLICATION_CLASSLIB .self::PREFIX_VIEW .ucfirst($directory_name);
			default:
				return false;
		}
		
	}
}

?>