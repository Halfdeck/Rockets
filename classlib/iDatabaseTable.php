<?php

/*
	Database table interface - used to interact with MYSQL
*/

interface iDatabaseTable {

	public function createQuery(); 							// create query - it should return the query string
	public function delete($ar = array(null)); 				// delete a record
	public function get_row($ar = array(null));				// get a record (assume only one record is returned)
	public function get_field($fieldname);					// wrapper for all get functions
	//private function set_data($row);						// set data - called before any get_field function
	public function insert($ar = array(null));				// insert a new record
	//public function delete();
}

?>