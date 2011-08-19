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
  

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "p_readmes";
    $this->_nfotable = "";
    $this->_my = array(
                        "patch" => SQL_INDEX,
                        "revision" => SQL_INDEX,
                        "when" => SQL_INDEX,
                        "txt" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "patch" => "patch",
                        "revision" => "revision",
                        "when" => "when",
                        "txt" => "txt"
                 );
  }

}
?>
