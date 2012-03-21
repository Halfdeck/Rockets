<?php

/**
 * Uses
 * 
 * Purpose of this page is to simplify high-end coding process, so
 * more of development is plug and play
 * 
 * - Used to respond to user input
 * - Used to respond to url requests.
 *
 * @author Tetsuto
 */
class ROCKETS_Controller extends ROCKETS_MVC
{

	protected $directory_name;
	protected $action;
	protected $view_classname;
	protected $model_name;

	const ACTION_TYPE_INSERT = 1;
	const ACTION_TYPE_DELETE = 2;
	const ACTION_TYPE_EDIT = 3;
	const ACTION_TYPE_LIST =4;
	const ACTION_TYPE_CREATE = 5;

	const LAYOUT_LIGHTBOX = 'lightbox';
	const LAYOUT_AJAX = 'ajax';
	
	const KEY_LAYOUT = 'layout';
	
	protected $action_strings = array(
		1 => 'insert',
		2 => 'delete',
		3 => 'edit',
		4 => 'list',
		5 => 'create'
	);

	public function __construct()
	{
		
	}

	/**
	 * Get include path
	 * 
	 * @param type $action_type
	 * @param type $custom_action_string
	 * @return string 
	 */
	protected function get_path($action_type, $custom_action_string = NULL, $options = array(null))
	{
		$extension = "php";
		
		if($custom_action_string) {
			$action_string = $custom_action_string;
		}
		else {
			$action_string = $this->action_strings[$action_type];
		}
		
		if(isset($options['layout'])) {
			if($options['layout'] == 'lightbox') {
				$extension = "ltbx";
			}
			/**
			 * CSV template filename pattern: xyz.csv.php
			 */
			else if($options['layout'] == 'csv')
			{
				$extension = "csv.{$extension}";
			}
		}
		
		$path = PATH_PAGES . "{$this->directory_name}/{$action_string}.{$extension}";

		if (file_exists($path))
		{
			return $path;
		}
		else
		{
			return PATH_ELEMENTS . "/defaults/{$action_string}.{$extension}";
		}
	}
	
