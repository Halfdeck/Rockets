<?php

/**
 * Model class used to access MYSQL data and linked to View objects
 * 
 * @author Halfdeck
 */
class ROCKETS_Model extends ROCKETS_MYSQL_TwoPass {

	/**
	 * $alias - must be set to PUBLIC, so its accessible by view
	 * $tbl - set to PROTECTED
	 */
	public function __construct($ar = array(null))
	{
		parent::__construct($ar);
	}

}

?>