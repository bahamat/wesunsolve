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
  public $is_twitter = 1;
  public $is_irc = 1;
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
                        "is_twitter" => SQL_PROPE,
                        "is_irc" => SQL_PROPE,
                        "link" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "synopsis" => "synopsis",
                        "date" => "date",
                        "is_twitter" => "is_twitter",
                        "is_irc" => "is_irc",
                        "link" => "link"
                 );
  }

}
?>
