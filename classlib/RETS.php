<?

class ROCKETS_RETS {

  /*
   *  RETS_Class.php - simple RETS class in php 5 using libcurl
   *  Copyright (C) 2008 Tony Russo 
   *  
   *  All rights reserved.
   *  Permission is hereby granted, free of charge, to use, copy or modify this softeare
   *
   *  Methods Supported: Login, GetMetadata, Search, Logout and Logging.
   *  This class lacks methods for xmlparser, GetObject and storage methods.
   *
   *  Seems to work with the following servers:
   *    Easy List MLS
   *    FBS - FLXMLS
   *    First American - Innovia
   *    First American - MLXchange (older server & new Rets Pro server) with User-Agent Authtincation
   *    First American - Tempo  (Tempo 3 & Tempo 5)
   *    Fidelity - Paragon 3
   *    Rapattoni - RapattoniMLS
   *    Solid Earth - LIST-IT
  */

  private $ch               = '';
  public  $host             = '';
  private $account          = '';
  private $password         = '';
  private $user_agent       = '';
  private $user_agent_pwd   = '';
  private $user_agent_auth  = false;
  private $rets_version     = 'RETS/1.5';
  public  $cookie_file      = '/rets_class_cookie.txt';
  private $standard_names   = false;
  private $post_requests    = true;
  private $compact_decoded  = true;
  private $http_logging     = false;
  private $log_file         = '';
  private $headers          = array();
  private $login_path       = '';
  private $logout_path      = '';
  private $search_path      = '';
  private $getmetadata_path = '';
  private $getobject_path   = '';
  private $rets_request_id  = '';
  private $exec_count       = 0;
  private $format           = '';
  public  $response         = '';
  private $main_start_time  = '';
  private $main_end_time    = '';
  private $sessionid        = '';
  public  $connect_str      = '';
  public  $logout_str       = '';




  function init ( $host, $account, $password, $user_agent, $user_agent_pwd, $user_agent_auth, $rets_version,
                  $standard_names, $post_requests, $compact_decoded, $http_logging, $log_file )
  {  //initilize varialbes
    if( file_exists( $this->cookie_file ) ) { unlink ($this->cookie_file); }

    $url_parts             = parse_url($host);
    $this->host            = "{$url_parts['scheme']}://{$url_parts['host']}" . (isset($url_parts['port']) ? ":{$url_parts['port']}" : '');
    $this->account         = $account;
    $this->password        = $password;
    $this->user_agent      = $user_agent;
    $this->user_agent_pwd  = $user_agent_pwd;
    $this->user_agent_auth = $user_agent_auth;
    $this->rets_version    = $rets_version;
    $this->standard_names  = $standard_names;
    $this->post_requests   = $post_requests;
    $this->compact_decoded = $compact_decoded;
    $this->http_logging    = $http_logging;
    $this->log_file        = $log_file;
    $this->headers[]       = "RETS-Version: {$rets_version}";
    if ($this->user_agent_pwd <> null ) { $this->headers[]     = $this->add_UA_header(); }

    $this->ch = curl_init();
    curl_setopt($this->ch, CURLOPT_FRESH_CONNECT, true);
    //curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);

  } //end functio init




  function add_UA_header ()
  { // add RETS-UA header - tested with First American MlxCHANGE rets servers
    $user_agent_str = $this->user_agent;
    $pos = strpos($user_agent_str, '/');
    if ($pos > -1) { $user_agent_str = trim(substr($user_agent_str, 0, $pos)); }
    $A1en = md5($user_agent_str . ':' . $this->user_agent_pwd);
    return 'RETS-UA-Authorization: Digest '.  md5($A1en .':'. $this->rets_request_id.':'.$this->sessionid.':'.$this->rets_version);
  } //end function add_UA_header()




  function get_sessionid()
  { //return the curl session id available after login
    return $this->sessionid;
  } //end function get_sessionid




  private function get_CURL_SessionID()
  {  //use regex to get curl session id from first sent header
     preg_match("/JSESSIONID=(.*)/im", $this->response['request_header'], $matches);

     if( isset($matches[1])) {
       $SessionID = $matches[1];
     } else {
       $SessionID = '';
     }
     return $SessionID;
   } //end function get_CURL_SessionID




