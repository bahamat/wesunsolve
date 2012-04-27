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
  public $is_api = 0;
  public $is_dl = 0;
  public $is_log = 1;
  public $is_enabled = 1;
  public $_plist;

  public $o_code = null;

  public $a_servers = array();
  public $a_uclists = array();
  public $a_mlists = array();
  public $a_reports = array();


  public static function fetchLast($nb = 20) {
    $rc  = array();
    $table = "`login`";
    $index = "`id`";
    $where = " ORDER BY `last_seen` DESC LIMIT 0,$nb";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Login($t['id']);
        $k->fetchFromId();
        array_push($rc, $k);
      }
    }
    return $rc;
  }


  public function __toString() {
    return $this->username;
  }

  function fetchMList() {
    $this->a_mlists = array();
    $table = "`jt_login_mlist`";
    $index = "`id_mlist`";
    $where = "WHERE `id_login`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    { 
      foreach($idx as $t) {
        $k = new MList($t['id_mlist']);
        $k->fetchFromId();
        array_push($this->a_mlists, $k);
      }
    }
    return 0;
  }

  function addMList($k) {

    $table = "`jt_login_mlist`";
    $names = "`id_login`, `id_mlist`";
    $values = "'$this->id', '".$k->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_mlists, $k);
    return 0;
  }

  function delMList($k) {

    $table = "`jt_login_mlist`";
    $where = " WHERE `id_login`='".$this->id."' AND `id_mlist`='".$k->id."'";

    if (mysqlCM::getInstance()->delete($table, $where) == -1) {
      return -1;
    }
    foreach ($this->a_mlists as $ak => $v) {
      if ($k->id == $v->id) {
        unset($this->a_mlists[$ak]);
	break;
      }
    }
    return 0;
  }

  function isMList($p) {
    foreach($this->a_mlists as $po)
      if ($p->id == $po->id)
        return TRUE;
    return FALSE;
  }

  public function fetchUReports() {

    $this->a_ureports = array();
    $table = "`u_report`";
    $index = "`id`";
    $where = "WHERE `id_owner`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $g = new UReport($t['id']);
        $g->fetchFromId();
        $g->fetchServer();
        array_push($this->a_ureports, $g);
      }
      return true;
    }
    return false;
  }


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

  public function logAction($type, $id) {
    $ul = new ULog();
    $ul->id_login = $this->id;
    $ul->what = $type;
    $ul->id_link = $id;
    if ($ul->fetchFromId()) {
      $ul->insert();
    } else {
      $ul->when = time();
      $ul->update();
    }
    return false;
  }

  public function alreadyReset() {
    $rc = new UForgetp();
    $rc->id_login = $this->id;
    if ($rc->fetchFromId()) {
      return false;
    } else {
      return true;
    }
  }

  public function sendResetcode() {
    global $config;

    $str = $this->username.$this->email.$config['sitename'].time();
    $c = md5($str);

    $co = new UForgetp();
    $co->id_login = $this->id;
    $co->code = $c;
    $co->insert();

    Mail::getInstance()->sendResetcode($this, $c);

    return true;
  }

  public function sendConfirm($a=0) {
    global $config;

    $str = $this->username.$this->email.$config['sitename'].time();
    $c = md5($str);

    $co = new UConfirm();
    $co->id_login = $this->id;
    $co->code = $c;
    $co->insert();
    $this->o_code = $co;

    if (!$a) {
      Mail::getInstance()->sendConfirm($this, $c);
    } else {
      Mail::getInstance()->sendConfirm2($this, $c);
    }

    return true;
  }

  public function checkConfirm() {
    global $config;

    if (!$this->o_code) return false;

    // enable the account
    $this->is_enabled = 1;
    $this->update();
    // self delete this code
    $this->o_code->delete();
    return true;
  }

  public function resetPasswordcode($c) {
    return $c->delete();
  }
  

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "login";
    $this->_nfotable = "nfo_login";
    $this->_plist = array(
			  "cvePerPage" => array("type" => "N",
						  "desc" => "CVE per page to be shown",
						  "max" => 100,
						  "min" => 1,
						 ),
			  "patchPerPage" => array("type" => "N",
						  "desc" => "Patch per page to be shown",
						  "max" => 100,
						  "min" => 1,
						 ),
			  "bugsPerPage" => array("type" => "N",
						  "desc" => "Bugs per page to be shown",
						 "max" => 200,
						 "min" => 1
						),
		  	  "is_log" => array("type" => "B",
                                                  "desc" => "Log your action for history purpose",
                                                 "objvar" => 1
                                                ),
                          "apiAccess" => array("type" => "B",
                                                  "desc" => "Access to API enabled",
                                                ),
			  "resolution" => array("type" => "E", // enum
						"desc" => "Width of the page",
						"values" => array("960" => 3,
								  "1200" => 1,
								  "1600" => 2)
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
                        "is_api" => SQL_PROPE,
                        "is_log" => SQL_PROPE,
                        "is_enabled" => SQL_PROPE,
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
                        "is_api" => "is_api",
                        "is_log" => "is_log",
                        "is_enabled" => "is_enabled",
                        "fullname" => "fullname"
                 );
  }

}
?>