	/**
	 * Is this path a controller path (e.g. /user/list) or a custom path (e.g terms-of-service.php)
	 * 
	 * @param array $path_info
	 * @return type 
	 */
	static private function is_controller_path(Array $path_info) 
	{
		if(isset($path_info['extension'])) 
		{
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * Generates content based on Controller-configured behavior
	 * 
	 * @param type $str_url_query_string 
	 */
	static public function load_page($str_url_query_string)
	{
		/**
		 * @global $c - JOB_CAPMAIL_Auth - in /frame/index.php
		 */
		$path_info = pathinfo($str_url_query_string);
		$directory_name = $path_info['dirname'];
		
		/**
		 * If path is a plain file like "logout.php" instead of something like "/envelope-type/list/"
		 * just get the content of path and return
		 */
		if(self::is_controller_path($path_info) == FALSE) 
		{
			if(file_exists(PATH_PAGES ."/{$str_url_query_string}")) {
				include(PATH_PAGES ."/{$str_url_query_string}");
				return;
			}
			else {
				ROCKETS_HTTP::notFound();
				return false;
			}
		}
		
		$class_name = self::get_classname($directory_name, self::TYPE_CONTROLLER);
		
		/**
		 * If class doesn't exist, dynamically create the class as an extension
		 * of this class using EVAL() 
		 * 
		 * This allows you to create pages without having to create a controller
		 * for every page.
		 */
		if (class_exists($class_name) == FALSE)
		{
			//$this_class_name = get_class();
			$this_class_name = get_called_class();
			eval("class {$class_name} extends {$this_class_name} {};");
		}

		$o = new $class_name;
		
		$o->model_name = self::get_model_name_singular($directory_name);
		$o->directory_name = $directory_name;
		$o->action = $path_info['filename'];
		//echo "activateD";
		$action_method = "do_" . $o->action;
		if($o->permission_check() == false) {
			//echo "access not permitted";
			return false; // pre page load check (e.g. permission checking)
		}
		$o->$action_method();
	}
	
	/**
	 * Base optional permission checking pre page load.
	 * Usage: extension of this class should optionally override this method.
	 * @return type 
	 */
	public function permission_check() {
		return true;
	}
	
	public function do_json() 
	{
		echo ROCKETS_JSON::mysql_result_encode($this->model->get_all_records());
	}
	
	/**
	 * Modify URL structure so we have /job/list/.. /job/1/ as root
	 */
	public function do_jsonsingle() {
		
		echo ROCKETS_JSON::mysql_result_encode($this->model->get_record_by_id(ROCKETS_Request::get('id')));
	}

	/**
	 * Unlike edit() - which responds to "AJAX" requests, edit_item() displays
	 * one item for editing (e.g. Edit Job)
	 */
	public function do_edit()
	{
		$view_classname = self::get_classname($this->directory_name, self::TYPE_VIEW);
		$o = $view_classname::load_object(ROCKETS_Request::get('id'));

		include($this->get_path(self::ACTION_TYPE_EDIT));
	}
	
	/**
	 * Default Create behavior - identical to do_edit()
	 */
	public function do_create()
	{
		$view_classname = self::get_classname($this->directory_name, self::TYPE_VIEW);
		$o = $view_classname::load_object(ROCKETS_Request::get('id'));

		include($this->get_path(self::ACTION_TYPE_EDIT));
	}

	/**
	 * Default implementation of LIST action
	 */
	public function do_list()
	{
		$sorter = array(
			'name' => "sorter",
			'default' => 'name ASC',
			'options' => array(
				"name ASC" => "Name",
			)
		);

		$result = $this->model->get_records(array(
			'conditionset' => array(
			),
			'sorter' => $sorter,
			'limit' => 10
			));

		include($this->get_path(self::ACTION_TYPE_LIST));
	}

	/**
	 * Insert a record in MYSQL
	 */
	public function do_insert()
	{
		$this->model->insert();
		
		/**
		 * If the page is lightbox don't redirect
		 */
		if(ROCKETS_Request::get(self::KEY_LAYOUT) == self::LAYOUT_AJAX) 
		{
			// do nothing
		}
		else {
			/**
			 * redirect to List view
			 */
			ROCKETS_HTTP::redirect(RPATH_ROOT . "{$this->directory_name}/list/");
		}
	}

	/**
	 * Delete a MYSQL record
	 */
	public function do_delete()
	{
		$this->model->delete();
		
		/**
		 * Redirect unless the request came from a lightbox
		 */
		if(ROCKETS_Request::get(self::KEY_LAYOUT) != self::LAYOUT_AJAX) {
			ROCKETS_HTTP::redirect($_SERVER['HTTP_REFERER']);
		}
		//ROCKETS_HTTP::redirect(RPATH_ROOT . "{$this->directory_name}/list/");
	}
	
	/**
	 * Check for unique data - used for validating data before record insertion.
	 * AJAX query form example: store/check_unique/?value=store_name&fieldname=Savemart
	 * @return 'unique' or primary key value of a record that already exists
	 */
	public function do_check_unique()
	{
		$result = $this->model->isUnique(ROCKETS_Request::get('value'), ROCKETS_Request::get('fieldname'), $this->model->primary_key_fieldname);
		if($result == false) echo "unique";
		else echo $result;
	}

	/**
	 * auto-draw interal URLs.
	 * 
	 * @param type $action_type
	 * @return type 
	 */
	public function draw_url($action_type)
	{
		$action = $this->action_strings[$action_type];
		return RPATH_ROOT . "{$this->directory_name}/{$action}/";
	}
	
	/**
	 * Get page title
	 * 
	 * @param type $action_type
	 * @return type 
	 */
	protected function get_title($action_type) {
		switch($action_type) {
			case self::ACTION_TYPE_LIST:
				$title = $this->get_model_name_singular($this->directory_name); // "/envelope-type" => "envelope type" 
				$title = ROCKETS_String::makePlural($title); // "envelope type" => "envelope types"
				return ucwords($title); // "envelope types" => "Envelope Types"
				break;
			case self::ACTION_TYPE_EDIT:
				$title = $this->get_model_name_singular($this->directory_name); // "/envelope-type" => "envelope type" 
				$title = "Edit {$title}"; // => "Edit envelope type"
				return ucwords($title); // => "Edit Envelope Type"
		}
	}

}

?>
