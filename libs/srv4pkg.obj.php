<?php
/**
 * SRV4Pkg object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class SRV4Pkg extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $name = "";
  public $description = "";
  public $added = 0;
  public $updated = 0;

  /* Attributes for jt_ tables */
  public $arch = "";
  public $version = "";

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "srv4pkg";
    $this->_nfotable = null;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE,
                        "description" => SQL_PROPE,
                        "added" => SQL_PROPE,
                        "updated" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "description" => "description",
                        "added" => "added",
                        "updated" => "updated"
                 );


  }

}
?>
