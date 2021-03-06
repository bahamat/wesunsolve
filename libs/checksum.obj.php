<?php
/**
 * Checksum object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Checksum extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $name = "";
  public $md5 = "";
  public $sysv = "";
  public $sum = "";
  public $added = -1;
  public $cs_async = 0;

 public static function downloadFile() {
    global $config;

    $out = $config['tmppath']."/CHECKSUMS";
    if (file_exists($out)) {
      unlink($out);
    }

    $cmd = "/usr/bin/wget -q -O \"$out\" --no-check-certificate ".$config['checksumurl'];
    $ret = `$cmd`;

    if (file_exists($out) && filesize($out)) {  
      IrcMsg::void();
      Announce::getInstance()->msg(3, "[BATCH] Updated CHECKSUMS (size: ".filesize($out).")", MSG_ADM);
      $fn = $config['ckpath']."/CHECKSUM-".date('dmY');
      if (file_exists($fn)) {
	unlink ($fn);
      }
      copy($out, $fn);
      return 0;
    } else {
      return -1;
    }
 }
  
 public static function updateFile() {
    global $config, $stats;

    $file = $config['tmppath']."/CHECKSUMS";
    if (!file_exists($file)) {
      return -1;
    }
    $lines = file($file);
    $nb=0;
    $mod=0;
    $trash = 0;
    $pp = false;
    $csum = null;
    $cnt = 0;
    $new = false;
    $p = null;
    foreach ($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
        if($pp) {
          $pp = false;
	  $csum = null;
          $new = false;
        }
	continue;
      }
      if ($line[0] == "#") {
        continue;
      }
      if (preg_match('/^========================================================$/', $line)) {
        $trash++;
      }
      if ($trash == 2) { /* post header */
        if (!$pp && !strpos($line, ":")) { /* this is the name of the file here... */
	  $pp = true;
          $csum = new Checksum();
	  $csum->name = $line;
          if ($csum->fetchFromField("name")) {
	    $csum->insert();
	    echo "  > Added checksum entry for $line\n";
	    $nb++;
            $new = true;
	  }
          if (preg_match("/^[0-9]{6}-[0-9]{2}/", $csum->name)) { // Patch checksum
            $prev = explode(".", $csum->name);
	    $prev = $prev[0];
	    $prev = explode("-", $prev);
	    $p = new Patch($prev[0], $prev[1]);
            if ($p->fetchFromId()) {
 	      echo "  >>> New patch detected ".$p->name()."\n";
	      $p->insert();
	      // announce new patch
              $ip = new Ircnp();
              $ip->p = $p->patch;
              $ip->r = $p->revision;
              Announce::getInstance()->nPatch($ip);
	    } else {
  	      if ($new) {
		$p->removeCtrlfile();
	      }
	    }
	  } else {
	    $p = null;
	  }
	} else if ($pp && strpos($line, ":")) {
	  $f = explode(":", $line);
          if (isset($f[1])) {
	    $f[1] = trim($f[1]);
          }
	  switch($f[0]) {
	    case "MD5":
              if (strcmp($csum->md5, $f[1]) && !$csum->cs_async) {
	        $csum->md5 = $f[1];
		$csum->update();
 		if ($p) { $p->to_update = 1; $p->update(); }
	        echo "  > Updated MD5 checksum entry for ".$csum->name." to ".$f[1]."\n";
		$mod++;
	      }
	    break;
	    case "SysV Sum":
              if (strcmp($csum->sysv, $f[1]) && !$csum->cs_async) {
	        $csum->sysv = $f[1];
	        echo "  > Updated SysV checksum entry for ".$csum->name." to ".$f[1]."\n";
		$csum->update();
		$mod++;
	      }
	    break;
	    case "Sum":
              if (strcmp($csum->sum, $f[1]) && !$csum->cs_async) {
	        $csum->sum = $f[1];
 		if ($p) { $p->to_update = 1; $p->update(); }
	        echo "  > Updated SUM checksum entry for ".$csum->name." to ".$f[1]."\n";
		$csum->update();
		$mod++;
	      }
	    break;
	  }
	}
      }
    }
    echo "[-] Done parsing CHECKSUMS, $nb new checksums\n";
    IrcMsg::void();
    Announce::getInstance()->msg(3, "[BATCH] Parsed CHECKSUMS $nb new checksums, $mod updates", MSG_ADM);
    if (isset($stats) && isset($stats['new']) && isset($stats['mod'])) {
      $stats['new'] += $nb;
      $stats['mod'] += $mod;
    }
    return 0;
 }

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "checksums";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE,
                        "md5" => SQL_PROPE,
                        "sysv" => SQL_PROPE,
                        "sum" => SQL_PROPE,
                        "cs_async" => SQL_PROPE,
			"added" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "md5" => "md5",
                        "sum" => "sum",
                        "sysv" => "sysv",
                        "cs_async" => "cs_async",
                        "added" => "added",
                        "name" => "name"
                 );
  }

}
?>
