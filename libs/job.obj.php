<?php
 /**
  * Job object
  * @author Gouverneur Thomas <tgo@espix.net>
  * @copyright Copyright (c) 2007-2011, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage job
  * @category classes
  * @filesource
  */

if (!defined('S_NONE')) {
 define ('S_NONE',   0);
 define ('S_NEW', 1);
 define ('S_RUN', 2);  
 define ('S_FAIL', 4);  
 define ('S_DONE', 8);   
 //define ('', 16);  
 //define ('', 32);  
}



class Job extends mysqlObj
{
  public $id = -1;		/* ID in the MySQL table */
  public $class = "";
  public $fct = "";
  public $id_owner = -1;
  public $d_start = -1;
  public $d_stop = -1;
  public $pid = -1;
  public $id_log = -1;
  public $arg = "";
  public $added = -1;
  public $state = S_NONE;

  public $o_owner = null;
  private $_icmid = null;
  private $_jlog = null;

  /* Display fct */

  public function stateStr() {
    switch($this->state) {
      case S_NONE:
	return "No state";
	break;
      case S_NEW:
	return "NEW";
	break;
      case S_RUN:
	return "RUNNING";
        break;
      case S_FAIL:
	return "FAILED";
        break;
      case S_DONE:
	return "DONE";
        break;
    }
    return "UNKNOWN";
  }

  /* process fct */

  public function fetchLogin() {
    $this->o_owner = new Login($this->id_owner);
    return $this->o_owner->fetchFromId();
  }

  public function runJob()
  {
    $this->_jlog = new JobLog();
    $this->_jlog->insert();
    $this->id_log = $this->_jlog->id;
    $this->d_start = time();
    $this->state = S_RUN;
    $this->pid = getmypid();
    $this->update();

    if (!class_exists($this->class) || !method_exists($this->class, $this->fct)) {
      $this->state = S_FAIL;
      $this->_jlog->log = "Error, can't find class or method ".$this->class."::".$this->fct."\n";
      $this->_jlog->rc = -1;
      $this->_jlog->insert();
      $this->id_log = $this->_jlog->id;
      $this->update();
      return;
    }

    $c = $this->class;
    $f = $this->fct;
    $ret = $c::$f($this,$this->arg);

    $this->d_stop = time();
    $this->_jlog->rc = $ret;

    $this->_icmid->log("finished ".$this->class."::".$this->fct." Duration: ".$this->d_stop - $this->d_start." sec - rc=$ret");

    if ($ret) {
      $this->state = S_FAIL;
    } else {
      $this->state = S_DONE;
    }

    /* Update job log */
    $r = $this->_jlog->update();
    $this->update();
  }

  public function log($str) {
    if ($this->_jlog) $this->_jlog->log .= $str."\n";
    $this->_jlog->update();
  }

  /* ctor */
  public function __construct($id=-1, $daemon=null)
  { 
    $this->id = $id;
    $this->_table = "jobs";
    $this->_icmid = $daemon;

    $this->added = time();

    $this->_my = array( 
			"id" => SQL_INDEX, 
		        "class" => SQL_PROPE,
			"fct" => SQL_PROPE,
			"id_owner" => SQL_PROPE,
			"d_start" => SQL_PROPE,
			"d_stop" => SQL_PROPE,
			"pid" => SQL_PROPE,
			"id_log" => SQL_PROPE,
			"arg" => SQL_PROPE,
			"added" => SQL_PROPE,
			"state" => SQL_PROPE
 		 );


    $this->_myc = array( /* mysql => class */
			"id" => "id", 
			"class" => "class",
			"fct" => "fct",
			"id_owner" => "id_owner",
			"d_start" => "d_start",
			"d_stop" => "d_stop",
			"pid" => "pid",
			"id_log" => "id_log",
			"arg" => "arg",
			"added" => "added",
			"state" => "state"
 		 );
  }
}

?>
