<?php
/**
 * BReadme object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class BReadme extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $when = -1;
  public $txt = "";
  public $diff = "";

  public $o_bundle = null;

  public function fetchBundle() {
    $this->o_bundle = new Bundle($this->id);
    return $this->o_bundle->fetchFromId();
  }
  

  public static function fetchLast10() {
    $ret = array();

    $table = "`b_readmes`";
    $index = "`id`, `when`";
    $where = "where `when`!=0 order by `when` desc limit 0,10";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Bundle($t['id']);
        $k->fetchFromId();
	$k->lmod = $t['when'];
        array_push($ret, $k);
      }
    }
    return $ret;
  }

 /**
  * Constructor
  */
  public function __construct($id=-1,$when=0)
  {
    $this->id = $id;
    $this->when = $when;
    $this->_table = "b_readmes";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "when" => SQL_INDEX,
                        "txt" => SQL_PROPE,
                        "diff" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "when" => "when",
                        "diff" => "diff",
                        "txt" => "txt"
                 );
  }

}
?>
