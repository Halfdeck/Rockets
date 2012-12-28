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
	
	/**
	 * Used to encode after the fact - in cases where this information
	 * needs to further be packaged (e.g. packaged with field metas)
	 * mysql_fetch_array is used so we can match up the fields.
	 * 
	 * @param type $result
	 * @return type 
	 */
	static public function mysql_result_unencoded($result)
	{
		$rows = array();
		while ($row = mysql_fetch_array($result))
		{
			/**
			 * Prevent memory allocation failure
			 */
			if(memory_get_usage() > ROCKETS_PHP_MEMORY_LIMIT_THRESHOLD)
			{
				mail(EMAIL_ADMIN, "Job Board Notice: Mysql_result_unencoded memory exceeded", print_r($GLOBALS, true));
				break;
			}
									
			$rows[] = $row;
		}
		return $rows;
	}
	
	/**
	 * Called after MYSQL Two Pass call.. where fieldsMeta is saved
	 * Useful when retrieving data from multi-joined query
	 * mapping is reversed so given tablename and fieldname, we can easily
	 * find the index.
	 * 
	 * @param type $result 
	 */
	static public function mysql_result_mapped($result)
	{
		$mapping = array();
		
		foreach(ROCKETS_MYSQL_TwoPass::$fieldsMeta as $key => $map)
		{
			$mapping[$map['table']][$map['name']] = $key;
		}
		
		$JSONresponse = array(
			'meta' => $mapping,
			'result' => ROCKETS_JSON::mysql_result_unencoded($result),
		);

		return json_encode($JSONresponse);
	}

}

?>
