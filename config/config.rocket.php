<?php

/**
 * Folder names
 */

/** Server path to the project directory, which is /pkg_project/ */
define("FLDR_APPLICATION", "pkg_bacuri");
/** Site package directory name */
define("FLDR_CLIENT", "pkg_site");
/** Rockets PHP framework package directory name */
define("FLDR_ROCKETS", "pkg_rockets");
/** IDX folder name */
define("FLDR_IDX", "pkg_idx");

/**
 * Absolute paths
 */
define("PATH_ROOT", $_SERVER['DOCUMENT_ROOT']);
/** Application path */
define("PATH_APPLICATION", PATH_ROOT ."/" .FLDR_APPLICATION ."/");
/** Rockets PHP Framework path */
define("PATH_ROCKETS", PATH_ROOT ."/" .FLDR_ROCKETS ."/");

/** Include the project config file - let that config file worry about the rest */
include(PATH_APPLICATION ."/config/config.paths.php");


?>
