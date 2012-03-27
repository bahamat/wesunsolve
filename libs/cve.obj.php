<?php
 /**
  * CVE object
  * @author Gouverneur Thomas <tgo@espix.net>
  * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage job
  * @category classes
  * @filesource
  */

class CVE extends mysqlObj
{
  public $id = -1;		/* ID in the MySQL table */
  public $name = '';
  public $affect = '';
  public $added = -1;
  public $updated = -1;

  public $a_patches = array();

  function link() {
    return '<a href="/cve/id/'.$this->id.'">'.$this->name.'</a>';
  }

  function __toString() {
    return $this->name;
  }

  function fetchAll($all) {
    $this->fetchPatches($all);
  }

  function viewed() {
    /*placeholder*/
  }

  function addPatch($k) {

    $table = "`jt_patches_cve`";
    $names = "`cveid`, `patchid`, `revision`";
    $values = "'$this->id', '".$k->patch."', '".$k->revision."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_patches, $k);
    return 0;
  }

  function delPatch($k) {

    $table = "`jt_patches_cve`";
    $where = " WHERE `cveid`='".$this->id."' AND `patchid`='".$k->patch."' AND `revision`='".$k->revision."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_patches as $ak => $v) {
      if ($k->patch == $v->patch &&
	  $k->revision == $v->revision) {
        unset($this->a_patches[$ak]);
      }
    }
    return 0;
  }

  function isPatch($k) {
    foreach($this->a_patches as $ko)
      if ($ko->patch == $k->patch &&
	  $ko->revision == $k->revision)
        return TRUE;
    return FALSE;
  }

  function fetchPatches($all=1) {

    $this->a_patches = array();
    $table = "`jt_patches_cve`";
    $index = "`patchid`, `revision`";
    $where = "WHERE `cveid`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['patchid'], $t['revision']);
        if ($all) $k->fetchFromId();
        array_push($this->a_patches, $k);
      }
    }
    return 0;
  }


  public function update() {
    $this->updated = time();
    parent::update();
  }

  public function insert() {
    $this->added = time();
    parent::insert();
  }


  /* ctor */
  public function __construct($id=-1, $daemon=null)
  { 
    $this->id = $id;
    $this->_table = "cve";

    $this->added = time();

    $this->_my = array( 
			"id" => SQL_INDEX, 
		        "name" => SQL_PROPE,
			"affect" => SQL_PROPE,
			"added" => SQL_PROPE,
			"updated" => SQL_PROPE
 		 );


    $this->_myc = array( /* mysql => class */
			"id" => "id", 
			"name" => "name",
			"affect" => "affect",
			"added" => "added",
			"updated" => "updated"
 		 );
  }
}

?>
