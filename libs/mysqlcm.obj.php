<?php
/**
 * MySQL Connection Manager
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2007-2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


if (!defined('SQL_NONE')) {
 define ('SQL_NONE',   0);  /* not used */
 define ('SQL_INDEX', 1);   /* is the property an index ? */
 define ('SQL_WHERE', 2);   /* is the property an part of the where condition when search for object */
 define ('SQL_EXIST', 4);   /* is the property a part of the condition for the object to exist in the db */
 define ('SQL_PROPE', 8);   /* is the property should be fetched ? */
 define ('SQL_SORTA', 16);  /* sort with this field by ASC ? */
 define ('SQL_SORTD', 32);  /* sort with this field by DESC ? */
}

/**
  * MySQL Connection Manager
  * 
  * @category classes
  * @package objects
  * @subpackage config
  * @author Gouverneur Thomas <tgo@ians.be>
  */
class mysqlCM
{
  /**
   * Holds the db link
   */
  private $_link = null;
  /**
   * Keep the latest's query result
   */
  private $_res = null;
  /**
   * Keep the latest's query result count
   */
  private $_nres = null;
  /**
   * Latest error given by the server
   * @var string
   */
  private $_error = null;
  /**
   * Number of rows affected by latest query
   */
  private $_affect = null;

  /**
   * Debug mode
   */
  private $_debug = false;
  private $_dfile = false;
  private $_dfd = null;
  private $_elapsed = 0;
  /**
   * Error logging
   */
  private $_errlog = false;
  private $_errfile = false;
  private $_efd = null;
 
  /**
   * Singleton variable
   */
  private static $_instance;

  /**
   * Returns the singleton instance
   */
  public static function getInstance()
  {
    if (!isset(self::$_instance)) {
     $c = __CLASS__;
     self::$_instance = new $c;
    }
    return self::$_instance;
  }

  public function quote($str) {
    return $this->_link->quote($str);
  }

  /**
   * Enable error logging
   */
  private function _errlog($fname)
  {
    $this->_errlog = true;
    $this->_errfile = $fname;
    $this->_efd = null;
    if (!($this->_efd = fopen($this->_errfile, "a"))) {
      $this->_errfile = "";
      $this->_efd = null;
      $this->_errlog = false;
      return false;
    }
    return true;
  }

  /**
   * Write entry to error log file
   */
  private function _eprint($line, $args = null)
  {
    if ($this->_errlog && $this->_efd && !empty($line)) {
      return vfprintf($this->_efd, $line, $args);
    }
    return false;
  }


  /**
   * Enable debug mode
   */
  private function _debug($fname)
  {
    $this->_debug = true;
    $this->_dfile = $fname;
    $this->_dfd = null;
    if (!($this->_dfd = fopen($this->_dfile, "a"))) {
      $this->_dfile = "";
      $this->_dfd = null;
      $this->_debug = false;
      return false;
    }
    return true;
  }

  /**
   * Write entry to debug log file
   */
  private function _dprint($line, $args = null)
  {
    if ($this->_debug && $this->_dfd) {
      return vfprintf($this->_dfd, $line, $args);
    }
    return false;
  }

  /**
   * Measure the time taken between two call of this function
   */
  private function _time() {
    if (!$this->_elapsed) {
      $this->_elapsed = time();
      return $this->_elapsed;
    }
    else { 
      $ret = (time() - $this->_elapsed); 
      $this->_elapsed = 0; 
      return $ret; 
    }
  }

  /**
   * Destructor
   */
  public function __destruct()
  { 
    global $config;

    if ($this->_link) $this->disconnect();

    if ($config['mysql']['DEBUG'] && $this->_dfd) {
      fclose($this->_dfd);
    }
  }


  /**
   * Constructor
   */
  public function __construct()
  {
    global $config;
    if ($config['mysql']['DEBUG']) {
      $this->_debug($config['mysql']['DEBUG']);
    }
    if ($config['mysql']['ERRLOG']) {
      $this->_errlog($config['mysql']['ERRLOG']);
    }

  }

  /**
   * Avoid the call of __clone()
   */
  public function __clone()
  {
    trigger_error("Cannot clone a singlton object, use ::instance()", E_USER_ERROR);
  }

  /**
   * Accessors
   */

  public function getError() { return $this->_error; }
  public function getNR() { return $this->_nres; }
  public function getAffect() { return $this->_affect; }


  /**
   * Connect to the database
   * store the link resource in $this->_link,
   * @return 0 if ok, non-zero if any error
   */
  public function connect()
  {
    global $config;

    $dbstring = "mysql:host=".$config['mysql']['host'];
    $dbstring .= "; port=".$config['mysql']['port'];
    $dbstring .= "; dbname=".$config['mysql']['db'];
    try {
      $this->_link = new PDO($dbstring, 
                             $config['mysql']['user'], 
                             $config['mysql']['pass']);
    } catch (PDOException $e) {
      $this->_error = $e->getMessage();
      if ($this->_debug)
        $this->_dprint("[".time()."] Connection failed to database ".$config['mysql']['db']."@".$config['mysql']['host'].":".$config['mysql']['port']."\n");
      return -1;
    }
    if ($this->_debug)
      $this->_dprint("[".time()."] Connection succesfull to database ".$config['mysql']['db']."@".$config['mysql']['host'].":".$config['mysql']['port']."\n");
    return 0;
  }

