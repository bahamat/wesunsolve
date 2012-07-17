<?php
/**
 * GPG class
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2008, Gouverneur Thomas
 * @version 1.0
 * @package libs
 * @subpackage various
 * @category libs
 * @filesource
 */


class GPG
{

  public static function refreshKey($pub) {
    global $config;
    
    $cmd = $config['gpgbin'].' '.$config['gpgopt'].' --refresh-keys "'.$pub.'" 2>&1';
    $rc = 0;
    $output = array();
    @exec($cmd, $output, $rc);
    if (!$rc) {
      return true;
    }
    return false;
  }

  public static function isKey($pub) {
    global $config;
    
    $cmd = $config['gpgbin'].' '.$config['gpgopt'].' --list-keys "'.$pub.'" 2>&1';
    $rc = 0;
    $output = array();
    @exec($cmd, $output, $rc);
    if (!$rc) {
      return true;
    }
    return false;
  }

  public static function checkKey($pub, $mail) {
     global $config;
    
    $cmd = $config['gpgbin'].' '.$config['gpgopt'].' --list-keys "'.$pub.'" 2>&1';
    $rc = 0;
    $output = array();
    @exec($cmd, $output, $rc);
    if (!$rc) {
      foreach($output as $line) {
        $line = trim($line);
        if (!preg_match('/^uid.*<(.*@.*)>$/', $line, $matches)) {
          continue;
        }
        $e = $matches[1];
        if (!strcmp($e, $mail)) {
          return true;
	}
      }
    }
    return false;
  }

  public static function delKey($pub) {
    global $config;

    $cmd = $config['gpgbin'].' '.$config['gpgopt'].' --yes --delete-keys "'.$pub.'" 2>&1';
    $rc = 0;
    $output = array();
    @exec($cmd, $output, $rc);
    if (!$rc) {
      return true;
    } 
    return false;
  }


  public static function addKey($pub) {
    global $config;

    $cmd = $config['gpgbin'].' '.$config['gpgopt'].' --keyserver hkp://subkeys.pgp.net --recv-key "'.$pub.'" 2>&1';
    $rc = 0;
    $output = array();
    @exec($cmd, $output, $rc);
    if (!$rc) {
      return true;
    } 
    return false;
  }

  public static function getFingerprint($pub) {
    global $config;

    $cmd = $config['gpgbin'].' '.$config['gpgopt'].'  --fingerprint "'.$pub.'" 2>&1';
    $rc = 0;
    $output = array();
    @exec($cmd, $output, $rc);
    if (!$rc) {
      return $output;
    }
    return false;
  }

  public static function signTxt($pubkey, $body) {
    global $config;

    $of = tempnam($config['tmppath'], 'out-');
    $if = tempnam($config['tmppath'], 'in-');
    @unlink($of);
    @file_put_contents($if, $body);

    $cmd = $config['gpgbin'].' '.$config['gpgopt'].' -a --recipient "'.$pubkey.'" --sign --digest-algo sha1 -t -o "'.$of.'" "'.$if.'" 2>&1';
    $rc = 0;
    $output = array();
    @exec($cmd, $output, $rc);
    if (!$rc) {
      $crypted = @file_get_contents($of);
      @unlink($if);
      @unlink($of);
      return $crypted;
    }
    return false;

  }

  public static function cryptTxt($pubkey, $body) {
    global $config;

    $of = tempnam($config['tmppath'], 'out-');
    $if = tempnam($config['tmppath'], 'in-');
    @unlink($of);
    @file_put_contents($if, $body);

    $cmd = $config['gpgbin'].' '.$config['gpgopt'].' -a --recipient "'.$pubkey.'" --encrypt -o "'.$of.'" "'.$if.'" 2>&1';
    $rc = 0;
    $output = array();
    @exec($cmd, $output, $rc);
    if (!$rc) {
      $crypted = @file_get_contents($of);
      @unlink($if);
      @unlink($of);
      return $crypted;
    }
    return false;

  }
}

?>