  function login ( $host, $account, $password, $user_agent, $user_agent_pwd, $user_agent_auth, $rets_version,
                   $standard_names, $post_requests, $compact_decoded, $http_logging, $log_file )
  { //connect and login to RETS server
     $this->init ( $host, $account, $password, $user_agent, $user_agent_pwd, $user_agent_auth, $rets_version,
                   $standard_names, $post_requests, $compact_decoded, $http_logging, $log_file );

    $result = $this->exec( $host );     //first login attempt

     if ( !$result ) {
        $this->connect_str  =  'Rets Reply code: ' . $rets->response['rets_replycode'] . "\r\n";
        $this->connect_str .=  'Rets Reply text: ' . $rets->response['rets_replytext'] . "\r\n";
        $this->connect_str .=  'Unable to login: ' . $rets->response['http_code'] . "\r\n";
     } else {
       $this->connect_str =  'Connected ' . date("F j, Y, H:i:s") . "\r\n";
     }
	// echo "HTTP CODE: " .$rets->response['http_code'];
	// print_r($result);

    $this->log_write( "Trying ...\r\n  Host: {$this->host}\r\n  {$this->connect_str}\r\n" );

    $this->sessionid = $this->get_CURL_SessionID();

    if ($this->response['http_code'] <> 200) {
      $result = $this->exec( $host );
      $this->log_write();
    }    //try again if not successfull

    //return response or false if not connected
    if ($this->response['http_code'] <> 200 OR $this->response['rets_replycode'] <> 0 ) {
      $result = false;
    } else {
      $this->login_path       = $this->get_path($result, 'login');
      $this->logout_path      = $this->get_path($result, 'logout');
      $this->search_path      = $this->get_path($result, 'search');
      $this->getmetadata_path = $this->get_path($result, 'getMetadata');
      $this->getobject_path   = $this->get_path($result, 'getObject');
    }
    
    return $result;

  } //end function login




  function get_path ($text, $path)
  {  //get the RETS path information after logging
    $result = preg_match('/^'.$path.'.*/im', $text, $matches);

    if ($result === FALSE OR count($matches) == 0 ) {
      $path = '';
    } else {
      list($key, $url )= explode( '=', $matches[0]);
    }
    $url_parts = parse_url( trim($url," \r\n") );

    return $url_parts['path'];
  } //end function get_path




  function logout ()
  {  //logout from RETS server
    $response = $this->exec( $this->host.$this->logout_path );
    curl_close($this->ch);
    

    if ( !$response ) {
       $this->logout_str  =  'Rets Reply code: ' . $rets->response['rets_replycode'] . "\r\n";
       $this->logout_str .=  'Rets Reply text: ' . $rets->response['rets_replytext'] . "\r\n";
       $this->logout_str .=  'Unable to login: ' . $rets->response['http_code'] . "\r\n";
    } else {
      $this->logout_str =  'Logout ' . date("F j, Y, H:i:s") . "\r\n";
    }

    $this->log_write( &$this->logout_str, false );


    if ($this->response['http_code'] <> 200 OR $this->response['rets_replycode'] <> 0 ) {
      return false;
    } else {
      return $response;
    }
  } //end function logout




  function getMetadata( $format, $id, $type )
  {  // RETS Get metadata method
    //$format = "Format=COMPACT&ID=0&Type=METADATA-RESOURCE";
    $data  = "Format={$format}&ID={$id}&Type={$type}";

    $result = $this->exec( $this->host.$this->getmetadata_path, &$data );

    $this->log_write();

    return $result;
    
    
  } //end function getMetadata




  function Search( $Resource, $Class, $Count, $Format, $Limit, $QueryType, $StandardNames, $Select, $Query)
  { // RETS search method
    $data ="Class={$Class}"
              ."&Count={$Count}"
              ."&Format={$Format}"
              ."&Limit={$Limit}"
              ."&Query={$Query}"
              ."&QueryType={$QueryType}"
              ."&SearchType={$Resource}"
              .( ($Select == '' OR $Select == '*') ? '' : "&Select={$Select}")
              ."&StandardNames=" . ($StandardNames == true ? 1 : 0 );
    $result = $this->exec( $this->host.$this->search_path, &$data );

    $this->log_write();

    return $result;
  } //end function Search




