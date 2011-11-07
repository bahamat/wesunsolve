<?php
/**
 * Curl class
 *
 * @author Gouverneur Thomas <thomas@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package libs
 * @subpackage various
 * @category libs
 * @filesource
 */


class Curl
{
  private $_curl = null;
  private $_useragent = "";
  private $_url = "";
  private $_method = "GET";
  private $_expect = "txt";
  private $_headers = array();
  private $_timeout = 3;
  private $_cookiefile = "";
  private $_data = array();
  
  public function init() {

    if ($this->_curl) 
      curl_close($this->_curl);

    $this->_curl = curl_init();

    curl_setopt($this->_curl, CURLOPT_URL, $this->_url); 
    curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $this->_headers); 
    curl_setopt($this->_curl, CURLOPT_TIMEOUT, $this->_timeout); 
    curl_setopt($this->_curl, CURLOPT_MAXREDIRS, 5); 
    curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true); 
    curl_setopt($this->_curl, CURLOPT_COOKIEJAR, $this->_cookiefile); 
    curl_setopt($this->_curl, CURLOPT_COOKIEFILE, $this->_cookiefile); 

    if ($this->_expect == "bin") {
      curl_setopt($this->_curl, CURLOPT_BINARYTRANSFER, true);
    }

  } 

  public function setAgent($s) { $this->_useragent = $s; }
  public function setUrl($s) { $this->_url = $s; }
  public function addHeader($s) { array_push($this->_headers, $s); }
  public function setCookieFile($s) { $this->_cookiefile = $s; }
  public function addData($s) { array_push($this->_data, $s); }
  public function setUrl($url, $method, $exp) { $this->_url = $url; $this->_method = $method; $this->_expect = $exp; }

  public function __construct() {

    $this->_useragent = "Mozilla/5.0 (X11; Linux i686; rv:7.0.1) Gecko/20100101 Firefox/7.0.1";
  } 

  public function __destruct() {

    if ($this->_curl) curl_close($this->_curl);
  }
}

?>
