<?php
/**
 * CSS object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class CSS extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $css_file = "";
  public $s_menu = 0;
  public $s_total = 0;
  public $s_box = 0;
  public $s_snet = 0;
  public $p_snet = 0;
  public $s_strip = 0;
  public $is_default = 0;
  

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "css";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "css_file" => SQL_PROPE,
                        "s_menu" => SQL_PROPE,
                        "s_total" => SQL_PROPE,
                        "s_box" => SQL_PROPE,
                        "s_snet" => SQL_PROPE,
                        "p_snet" => SQL_PROPE,
                        "s_strip" => SQL_PROPE,
                        "is_default" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "css_file" => "css_file",
                        "s_menu" => "s_menu",
                        "s_total" => "s_total",
                        "s_box" => "s_box",
                        "s_snet" => "s_snet",
                        "p_snet" => "p_snet",
                        "s_strip" => "s_strip",
                        "is_default" => "is_default"
                 );
  }

}
?>
