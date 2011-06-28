<?php
/**
 * Server object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Server extends mysqlObj
{
  public $id = -1;
  public $id_owner = "";
  public $name = "";
  public $comment = "";
  public $added = -1;
  public $updated = -1;
  
  public $o_owner = null;
  public $a_plevel = array();

  public function fetchPLevels() {
    $table = "`u_plevel`";
    $index = "`id`";
    $where = "WHERE `id_server`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $g = new PLevel($t['id']);
        $g->fetchFromId();
        $g->fetchPatches(0);
        array_push($this->a_plevel, $g);
      }
    }
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
    $this->_table = "u_servers";
    $this->_nfotable = NULL;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "id_owner" => SQL_PROPE|SQL_EXIST,
                        "name" => SQL_PROPE,
                        "comment" => SQL_PROPE,
                        "added" => SQL_PROPE,
                        "updated" => SQL_PROPE
                 );


    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "id_owner" => "id_owner",
                        "name" => "name",
                        "comment" => "comment",
                        "added" => "added",
                        "updated" => "updated"
                 );
  }

}
?>
