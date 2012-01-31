<?php
/**
 * Readme object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Readme extends mysqlObj
{
  /* Data Var */
  public $patch = -1;
  public $revision = -1;
  public $when = -1;
  public $txt = "";
  public $diff = "";

  public $o_patch = null;

  public function fetchPatch() {
    $this->o_patch = new Patch($this->patch, $this->revision);
    return $this->o_patch->fetchFromId();
  }
  

  public static function fetchLast10() {
    $ret = array();

    $table = "`p_readmes`";
    $index = "`patch`, `revision`, `when`";
    $where = "where `when`!=0 order by `when` desc limit 0,10";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['patch'], $t['revision']);
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
  public function __construct($patch=-1,$revision=0,$when=0)
  {
    $this->patch = $patch;
    $this->revision = $revision;
    $this->when = $when;
    $this->_table = "p_readmes";
    $this->_nfotable = "";
    $this->_my = array(
                        "patch" => SQL_INDEX,
                        "revision" => SQL_INDEX,
                        "when" => SQL_INDEX,
                        "txt" => SQL_PROPE,
                        "diff" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "patch" => "patch",
                        "revision" => "revision",
                        "when" => "when",
                        "diff" => "diff",
                        "txt" => "txt"
                 );
  }

}
?>
