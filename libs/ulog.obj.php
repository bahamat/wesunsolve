<?php
/**
 * ULog object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class ULog extends mysqlObj
{
  /* Data Var */
  public $id_login = -1;
  public $what = "";
  public $id_link = 0;
  public $when = -1;
  
  public $o_link = null;

  public function fetchLink() {

  }

  public function insert() {
    $this->when = time();
    parent::insert();
  }


 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "u_history";
    $this->_nfotable = "";
    $this->_my = array(
                        "id_login" => SQL_INDEX,
                        "what" => SQL_INDEX,
                        "id_link" => SQL_INDEX,
                        "when" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id_login" => "id_login",
                        "what" => "what",
                        "id_link" => "id_link",
                        "when" => "when"
                 );
  }

}
?>
