<?php
/**
 * SSHSession
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage ssh
 * @filesource
 */


class SSHSession {
  
  public $hostname;
  public $port;
  public $user;
  public $password;
  public $sshkey;
  
  private $_con, $_connected, $_shell;

  public function connect() {

    $callbacks = array('disconnect' => '$this->notify_disconnect');

    if (!($this->_con = @ssh2_connect($this->hostname, $this->port))) {

      return -1;
    }
    if (!empty($this->password)) { /* password auth */

      if (!@ssh2_auth_password($this->_con, $this->user, $this->password)) {
        return -2;
      }
    } else if (!empty($this->sshkey)){ /* key auth ? */

      return -4;
    } else { /* cannot authenticate... */
      return -3;
    }
    $this->_connected = 1;
    return 0;
  }
 
  public function execSecure($c, $timeout=30) {
    $c = $c.";echo \"__COMMAND_FINISHED__\"";
    $time_start = time();
    $buf = "";
    if (!($stream = ssh2_exec($this->_con, $c))) {
      return -1;
    } else {
      stream_set_blocking($stream, true);
      while (true) {
        $wa = NULL;
        $ex = NULL;
        $ra = array($stream);
        $nc = stream_select($ra, $wa, $ex, $timeout);
        if ($nc) {
          $buf .= stream_get_line($stream, 4096, PHP_EOL).PHP_EOL;
          if (strpos($buf,"__COMMAND_FINISHED__") !== false) {
            fclose($stream);
            $buf = str_replace("__COMMAND_FINISHED__\n", "", $buf);
            return $buf;
          }
          if ((time()-$time_start) > $timeout ) {
            fclose($stream);
            return -1;
          }
        } else {
          if ((time()-$time_start) >= $timeout ) {
            fclose($stream);
            return -1;
          }
	}	
      }
    }
  }

  public function exec($c) {

    if (!($stream = ssh2_exec($this->_con, $c))) {
      return -1;
    } else {
      stream_set_blocking($stream, true);
      $data = "";
      while ($buf = fread($stream,4096)) {
        $data .= $buf;
      }
      fclose($stream);
      return $data;
    }
  }

  public function notify_disconnect($reason, $message, $language) {
    $this->_connected = 0;
    $this->_shell = null;
  }

  public function __construct ($h="") {

    if (!function_exists("ssh2_connect")) 
      die("SSHSession::__construct: ssh2_connect doesn't exist. please check your ssh2 php installation.");

    $this->hostname = $h;
    $this->port = 22;

    $this->_connected = 0;
    $this->_con = null;
    $this->_shell = null;
  }

}
