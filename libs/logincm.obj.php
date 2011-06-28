<?php
/**
 * Login Manager
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2007-2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class loginCM
{
  /**
   * Singleton variable
   */
  private static $_instance;
  public $username = "";
  public $isLogged = 0;
  public $o_login = NULL;

  public function startSession() {
    session_start();
    $this->checkLogin();
  }

  public function login($username, $password) {
    $l = new Login();
    $l->username = $username;
    if ($l->fetchFromField("username")) {
      return -1;
    }
    if ($l->auth($password) == FALSE) {
      return -1;
    }
    $this->isLogged = 1;
    $this->o_login = $l;
    $l->last_seen = time();
    $l->update();
    $this->username = $l->username;
    $_SESSION['username'] = $l->username;
    return 0;
  }

  public function logout() {
    if ($this->isLogged) {
      $this->isLogged = 0;
      unset($_SESSION['username']);
      $this->o_login = NULL;
      $this->username = ""; 
    }
  }

  public function checkLogin() {
    global $_SESSION;
    if (isset($_SESSION['username'])) {
      $this->username = $_SESSION['username'];
      $l = new Login();
      $l->username = $_SESSION['username'];
      if ($l->fetchFromField("username")) {
        $this->isLogged = 0;
	$this->username = "";
	$_SESSION['username'] = "";
	$this->o_login = NULL;
      } else {
        $this->o_login = $l;
        $this->isLogged = 1;
      }
    }
  }

  /**
   * Returns the singleton instance
   */
  public static function getInstance()
  {
    if (!isset(self::$_instance)) {
     $c = __CLASS__;
     self::$_instance = new $c;
    }
    return self::$_instance;
  }

  /**
   * Avoid the call of __clone()
   */
  public function __clone()
  {
    trigger_error("Cannot clone a singlton object, use ::instance()", E_USER_ERROR);
  }
}

?>
