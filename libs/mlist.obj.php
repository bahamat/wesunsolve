<?php
/**
 * MList object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class MList extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $name = "";
  public $sdesc = "";
  public $example = "";
  public $frequency = "";

  public $a_logins = array();

  function liveCount() {
    $table = "`jt_login_mlist`";
    $index = "count(`id_login`) as c";
    $where = "WHERE `id_mlist`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (isset($idx[0]) && isset($idx[0]['c'])) {
        return $idx[0]['c'];
      }
    }
    return 0;
  }

  function fetchLogins() {
    $this->a_logins = array();
    $table = "`jt_login_mlist`";
    $index = "`id_login`";
    $where = "WHERE `id_mlist`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Login($t['id_login']);
        $k->fetchFromId();
        array_push($this->a_logins, $k);
      }
    }
    return 0;
  }

  function addLogin($k) {

    $table = "`jt_login_mlist`";
    $names = "`id_login`, `id_mlist`";
    $values = "'$k->id', '".$this->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_logins, $k);
    return 0;
  }

  function delLogin($k) {

    $table = "`jt_login_mlist`";
    $where = " WHERE `id_login`='".$k->id."' AND `id_mlist`='".$this->id."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_logins as $ak => $v) {
      if ($k->id == $v->id) {
        unset($this->a_logins[$ak]);
      }
    }
    return 0;
  }

  function isLogin($p) {
    foreach($this->a_logins as $po)
      if ($p->id == $po->id)
        return TRUE;
    return FALSE;
  }

 /**
  * Implementation of mailling list text generation for recurrents ones
  *
  */
  static public function patchesWeekly() {
    global $config;
    $txt = $config['mlist']['header'];

    $p_stop = time();
    $p_start = $p_stop - (3600*24*7);
    $d_stop = date(HTTP::getDateFormat(), $p_stop);
    $d_start = date(HTTP::getDateFormat(), $p_start);
    


    $txt .= "\n".$config['mlist']['footer'];
    return $txt;
  }

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "mlist";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE,
                        "sdesc" => SQL_PROPE,
                        "example" => SQL_PROPE,
                        "frequency" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "sdesc" => "sdesc",
                        "example" => "example",
                        "frequency" => "frequency"
                 );
  }

}
?>
