<?php

/**
 * Description of Table
 *
 * @author Halfdeck
 */
class ROCKETS_HTML_Table {

    /**
     * A table display function that uses an array of values to fill a table.
     * use 3rd field to mod what gets displayed
     * if null, just displays the unmodified value
     * @param <type> $v
     */
    public static function displayTable($v) {
	$c = 0;/** counter */
	foreach ($v as $a) {
	    if ($a[0] && $a[0] != "\r\n") { // check for reo field that is blank but has a carriage return
		$html = "	<td class='value'>" . $a[1] . "</td>\n";
		if ($a[2] == null)
		    $html .= "	<td>" . $a[0] . "</td>\n";
		else if ($a[2] == "clean")
		    $html .= "	<td>" . ROCKETS_String::cleanStr($a[0]) ."</td>\n";
		else
		    $html .= "	<td>" . $a[2] . "</td>\n";
		if ($c == 0)
		    $html = "	<tr>\n" . $html;
		$c++;
		if ($c > 1) {  // Create a new row every 2 items
		    $html = $html . "	</tr>\n";
		    $c = 0;
		}
		echo $html;
	    }
	}
	// check to see if the last row contains 2 items - if not, close TR
	// $c = 0 if TR has just been closed.

	if ($c != 0)
	    echo "	</tr>\n";
    }

}

?>
