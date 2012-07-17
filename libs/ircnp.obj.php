<?php
/**
 * Ircnp object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
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
  
  public $f_irc = 0;
  public $f_twitter = 0;

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
                        "r" => SQL_INDEX,
			"f_irc" => SQL_PROPE,
			"f_twitter" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "p" => "p",
                        "f_irc" => "f_irc",
                        "f_twitter" => "f_twitter",
                        "r" => "r"
                 );
  }
}
?>
