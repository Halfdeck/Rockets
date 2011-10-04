<?php

/**
 * Description of JSON
 *
 * @author Tetsuto
 */
class ROCKETS_JSON {

	/**
	 * Turn mysql result into json
	 * 
	 * @param type $result
	 * @return type 
	 */
	static public function mysql_result_encode($result)
	{
		$rows = array();
		while ($row = mysql_fetch_assoc($result))
		{
			$rows[] = $row;
		}
		return json_encode($rows);
	}

}

?>
