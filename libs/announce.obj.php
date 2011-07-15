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

  public function getShortUrl($url) {
    global $config;

    $ch = curl_init(sprintf($config['ws2ShortUrl'], $url));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
 
    if (!empty($result) && preg_match("@^http://@i", $result)) {
      return $result;
    }
    return FALSE;
  }

/**
 * Google tiny url service
 */
  public function getGoogleShortUrl($url) {
    global $config;
    
    $ch = curl_init(sprintf('%s/url?key=%s', $config['googleShortUrl'], $config['googleApiKey']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $requestData = array(
       'longUrl' => $url
    );
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
 
    $result = curl_exec($ch);
    curl_close($ch);
 
    $ret = json_decode($result, true);
    return $ret['id'];
  }

  public function format($p) {
    $msg = '['.$p->name().']';

    if ($p->pca_sec) {
      $msg .= '[S]';
    }
    if ($p->pca_rec) {
      $msg .= '[R]';
    }
    if ($p->pca_bad) {
      $msg .= '[W]';
    }

    $url = 'http://wesunsolve.net/patch/id/'.$p->name();
    $surl = $this->getShortUrl($url);
    if ($surl === FALSE) {
      $surl = $this->getGoogleShortUrl($url);
    }
    if ($surl === FALSE) {
      $surl = $url;
    }
    
    $tags = "#solaris #oracle";

    $len = 140;
    $len -= strlen($msg);
    $len -= strlen($surl);
    $len -= strlen($tags);
    $len -= 2;
    
    $synopsis = $p->synopsis;
    if (!empty($synopsis)) {
      if (strlen($synopsis) > $len) {
         // Strip synopsis
         $synopsis = substr($synopsis, 0, $len);
      }
    } else return false;

    $msg = "$msg $synopsis $surl $tags";

    return $msg;
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
