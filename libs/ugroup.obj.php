<?php
/**
 * UGroup object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class UGroup extends mysqlObj
{
  public $id = -1;
  public $name = '';
  public $desc = '';
  public $id_owner = -1;
  public $added = -1;
  public $updated = -1;

  /* Lists */
  public $a_users = array();
  public $a_srv = array();

  public function update() {
    $this->updated = time();
    parent::update();
  }

  public function delete() {
    $this->fetchUsers();
    foreach($this->a_users as $l) $this->delUser($l);
    parent::delete();
  }
  public function insert() {
    $this->added = time();
    parent::insert();
  }

  /* Servers */
  function fetchSrv($all=0) {

    $this->a_srv = array();
    $table = "`jt_ugroup_srv`";
    $index = "`id_srv`, `w`";
    $where = "WHERE `id_ugroup`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Server($t['id_srv']);
        $k->fetchFromId();
        $k->w = $t['w'];
        if ($all) $k->fetchPLevels(0);
        array_push($this->a_srv, $k);
      }
    }
    return 0;
  }

  function addSrv($k) {

    $table = "`jt_ugroup_srv`";
    $names = "`id_srv`, `id_ugroup`, `w`";
    $values = "'$k->id', '".$this->id."', '".$k->w."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_srv, $k);
    return 0;
  }

  function delSrv($k) {

    $table = "`jt_ugroup_srv`";
    $where = " WHERE `id_srv`='".$k->id."' AND `id_ugroup`='".$this->id."'";

    mysqlCM::getInstance()->delete($table, $where);
    foreach ($this->a_srv as $ak => $v) {
      if (!strcmp($k->name, $v->name)) {
        unset($this->a_srv[$ak]);
      }
    }
    return 0;
  }

  function isSrv($k) {
    foreach($this->a_srv as $ko)
      if (!strcasecmp($ko->name, $k->name))
        return TRUE;
    return FALSE;
  }

  /* Users */
  function fetchUsers($all=1) {

    $this->a_users = array();
    $table = "`jt_ugroup_users`";
    $index = "`id_user`";
    $where = "WHERE `id_ugroup`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Login($t['id_user']);
        $k->fetchFromId();
        array_push($this->a_users, $k);
      }
    }
    return 0;
  }

  function addUser($k) {

    $table = "`jt_ugroup_users`";
    $names = "`id_user`, `id_ugroup`";
    $values = "'$k->id', '".$this->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_users, $k);
    return 0;
  }

  function delUser($k) {

    $table = "`jt_ugroup_users`";
    $where = " WHERE `id_user`='".$k->id."' AND `id_ugroup`='".$this->id."'";

    mysqlCM::getInstance()->delete($table, $where);
    foreach ($this->a_users as $ak => $v) {
      if (!strcmp($k->username, $v->username)) {
        unset($this->a_users[$ak]);
      }
    }
    return 0;
  }

  function isUser($k) {
    foreach($this->a_users as $ko)
      if (!strcasecmp($ko->username, $k->username))
        return TRUE;
    return FALSE;
  }

  public function countServers() {
    $c = count($this->a_srv);
    if ($c) return $c;
     
    return MysqlCM::getInstance()->count("jt_ugroup_srv", " WHERE `id_ugroup`='".$this->id."'");
  }


  public function countUsers() {
    $c = count($this->a_users);
    if ($c) return $c;
     
    return MysqlCM::getInstance()->count("jt_ugroup_users", " WHERE `id_ugroup`='".$this->id."'");
  }

  public function __toString() {
    return $this->name;
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "u_group";
    $this->_nfotable = NULL;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE|SQL_EXIST,
                        "desc" => SQL_PROPE,
                        "id_owner" => SQL_PROPE,
                        "added" => SQL_PROPE,
                        "updated" => SQL_PROPE
                 );


    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "desc" => "desc",
                        "id_owner" => "id_owner",
                        "added" => "added",
                        "updated" => "updated"
                 );
  }

}
?>