  /**
   * Disconnect the database link;
   * @return 0 if ok, non-zero if any error
   */
  public function disconnect()
  {
    global $config;
    if ($this->_debug)
      $this->_dprint("[".time()."] Connection closed to database ".$config['mysql']['db']."@".$config['mysql']['host'].":".$config['mysql']['port']."\n");

    $this->_link = null;

    return 0;
  }

  /**
   * Count object matching criteria
   * @return -1 if error, else the number of row
   */
  public function count($table, $where="")
  {
    $query = "SELECT COUNT(*) FROM `".$table."` ".$where;
    
    $this->_nres = null;
    
    if (!$this->_query($query))
    {
      $row = $this->_res->fetch(PDO::FETCH_ASSOC);
      if (isset($row['COUNT(*)']))
	$data = $row['COUNT(*)'];
      unset($this->_res);

      return $data;
    }
    else
      return -1;
  }

  /**
   * Query mysql server for select
   * @return datas selected or -1 if error
   */
  public function select($fields, $table, $where="", $sort="")
  {
    $query = "SELECT ".$fields." FROM `".$table."` ".$where." ".$sort;

    $this->_nres = null;

    if (!$this->_query($query))
    {
      $data = array();
      $this->_nres = $this->_link->query("SELECT FOUND_ROWS()")->fetchColumn();
      if ($this->_nres) {
        for ($i=0; $r = $this->_res->fetch(PDO::FETCH_ASSOC); $i++)
          $data[$i] = $r;
      }
      unset($this->_res);
      return $data;
    }
    else 
      return -1;
  }

  /**
   * Insert data into table
   * @return -1 if error, 0 if ok
   */
  public function insert($fields, $values, $table)
  {
    $query = "INSERT INTO ".$table."(".$fields.") VALUES(".$values.")";
    
    if (!$this->_rquery($query))
    {
      $this->_nres = $this->_link->lastInsertId();
      return 0;
    }
    else 
    {
     return -1;
    }
  }

  /**
   * Remove data from table
   * @return -1 if error, else the number of affected rows
   */
  public function delete($table, $cond)
  {
    $query = "DELETE FROM ".$table." ".$cond;
    
    if (!$this->_rquery($query))
    {
      return $this->_affect;
    }
    else
    {
      return -1;
    }
  }

  /**
   * update data in table
   * @return -1 if error, else the number of updated rows
   */
  public function update($table, $set, $where)
  {
    $query = "UPDATE `".$table."` SET ".$set." ".$where;
  
    if (!$this->_rquery($query))
    {
      return 0;
    }
    else
    {
      return -1;
    }
  }

  /**
   * Fetch index of a table following $where condition
   * @return The index datas of the table
   */
  function fetchIndex($index, $table, $where)
  {
    $query = "SELECT ".$index." FROM ".$table." ".$where;

    $this->_nres = null;
    if (!$this->_query($query, null))
    {
      /* TODO: Fix the rowCount being called on a non object ! */
      $this->_nres = $this->_link->query("SELECT FOUND_ROWS()")->rowCount();
      $data = array();
      if ($this->_nres) {
        for ($i=0; $r = $this->_res->fetch(PDO::FETCH_ASSOC); $i++)
          $data[$i] = $r;
      }
      unset($this->_res);
      return $data;
    }
    else 
      return 0;
  }
   
  /**
   * RAW Query database and handle errors
   * @return 0 if ok, non-zero if any error
   */
  private function _rquery($query, $args=null)
  {
    if ($this->_debug) $this->_time();

    unset($this->_res);
    if (($this->_affect = $this->_link->exec($query)) === FALSE) {

      $this->_error = $this->_link->errorInfo();
      $this->_error = $this->_error[2];
      if ($this->_debug) $this->_time();
      if ($this->_errlog) {
        $this->_eprint("[".time()."] Failed _rquery (".$this->_affect."): $query\n");
        $this->_eprint("\tError: ".$this->_error."\n");
      }
      return -1;

    } else if ($this->_affect) {

      if ($this->_debug) $this->_dprint("[".time()."] (".$this->_time().") ".$query."\n");
      return 0;

    } 
    // Log null queries
    /* else {

      if ($this->_errlog) {
        $this->_eprint("[".time()."] Null _rquery: $query\n");
      }
    }
    */
  }

  public function rawQuery($q) {
    return $this->_query($q);
  }

  /**
   * Query database and handle errors
   * @return 0 if ok, non-zero if any error
   */
  private function _query($query, $args=null)
  {
    if ($this->_debug) $this->_time();

    $this->_res = $this->_link->prepare($query);
    if ($this->_res->execute($args)) {

      if ($this->_debug) $this->_dprint("[".time()."] (".$this->_time().") ".$query."\n");

      return 0;
    } else {
      $this->_error = $this->_res->errorInfo();
      $this->_error = $this->_error[2];
      if ($this->_debug) $this->_time();
      if ($this->_errlog) { 
        $this->_eprint("[".time()."] Failed _query: $query\n");
        $this->_eprint("\tError: ".$this->_error."\n");
      }
      return -1;
    }
  }
  
}

?>