  function exec( $url, &$data = '' )
  {  //execute CURL method
    $this->exec_count++;
    $options = array( CURLOPT_URL            => $url,
                      CURLOPT_HEADER         => true,
                      CURLOPT_USERAGENT      => $this->user_agent,
                      CURLOPT_HTTPHEADER     => $this->headers,
                      CURLOPT_POST           => $this->post_requests,
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_COOKIEFILE     => $this->cookie_file,
                      CURLOPT_COOKIEJAR      => $this->cookie_file,
                      CURLINFO_HEADER_OUT    => true,
                      CURLOPT_POSTFIELDS     => $data,
                      CURLOPT_CONNECTTIMEOUT => 30,
                      CURLOPT_VERBOSE        => true,
                      
                     );

    if (!$this->user_agent_auth )  {
         $options[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST|CURLAUTH_BASIC;
         $options[CURLOPT_USERPWD]  = $this->account.":".$this->password;
     } elseif ($this->exec_count <> 1 ) {
         $options[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
         $options[CURLOPT_USERPWD]  = $this->account.":".$this->password;
     }
	 //print_r($options);
    curl_setopt_array($this->ch, $options);
    $response = curl_exec($this->ch);


    $error = curl_error($this->ch);
    if ( $error != '' ) {
       $this->response['curl_error'] = $error;
       $this->response['request_header'] ='';
       $this->response['received_header']='';
       $this->response['received_body']  ='';
       $this->response['http_code']      ='';
    } else {
       $this->response = curl_getinfo( $this->ch );
       $this->response['curl_error'] = '';
       $this->response['received_header'] = substr( $response, 0, $this->response['header_size'] );
       $this->response['received_body'] = substr( $response, $this->response['header_size'] );
    }
       $this->response['data'] = $data;

	//echo "DATA: " .$response;
	
    $xml = @simplexml_load_string( $this->response['received_body'] );
    if (!$xml ) {
      $this->response['rets_replycode'] = '';
      $this->response['rets_replytext'] = '';
    } else {
      $this->response['rets_replycode'] = $xml->attributes()->ReplyCode;
      $this->response['rets_replytext'] = $xml->attributes()->ReplyText;
    }

    return $this->response['received_body'];

  } //end function rets_exec




  function log_write( $log_str = '', $start = true )
  {  //write HTTP headers to log, optionally add text just before or after the log entry

    if ($this->http_logging ) {

      $handle = fopen ( $this->log_file, 'a' ) or die ('FATIAL Error opening log file: ' . $this->log_file . "\r\n" );


      $str  = ">>>SENT\r\n" . trim($this->response['request_header'], "\r\n") . "\r\n";
      $str .= ($this->response['data'] == ''  ? '' : $this->response['data'] . "\r\n" ) . "\r\n\r\n";

//      $str .= "total_time: {$this->response['total_time']}\r\n";
//      $str .= "connect_time: {$this->response['connect_time']}\r\n";
//      $str .= "pretransfer_time: {$this->response['pretransfer_time']}\r\n";
//      $str .= "speed_download: {$this->response['speed_download']}\r\n";
//      $str .= "starttransfer_time: {$this->response['starttransfer_time']}\r\n\r\n";

      $str .= "<<<RECEIVED\r\nHEADER\r\n" . trim($this->response['received_header'], "\r\n") . "\r\n";
      $str .= ($this->response == '' ? '' : $this->response['received_body']) . "\r\n\r\n";

      if ( $start ) {
        fwrite($handle, $log_str . $str  )  or die ('FATIAL Error writing to log file: ' . $this->log_file );
      } else {
        fwrite($handle, $str . $log_str  )  or die ('FATIAL Error writing to log file: ' . $this->log_file );
      }
      $result = fclose($handle) or die ('FATIAL Error closing log file: ' . $this->log_file );
    }

  } //end fuction log_write


} //end rets class

?>