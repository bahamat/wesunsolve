<?php
/**
 * Keyword object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Keyword extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $keyword = "";
  
  /* Lists */
  public $a_patches = array();

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "keywords";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "keyword" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "keyword" => "keyword"
                 );
  }

}
?>
