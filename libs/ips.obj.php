<?php
/**
 * IPS object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */

@require_once($config['rootpath']."/libs/functions.lib.php");

class IPS extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $root = "";
  public $name = "";
  public $publisher = "";
  public $desc = "";
  public $added = 0;
  public $updated = 0;

  public $a_pkgs = array();

  public $f_nofiles = false;

  public function __toString() {
    return $this->name;
  }

  /* pkg list */
  function fetchPkgs($all=1) {

    $this->a_pkgs = array();
    $table = "`jt_pkg_ips`";
    $index = "`id_pkg`";
    $where = "WHERE `id_ips`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Pkg($t['id_pkg']);
        if ($all) $k->fetchFromId();
        array_push($this->a_pkgs, $k);
      }
    }
    return 0;
  }

  public function link() {
    return '<a href="/ips/id/'.$this->id.'">'.$this->name.'</a>';
  }

  function addPkg($k) {

    $table = "`jt_pkg_ips`";
    $names = "`id_pkg`, `id_ips`";
    $values = "'$k->id', '".$this->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_pkgs, $k);
    return 0;
  }

  function delPkg($k) {

    $table = "`jt_pkg_ips`";
    $where = " WHERE `id_pkg`='".$k->id."' AND `id_ips`='".$this->id."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_pkgs as $ak => $v) {
      if ($k->id == $v->id) {
        unset($this->a_pkgs[$ak]);
      }
    }
    return 0;
  }

  function isPkg($k) {
    foreach($this->a_pkgs as $ko)
      if ($ko->id == $k->id)
        return TRUE;
    return FALSE;
  }



  public function md5Sum($file) {

    $fp = $this->root.'/file/'.substr($file->sha1, 0, 2).'/'.$file->sha1;

    if (!file_exists($fp)) {
      return -1;
    }
    $cmd = "/bin/gzip -dc $fp | /usr/bin/sha1sum - | /usr/bin/cut -f 1 -d' '";
    $ret = `$cmd`;
    return trim($ret);
  }

  public function listAll() {
    global $config;
    
    if (!is_dir($this->root)) {
      echo " > ips root path not found\n";
      return -1;  
    }

    $d_pkg = $this->root.'/pkg';

    /* Browse packages and update db info on them */
    if (!is_dir($d_pkg)) {
      echo " > pkg/ subdir not found inside IPS repo\n";
      return -1;
    }
    $filter = "";
    if (!empty($pkg)) {
      $filter = $d_pkg.'/*'.$pkg.'*';
    } else {
      $filter = $d_pkg.'/*';
    }

    foreach (glob($filter, GLOB_ONLYDIR) as $pkg) {
      foreach(glob($pkg.'/*') as $pstamp) {
        $pkgname = explode('/', $pkg);
	$pkgname = $pkgname[count($pkgname)-1];
	$pkgfmri = explode('/', $pstamp);
	$pkgfmri = $pkgfmri[count($pkgfmri)-1];
        $pkgname = preg_replace('/%2F/', '/', $pkgname);
        $pkgname = preg_replace('/%2B/', '+', $pkgname);
        $pkgname = preg_replace('/%2C/', ',', $pkgname);
        $pkgname = preg_replace('/%3A/', ':', $pkgname);
        $pkgfmri = preg_replace('/%2F/', '/', $pkgfmri);
        $pkgfmri = preg_replace('/%2C/', ',', $pkgfmri);
        $pkgfmri = preg_replace('/%3A/', ':', $pkgfmri);
	$po = new Pkg();
	$po->fromString($pkgname.'@'.$pkgfmri);
        echo "[-] Found package $po\n";
      }
    }

    return;
  }

  public function findNew($pkg = "", $adv = true) {
    global $config;
    
    if (!is_dir($this->root)) {
      echo " > ips root path not found\n";
      return -1;  
    }

    $d_pkg = $this->root.'/pkg';

    /* Browse packages and update db info on them */
    if (!is_dir($d_pkg)) {
      echo " > pkg/ subdir not found inside IPS repo\n";
      return -1;
    }
    $filter = "";
    if (!empty($pkg)) {
      $filter = $d_pkg.'/*'.$pkg.'*';
    } else {
      $filter = $d_pkg.'/*';
    }

    foreach (glob($filter, GLOB_ONLYDIR) as $pkg) {
      foreach(glob($pkg.'/*') as $pstamp) {
        $pkgname = explode('/', $pkg);
	$pkgname = $pkgname[count($pkgname)-1];
	$pkgfmri = explode('/', $pstamp);
	$pkgfmri = $pkgfmri[count($pkgfmri)-1];
        $pkgname = preg_replace('/%2F/', '/', $pkgname);
        $pkgname = preg_replace('/%2B/', '+', $pkgname);
        $pkgname = preg_replace('/%2C/', ',', $pkgname);
        $pkgname = preg_replace('/%3A/', ':', $pkgname);
        $pkgfmri = preg_replace('/%2F/', '/', $pkgfmri);
        $pkgfmri = preg_replace('/%2C/', ',', $pkgfmri);
        $pkgfmri = preg_replace('/%3A/', ':', $pkgfmri);
	$po = new Pkg();
	$po->fromString($pkgname.'@'.$pkgfmri);
	if ($po->fetchFromFMRI()) {
          $content = file_get_contents($pstamp);
          $po->o_ips = $this;
	  if ($po->insert()) {
	    echo "[!] FAILED to insert $po\n";
	  }
  	  $po->parseIPS($content);
          echo "[-] Found NEW package $po\n";
          if ($adv) {
	    $po->f_irc = 0;
	    $po->f_twitter = 0;
          } else {
	    $po->f_irc = 1;
	    $po->f_twitter = 1;
          }
	  $po->update();
	} else {
	  echo "[-] OLD: $po\n";
	}
	if (!$this->isPkg($po)) {
          $this->addPkg($po);
          echo "[-] Linked to repository $this\n";
	}
      }
    }

  }

  public function refreshSRU() {
   global $config;

   $url = "https://support.oracle.com/CSP/main/article?cmd=show&type=NOT&doctype=REFERENCE&id=1372094.1";
   $out = $config['tmppath'].'/srulist.html';
   
   $cmd = "/usr/bin/wget -q --no-check-certificate -U \":-)\" ";
   $cmd .= " --load-cookies /srv/sunsolve/tmp/cookies.txt ";
   $cmd .= "--save-cookies /srv/sunsolve/tmp/cookies.txt --keep-session-cookies ";
   $cmd .= " -O \"".$out."\" \"".$url."\"";
   passthru($cmd);

   if (file_exists($out)) {
     $lines = file($out);
     foreach ($lines as $line) {
       if (!preg_match('/>Readme<\/a>/', $line))
	 continue;
       $f = explode('"', $line);
       $f = $f[5];
       $url = "https://support.oracle.com".$f;
     }
     unlink($out);
     $out = $config['tmppath'].'/lastsru.html';
     $cmd = "/usr/bin/wget -q --no-check-certificate -U \":-)\" ";
     $cmd .= " --load-cookies /srv/sunsolve/tmp/cookies.txt ";
     $cmd .= "--save-cookies /srv/sunsolve/tmp/cookies.txt --keep-session-cookies ";
     $cmd .= " -O \"".$out."\" \"".$url."\"";
     passthru($cmd);
 
     if (file_exists($out)) {
       $lines = file($out);
        foreach($lines as $line) {
	  if (!preg_match('/[0-9]{6,10}&nbsp;/', $line))
	    continue;
	  $line = preg_replace('/&nbsp;/', ' ', $line);
	  $line = preg_replace('/<br><br>/', '', $line);
	  $line = trim($line);
	  $p = strpos($line, ' ');
	  $id = substr($line, 0, $p);
	  $line = substr($line, $p+1);
	  $bo = new Bugid($id);
	  if ($bo->fetchFromId()) {
	    echo "[-] New bug id inserted: $bo\n";
	    $bo->insert();
	  }
	  if (empty($bo->synopsis) || strlen($bo->synopsis) < 10) {
	    if (strlen($line)) {
	      $bo->synopsis = $line;
	      $bo->update();
	      echo "[>] Updated bug synopsis $bo\n";
	    }
	  }
	}
     }
   }
   return -1;
  }

  public function browse($pkg = "") {
    global $config;

    if (!is_dir($this->root))
      return -1;

    $d_pkg = $this->root.'/pkg';

    /* Browse packages and update db info on them */
    if (!is_dir($d_pkg)) {
      echo " > pkg/ subdir not found inside IPS repo\n";
      return -1;
    }
    $filter = "";
    if (!empty($pkg)) {
      $filter = $d_pkg.'/*'.$pkg.'*';
    } else {
      $filter = $d_pkg.'/*';
    }

    foreach (glob($filter, GLOB_ONLYDIR) as $pkg) {
      foreach(glob($pkg.'/*') as $pstamp) {
        $content = file_get_contents($pstamp);
        $po = new Pkg();
        $po->o_ips = $this;
	$po->parseIPS($content);
        echo "[-] Found package $po\n";
      }
    }
  }

  public function update() {
    $this->updated = time();
    parent::update();
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
    $this->_table = "ips";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE,
                        "publisher" => SQL_PROPE,
                        "desc" => SQL_PROPE,
                        "added" => SQL_PROPE,
                        "updated" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "publisher" => "publisher",
                        "desc" => "desc",
                        "added" => "added",
                        "updated" => "updated"
                 );
  }

}
?>
