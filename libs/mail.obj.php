<?php
/**
 * Mail class
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2007-2008, Gouverneur Thomas
 * @version 1.0
 * @package libs
 * @subpackage various
 * @category libs
 * @filesource
 */


class Mail
{
  private static $_instance;    /* instance of the class */

 /**
  * return the instance of Mail object
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
  * Avoid the __clone method to be called
  */
  public function __clone()
  { 
    trigger_error("Cannot clone a singlton object, use ::instance()", E_USER_ERROR);
  }

  public static function sendAdmin($subject, $msg, $from="") {
    global $config;
    if (empty($from)) {
      $from = $config['mailFrom'];
    }
    mail($config['admin'], "[SUNSOLVE] $subject", "--\n".$msg."\n\n--\n", "From: $from");
  }
}

?>
