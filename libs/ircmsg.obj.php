<?php
/**
 * IrcMsg object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */

@require_once($config['rootpath']."/libs/functions.lib.php");

class IrcMsg extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $msg = "";
  public $added = -1;
  public $done = 0;

  public static function add($m) {
    /* First find any email address inside the login and scramble it if necessary */
    $m = mailScramble($m);
    $msg = new IrcMsg();
    $msg->msg = $m;
    $msg->done = 0;
    return $msg->insert();
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
    $this->_table = "irc_log";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "msg" => SQL_PROPE,
                        "added" => SQL_PROPE,
			"done" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "msg" => "msg",
                        "added" => "added",
                        "done" => "done"
                 );
  }

}
?>
