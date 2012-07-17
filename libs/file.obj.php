<?php
/**
 * File object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class File extends mysqlObj implements JSONizable
{
  /* Data Var */
  public $id = -1;
  public $name = "";

  /* Optionnal fields */
  public $size = 0;
  public $arch = "";
  public $bits = 0;
  public $md5 = "";
  public $sha1 = "";
  public $pkg = "";
  public $version = "";
  
  /* Lists */
  public $a_patches = array();

  public function __toString() {
    return $this->name;
  }

  public function toJSONArray($osrs=null, $patches=null) {
    $ret = array();
    $ret['path'] = $this->name;
    $ret['size'] = $this->size;
    $ret['md5'] = $this->md5;
    $ret['sha1'] = $this->sha1;
    $ret['pkg'] = $this->pkg;
    $ret['bits'] = $this->bits;
    if ($osrs && count($osrs)) {
      $ret['osrls'] = array();
      foreach($osrs as $osr) {
	$ret['osrls'][] = $osr->toJSONArray();
      }
    }
    if ($patches && count($patches)) {
      $ret['patches'] = array();
      foreach($patches as $patch) {
	$ret['patches'][] = $patch->toJSONArray();
      }
    }
    return $ret;
  }

  public function toJSON($osrs=null, $patches=null) {
    return json_encode($this->toJSONArray($osrs, $patches));
  }

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "files";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name"
                 );
  }

}
?>
