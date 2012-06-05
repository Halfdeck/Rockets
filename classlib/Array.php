<?php

/**
 * Description of Array
 *
 * @author Halfdeck
 */
class ROCKETS_Array {

	/**
	 * Add an item to an array. Called by Model::make_array()
	 * 
	 * Examples:
	 * 
	 * add_item_to_array($result, "name") => array('bob','joe','mary'..)
	 * add_item_to_array($result, "name", "user_id") => array('12311' => 'bob', '1221' => 'joe'...)
	 * 
	 * @param type $key_name
	 * @param type $value_name
	 * @param type $ar
	 * @return type 
	 */
	static public function add_item($key_name, $value_name, $ar)
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

		return $ar;
	}
}

?>