<?php
/**
 * User Custom List
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class UCList extends mysqlObj
{
  public $id = -1;
  public $id_login = -1;
  public $name = "";
  public $added = -1;
  public $updated = -1;
  
  public $o_login = null;
  public $a_patches = array();

  function fetchFiles() {
    foreach($this->a_patches as $p) {
      $p->fetchFiles();
    }
    return 0;
  }

  function fetchPatches($all=1) {
    $this->a_patches = array();
    $table = "`jt_patches_uclist`";
    $index = "`patch`, `rev`";
    $where = "WHERE `id_uclist`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['patch'], $t['rev']);
        if ($all) { 
          $k->fetchFromId();
        }
        
        array_push($this->a_patches, $k);
      }
    }
    return 0;
  }

  function addPatch($k) {

    $table = "`jt_patches_uclist`";
    $names = "`patch`, `rev`, `id_uclist`";
    $values = "'$k->patch', '".$k->revision."', '".$this->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_patches, $k);
    return 0;
  }

  function delPatch($k) {

    $table = "`jt_patches_uclist`";
    $where = " WHERE `patch`='".$k->patch."' AND `rev`='".$k->revision."' AND `id_uclist`='".$this->id."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_patches as $ak => $v) {
      if ($k->patch == $v->patch && $k->revision == $v->revision) {
        unset($this->a_patches[$ak]);
	break;
      }
    }
    return 0;
  }

  function isPatch($p) {
    foreach($this->a_patches as $po)
      if ($p->patch == $po->patch && $p->revision == $po->revision)
        return TRUE;
    return FALSE;
  }
 

  public function update() {
   $this->updated = time();
   parent::update();
  }

  public function insert() {
    $this->added = time();
    parent::insert();
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "u_clist";
    $this->_nfotable = NULL;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "id_login" => SQL_PROPE|SQL_EXIST,
                        "name" => SQL_PROPE,
                        "added" => SQL_PROPE,
                        "updated" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "id_login" => "id_login",
                        "name" => "name",
                        "added" => "added",
                        "updated" => "updated"
                 );
  }

}
?>
