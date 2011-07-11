<?php
/**
 * Announcement class
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2007-2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Announce
{
  /**
   * Singleton variable
   */
  private static $_instance;

  public function tweet($m) {
    global $config;
    @require_once($config['rootpath'].'/libs/tmhOAuth.php');
    @require_once($config['rootpath'].'/libs/tmhUtilities.php');
    
    $connection = new tmhOAuth(array(
      'consumer_key' => $config['twConsKey'],
      'consumer_secret' => $config['twConsSec'],
      'user_token' => $config['twUserTok'],
      'user_secret' => $config['twUserTokPriv'],
    )); 
 
    $connection->request('POST', 
                         $connection->url('1/statuses/update'),
                         array('status' => $m));

    return $connection->response['code'];
  }

  public function msg($p, $m) {
    switch($p) {
      case 1:
        IrcMsg::add($m);
        return $this->tweet($m);
      break;
      case 10:
        return $this->tweet($m);
      break;
      default:
        IrcMsg::add($m);
      break;
    }
    return 0;
  }

  public function nPatch($p) {
    // Add patch to IRC queue...
    $p->f_irc = 0;
    $p->f_twitter = 0;
    $p->insert();
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
