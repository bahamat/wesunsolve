<?php
 /**
  * Daemon object
  * @author Gouverneur Thomas <tgo@espix.net>
  * @copyright Copyright (c) 2007-2011, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage device
  * @category classes
  * @filesource
  */


interface Daemonizable {

  public function run();
  public function start();
  public function cleanup();
  public function sigterm();
  public function sighup();
  public function sigkill();
  public function sigusr1();
  public function sigusr2();
}

class Daemon
{
  private $pid = 0;
  
  public function __construct($obj, $f) 
  { 
    if (!defined('SIGHUP')){
        trigger_error('PHP is compiled without --enable-pcntl directive', E_USER_ERROR);
    }
    declare(ticks = 1);

    if (!pcntl_signal(SIGTERM,array($obj,'sigterm'))) {
      echo "[!] Unable to redirect SIGTERM\n";
    }
    if (!pcntl_signal(SIGHUP,array($obj,'sighup'))) {
      echo "[!] Unable to redirect SIGHUP\n";
    }
    if (!pcntl_signal(SIGUSR1,array($obj,'sigusr1'))) {
      echo "[!] Unable to redirect SIGUSR1\n";
    }
    if (!pcntl_signal(SIGUSR2,array($obj,'sigusr2'))) {
      echo "[!] Unable to redirect SIGUSR2\n";
    }
 
    if (!$f) {

      $this->pid = pcntl_fork();
      if ($this->pid) {
        echo "Forked\n";
        exit(); // parent
      } else {
        $this->pid = posix_getpid();
        $obj->start();
        while(1) {
          if ($obj->run()) exit(0);
        }
      }
    } else {
      $obj->start();
      while(1) {
        if ($obj->run()) exit(0);
      }
    }
  }
}

?>
