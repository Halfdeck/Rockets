<?php

error_reporting(0); // suppress errors

// user defined error handling function
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
   if($errno != 2) return null; // only deal with warnings
   
   $dt = date("Y-m-d H:i:s (T)");

   // define an assoc array of error string
   // in reality the only entries we should
   // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
   // E_USER_WARNING and E_USER_NOTICE
   $errortype = array (
               E_ERROR          => "Error",
               E_WARNING        => "Warning",
               E_PARSE          => "Parsing Error",
               E_NOTICE          => "Notice",
               E_CORE_ERROR      => "Core Error",
               E_CORE_WARNING    => "Core Warning",
               E_COMPILE_ERROR  => "Compile Error",
               E_COMPILE_WARNING => "Compile Warning",
               E_USER_ERROR      => "User Error",
               E_USER_WARNING    => "User Warning",
               E_USER_NOTICE    => "User Notice",
               E_STRICT          => "Runtime Notice"
               );
   // set of errors for which a var trace will be saved
   $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
   
   $url = URL_BASE .substr($_SERVER['REQUEST_URI'],1);
  
   $err .= "IP: " .$_SERVER['REMOTE_ADDR'] ."\n";
   $err .= "Referer:" .$_SERVER['HTTP_REFERER'] ."\n";
   $err .= "User Agent: " .$_SERVER['HTTP_USER_AGENT'] ."\n";
   $err .= "Time:{$dt}\n";
   $err .= "Error: {$errmsg}\n";
   $err .= "Script: {$filename}\n";
   $err .= "URL: $url\n";
   $err .= "Line Number: {$linenum}\n";

   mail(EMAIL_WEBDEV, "SERVER ERROR", $err);
}

$old_error_handler = set_error_handler("userErrorHandler",E_WARNING);

?>