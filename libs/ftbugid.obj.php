<?php
/**
 * Full Text Bugid object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class FTBugid extends mysqlObj
{
  private $_b;
  public $bugid = 0;

  /* Fulltext */
  public $description = "";
  public $comments = "";
  public $keywords = "";
  public $responsible_engineer = "";
  public $synopsis = "";
  public $workaround = "";
  public $raw = "";

  public function insert() {
    $this->bugid = $this->_b->id;
    parent::insert();
  }
  public function update() {
    $this->bugid = $this->_b->id;
    parent::update();
  }


 /**
  * Constructor
  */
  public function __construct(&$bo)
  {
    $this->_b = $bo;
    $this->bugid = $bo->id;

    $this->_table = "bugids_fulltext";
    $this->_nfotable = "";
    $this->_my = array(
                        "bugid" => SQL_INDEX,
                        "raw" => SQL_PROPE,
                        "keywords" => SQL_PROPE,
                        "responsible_engineer" => SQL_PROPE,
                        "synopsis" => SQL_PROPE,
                        "workaround" => SQL_PROPE,
                        "comments" => SQL_PROPE,
                        "description" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "bugid" => "bugid",
                        "synopsis" => "synopsis",
                        "raw" => "raw",
                        "keywords" => "keywords",
                        "responsible_engineer" => "responsible_engineer",
                        "workaround" => "workaround",
                        "description" => "description",
                        "comments" => "comments",
                 );
  }

}
?>
