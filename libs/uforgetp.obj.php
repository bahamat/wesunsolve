<?php
/**
 * UForgetp object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class UForgetp extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $id_login = -1;
  public $code = "";
  
  public $o_login = null;

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "u_forgetp";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "id_login" => SQL_PROPE,
                        "code" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "id_login" => "id_login",
                        "code" => "code"
                 );
  }

}
?>
