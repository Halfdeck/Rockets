<?php

/**
 * <p>ROCKETS_HTTP handles HTTP-related functions, like URL checks using HTTP headers.
 * ROCKETS_URL handles URL parsing, not HTTP header checks</p>
 * 
 */
class ROCKETS_HTTP {
    const HTTP_HEADER_200 = "HTTP/1.1 200 OK";

    /**
     * <p>Generic function that checks for broken links.</p>
     * @param string $url - e.g. http://www.cnn.com/
     * @return boolean true if URL is valid, false otherwise.
     */
    public static function http_isValidURL($url) {
	if(!ROCKETS_URL::isWellFormedURL($url)) {
	    if(BOOL_DEBUG) {
		echo "URL is NOT WELL FORMED!" .PHP_EOL;
	    }
	    return false;
	}
	if(BOOL_DEBUG) {
	    echo "URL is well formed!" .PHP_EOL;
	}
	$headers = get_headers($url);
	if ($headers[0] != self::HTTP_HEADER_200)
	    return false;
	return true;
    }

}

?>
