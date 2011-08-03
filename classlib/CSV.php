<?php

/**
 * A collection of CSV file related functions
 *
 * @author Halfdeck
 */
class ROCKETS_CSV extends ROCKETS_String {

    const CSV_DEFAULT_SEPARATOR = "|";

    public static function cleanLine($line) {
	$line = str_replace("\r\n", "", $line);
	$line = trim($line);
	return $line;
    }

    /**
     * Get data in a CSV line packed in an array.
     *
     * @param <type> $line
     * @param <type> $separator
     * @return array if line isn't empty, returns an array. Else returns null;
     */
    public static function getDataArray($line, $separator=self::CSV_DEFAULT_SEPARATOR) {
	$data = explode($separator, $line);
	if(self::isEmptyLine($data)) return NULL;
	else return $data;
    }

    /**
     * Determine if the CSV line data is empty.
     * If the field count is less than 2, we assume its empty.
     * WARNING: This is not true - some CSV files only contain one item per line.. like offmarket data
     *
     * @param array $data
     * @return boolean true if line is empty.
     */
    private static function isEmptyLine($data) {
	if (count($data) < 2) {
	    return true;
	}
	else return false;
    }

    /**
     * Takes a record of rows and turns it into a CSV.
     *
     * array structure: array("fieldname" => "mysql definition", "fieldname2" => "mysql def2"...)
     *
     * @param <type> $records
     * @param <type> $separator
     * @return string "fieldname|fieldname2|fieldname3\n"
     */
    public static function createCSVLine($fields, $separator=self::CSV_DEFAULT_SEPARATOR) {
	$content = "";
	$c = 0;

	foreach($fields as $key => $val) {
	    if($c>0) $content .= $separator;
	    $content .= $key;
	    $c++;
	}

	$content .= PHP_EOL;
	return $content;
    }
}
?>
