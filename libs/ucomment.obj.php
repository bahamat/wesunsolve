<?php
/**
 * UComment object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
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


  public function show() {
    $ret = $this->comment;
    $ret = str_replace('\r\n', '<br/>', $ret);
    return $ret;
  }

  public static function getLastComments() {
    $comments = array();
    $table = "`u_comments`";
    $index = "`id`";
    $where = "WHERE `is_private`=0 ORDER BY `added` DESC LIMIT 0,10";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new UComment($t['id']);
        $k->fetchFromId();
        array_push($comments, $k);
      }
    }
    return $comments;
  }

  public function link() {
    switch($this->type) {
      case "bug":
        return '<a href="/bugid/id/'.$this->id_on.'">'.$this->id_on.'</a>';
      break;
      case "patch":
        return '<a href="/patch/id/'.$this->id_on.'">'.$this->id_on.'</a>';
      break;
      case "list":
      break;
      case "bundle":
        return '<a href="/bundle/id/'.$this->id_on.'">'.$this->id_on.'</a>';
      break;
      case "news":
      break;
      default:
	return false;
      break;
    }
  }

  public function since() {
    $now = time();
    $dt = $now - $this->added;
    $min = 0;
    $hou = 0;
    $day = 0;
    $rest = $dt;
    if ($rest >= 60) {
      $min = floor($rest / 60);
      $rest = $rest - ($min * 60);
    }
    if ($min >= 60) {
      $hou = floor($min / 60);
      $min = $min - ($hou * 60);
    }
    if ($hou >= 24) {
      $day = floor($hou / 24);
      $hou = $hou - ($day * 24);
    }
    $msg = "";
    if ($day) $msg .= $day.'d, ';
    if ($hou) $msg .= $hou.'h, ';
    if ($min) $msg .= $min.'m, ';
    if ($rest) $msg .= $rest.'s, ';
  
    return $msg;
  }

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
