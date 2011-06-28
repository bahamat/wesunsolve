<?php
/**
 * News object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class News extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $synopsis = "";
  public $date = 0;
  public $link = "";
  

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "rss_news";
    $this->_date = time();
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "synopsis" => SQL_PROPE,
                        "date" => SQL_PROPE,
                        "link" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "synopsis" => "synopsis",
                        "date" => "date",
                        "link" => "link"
                 );
  }

}
?>
