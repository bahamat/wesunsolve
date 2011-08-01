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

  public static function sendConfirm($l, $code) {
    global $config;
    $from = '"'.$config['mailName']."\" ".$config['mailFrom'];
    $msg = "Dear ".$l->fullname.",\n";
    $msg .= "\nYou have registered on WeSunSolve.net with the e-mail address ".$l->email.".\n\n";
    $msg .= "In order to confirm your account, please follow the link below:\n\n";
    $msg .= " http://wesunsolve.net/confirm/c/".$code."\n\n";
    $msg .= "Thanks in advance,\n";
    $msg .= "\n\nWe Sun Solve!\n";

    $headers .= "From: $from\n";
    $headers .= "Reply-to: info@wesunsolve.net";
    $headers .= "Content-Type: text/plain; charset=\"utf-8\""; 

    mail($l->email, "[SUNSOLVE] Confirm your account", $msg, $headers);
  }

  public static function sendAdmin($subject, $msg, $from="") {
    global $config;
    $from = '"'.$config['mailName'].'" '.$config['mailFrom'];
    mail($config['admin'], "[SUNSOLVE] $subject", "--\n".$msg."\n\n--\n", "From: $from");
  }
}

?>
