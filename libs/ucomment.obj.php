<?php
/**
 * UComment object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class UComment extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $id_login = -1;
  public $comment = "";
  public $rate = 0;
  public $is_private = 0;
  public $type = "";
  public $id_on = -1;
  public $added = -1;

  public $o_login = null;

  public function fetchLogin() {
    $this->o_login = new Login($this->id_login);
    return $this->o_login->fetchFromId();
  }
  
  public function insert() {
    $this->added = time();
    parent::insert();
  }


 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "u_comments";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "id_login" => SQL_PROPE,
                        "comment" => SQL_PROPE,
                        "type" => SQL_PROPE,
                        "id_on" => SQL_PROPE,
                        "rate" => SQL_PROPE,
                        "is_private" => SQL_PROPE,
                        "added" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "id_login" => "id_login",
                        "comment" => "comment",
                        "is_private" => "is_private",
                        "id_on" => "id_on",
                        "type" => "type",
                        "rate" => "rate",
                        "added" => "added"
                 );
  }

}
?>
