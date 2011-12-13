<?php
/**
 * File object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class File extends mysqlObj
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
