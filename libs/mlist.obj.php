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
  public $fct = "";

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

  function sendToAll() {
    if (!method_exists('Mlist', $this->fct)) {
      return false;
    }
    $fct = $this->fct;
    $mlc = Mlist::$fct();
    foreach($this->a_logins as $l) {
      $this->sendTo($l, $mlc);
      echo "[-] Sending mail to ".$l->email."\n";
    }
  }

  public function sendTo($login, $content) {
    global $config;
    $from = '"'.$config['mailName']."\" <".$config['mailFrom'].">";
    $headers = "";
    $headers = "From: $from\r\n";
    $headers .= "Reply-to: ".$config['mailFrom']."\r\n";
    $headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n";

    mail($login->email, "[SUNSOLVE] ".$this->name, $content, $headers);

    return true;
  }

  public function example() {
    if (!method_exists('Mlist', $this->fct)) {
      return false;
    }
    $fct = $this->fct;
    $mlc = Mlist::$fct();
    return $mlc;
  }

 /**
  * Implementation of mailling list text generation for recurrents ones
  *
  */
  static public function patchesWeekly() {
    global $config;
    $txt = $config['mlist']['header']."\n";
    $lwpatches = array();

    $p_stop = time();
    $p_start = $p_stop - (60*60*24*7);
    $d_stop = date(HTTP::getDateFormat(), $p_stop);
    $d_start = date(HTTP::getDateFormat(), $p_start);

    $txt .= "<h2>Patches released from $d_start and $d_stop</h2>\n";
    
    $table = "`patches`";
    $index = "`patch`, `revision`";
    $where = "WHERE `releasedate` > $p_start AND `releasedate` < $p_stop";
    $where .= " ORDER BY `releasedate` ASC";

    $txt .= "<ul>";
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    { 
      foreach($idx as $t) {
        $k = new Patch($t['patch'], $t['revision']);
        $k->fetchFromId();
        $k->fetchBugids();
        $txt .= "<li>".$k->link(true)." released on ".date(HTTP::getDateFormat(), $k->releasedate)." - ".$k->synopsis."\n\n";
        $txt .= "<ul style=\"list-style-type: square\">";
        foreach($k->a_bugids as $b) {
	  $txt .= "\t<li>".$b->link()." ".$b->synopsis."</li>\n";
	}
        $txt .= "</ul></li>";
      }
    }
    $txt .= "</ul>";

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
                        "fct" => SQL_PROPE,
                        "frequency" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "sdesc" => "sdesc",
                        "example" => "example",
                        "fct" => "fct",
                        "frequency" => "frequency"
                 );
  }

}
?>
