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
  public $desc = '';
  public $score = '';
  public $severity = '';
  public $revised = -1;
  public $released = -1;
  public $added = -1;
  public $updated = -1;

  public $a_patches = array();
  public $a_comments = array();

  /* Users comments */
  function fetchComments($all=1) {

    $lm = loginCM::getInstance();
    if (!isset($lm->o_login) || !$lm->o_login) {
      $id = -1;
    } else {
      $id = $lm->o_login->id;
    }
    

    $this->a_comments = array();
    $table = "`u_comments`";
    $index = "`id`";
    $where = "WHERE `type`='cve' AND `id_on`='".$this->id."' AND (`is_private`=0 OR (`id_login`=$id AND `is_private`=1)) ORDER BY `added` ASC";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new UComment($t['id']);
        if ($all) $k->fetchFromId();
        array_push($this->a_comments, $k);
      }
    }
    return 0;
  }


  function link() {
    return '<a href="/cve/id/'.$this->id.'">'.$this->name.'</a>';
  }

  function __toString() {
    return $this->name;
  }

  function fetchAll($all) {
    $this->fetchPatches($all);
    $this->fetchComments($all);
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
			"score" => SQL_PROPE,
			"severity" => SQL_PROPE,
			"desc" => SQL_PROPE,
			"released" => SQL_PROPE,
			"revised" => SQL_PROPE,
			"added" => SQL_PROPE,
			"updated" => SQL_PROPE
 		 );


    $this->_myc = array( /* mysql => class */
			"id" => "id", 
			"name" => "name",
			"affect" => "affect",
			"score" => "score",
			"desc" => "desc",
			"severity" => "severity",
			"released" => "released",
			"revised" => "revised",
			"added" => "added",
			"updated" => "updated"
 		 );
  }
}

?>
