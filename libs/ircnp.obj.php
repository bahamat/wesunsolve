<?php
/**
 * Ircnp object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Ircnp extends mysqlObj
{
  /* Data Var */
  public $p = -1;
  public $r = -1;
  

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "irc_npatchs";
    $this->_nfotable = "";
    $this->_my = array(
                        "p" => SQL_INDEX,
                        "r" => SQL_INDEX
                 );

    $this->_myc = array( /* mysql => class */
                        "p" => "p",
                        "r" => "r"
                 );
  }

}
?>
