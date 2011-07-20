<?php
/**
 * Login object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Login extends mysqlObj
{
  public $id = -1;
  public $username = "";
  public $password = "";
  public $fullname = "";
  public $email = "";
  public $added = -1;
  public $last_seen = -1;
  public $is_admin = 0;
  public $is_dl = 0;
  public $_plist;

  public $a_servers = array();
  public $a_uclists = array();

  public function fetchUCLists() {
    $table = "`u_clist`";
    $index = "`id`";
    $where = "WHERE `id_login`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $g = new UCList($t['id']);
        $g->fetchFromId();
        array_push($this->a_uclists, $g);
      }
      return true;
    }
    return false;
  }

  public function fetchServers() {
    $table = "`u_servers`";
    $index = "`id`";
    $where = "WHERE `id_owner`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $g = new Server($t['id']);
        $g->fetchFromId();
        array_push($this->a_servers, $g);
      }
    }
 }
  
  public function auth($pwd) {

    $pwd_md5 = md5($pwd);
    if (!strcmp($pwd_md5, $this->password)) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function insert() {
    $this->added = time();
    parent::insert();
  }
  

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "login";
    $this->_nfotable = "nfo_login";
    $this->_plist = array("patchPerPage" => array("type" => "N",
						  "desc" => "Patch per page to be shown",
						  "max" => 100,
						  "min" => 1,
						 ),
			  "bugsPerPage" => array("type" => "N",
						  "desc" => "Bugs per page to be shown",
						 "max" => 200,
						 "min" => 1
						)
			);
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "username" => SQL_PROPE|SQL_EXIST,
                        "password" => SQL_PROPE,
                        "email" => SQL_PROPE,
                        "added" => SQL_PROPE,
                        "last_seen" => SQL_PROPE,
                        "is_admin" => SQL_PROPE,
                        "is_dl" => SQL_PROPE,
                        "fullname" => SQL_PROPE
                 );


    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "username" => "username",
                        "password" => "password",
                        "email" => "email",
                        "added" => "added",
                        "last_seen" => "last_seen",
                        "is_admin" => "is_admin",
                        "is_dl" => "is_dl",
                        "fullname" => "fullname"
                 );
  }

}
?>
