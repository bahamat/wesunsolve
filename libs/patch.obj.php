<?php
/**
 * Patch object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */

@require_once($config['rootpath']."/libs/functions.lib.php");

class Patch extends mysqlObj implements JSONizable
{
  /* Data Var */
  public $patch = -1;
  public $revision = -1;
  public $status = "";
  public $releasedate = "";
  public $synopsis = "";
  public $requirements = "";
  public $filesize = 0;
  public $sha512sum = 0;
  public $md5sum = 0;
  public $pca_rec = 0;
  public $pca_sec = 0;
  public $pca_bad = 0;
  public $pca_obs = 0;
  public $pca_y2k = 0;
  public $dia_version = "";
  public $dia_arch = "";
  public $dia_pkgs = "";
  public $to_update = 0;
  public $views = 0;
  public $updated = 0;
  public $added = 0;

  /* Fulltext search */
  public $score = 0;
  public $lmod = 0;

  /* Others */
  public $o_obsby = null; /* Obsoleted by .. */
  public $o_latest = null;
  public $o_current = null;
  public $o_csum = null;
  public $o_mfile = null;
  public $o_tl_start = null;
  public $o_tl_stop = null;
  
  /* Lists */
  public $a_files = array();
  public $a_keywords = array();
  public $a_bugids = array();
  public $a_releases = array();
  public $a_depend = array();
  public $a_obso = array();
  public $a_conflicts = array();
  public $a_comments = array();

  public $a_previous = array();
  public $a_bundles = array();
  public $a_freadmes = array();
  public $a_readmes = array();
  public $a_tline = array();

  public function toJSONArray() {
    return array('name' => $this->name(),
                             'synopsis' => $this->synopsis,
                             'md5sum' => $this->md5sum,
                             'sha512sum' => $this->sha512sum,
                             'recommended' => $this->pca_rec,
                             'security' => $this->pca_sec,
                             'bad' => $this->pca_bad,
                             'filesize' => $this->filesize);
  }

  public function fetchTimeline() {

    $this->a_tline = array();
    $table = "`p_timeline`";
    $index = "`id`";
    $cindex = "COUNT(`id`)";
    $where = "";
    $where .= " WHERE `id_patch`='".$this->patch."' AND `id_revision`='".$this->revision."'";
    $where .= " ORDER BY `when` ASC, `id_patch` ASC, `id_revision` ASC";
    $it = new mIterator("pTimeline", $index, $table, $where, $cindex);
    while(($e = $it->next())) {
      $e->fetchFromId();
      $this->a_tline[] = $e;
      if (!$this->o_tl_stop) {
        $this->o_tl_stop = $e;
      } else {
        if ($this->o_tl_stop->when < $e->when)
          $this->o_tl_stop = $e;
      }
      if (!$this->o_tl_start) {
        $this->o_tl_start = $e;
      } else {
        if ($this->o_tl_start->when > $e->when)
          $this->o_tl_start = $e;
      }
    }
    return 0;
  }

  public function toJSON() {
    return json_encode($this->toJSONArray());
  }

  public static function fromString($str) {
    $f = explode('-', $str);
    return new Patch($f[0], $f[1]);
  }

  public function shortLink($full=0, $color=false) {

    if ($this->added <= 0) 
      return '-'.sprintf("%02d", $this->revision);

    $link = "";
    $cl = "";
    if ($color) $cl = $this->color();
    if ($full) {
      $link = '<a '.$cl.' href="http://wesunsolve.net/patch/id/'.$this->name().'">-'.sprintf("%02d", $this->revision).'</a>';
    } else {
      $link = '<a '.$cl.' href="/patch/id/'.$this->name().'">-'.sprintf("%02d", $this->revision).'</a>';
    }
    return $link;
  }

  public function link($full=0, $color=false) {

    if ($this->added <= 0)
      return $this->name();

    $link = "";
    $cl = "";
    if ($color) $cl = $this->color();
    if ($full) {
      $link = '<a '.$cl.' href="http://wesunsolve.net/patch/id/'.$this->name().'">'.$this->name().'</a>';
    } else {
      $link = '<a '.$cl.' href="/patch/id/'.$this->name().'">'.$this->name().'</a>';
    }
    return $link;
  }

  public static function linkize($str) {
    $ret = $str;
    // match 0000000 as bugs
    $ret = preg_replace('/(^|\s| )([0-9]{7})/', '${1}<a href="/bugid/id/${2}">${2}</a>', $ret);
    // match 000000-00 as patches
    $ret = preg_replace('/(^|\s| )([0-9]{6}-[0-9]{2})/', '${1}<a href="/patch/id/${2}">${2}</a>', $ret);
    // match 6 digit as patchids
    $ret = preg_replace('/(^|\s| )([0-9]{6})/', '${1}<a href="/psearch/pid/${2}">${2}</a>', $ret);
    return $ret;
  }

  public function getAllReadme() {
    $this->a_freadmes = array();
    foreach(glob($this->readmePath()."-*") as $r) {
      $this->a_freadmes[] = $r;
    }
  }
 
  public function fetchCSum() {
    $this->o_csum = null;
    $table = "`checksums`";
    $index = "`id`";
    $where = "WHERE `name` LIKE '".$this->name()."%'";
    $where .= " LIMIT 0,1";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (isset($idx[0]) && isset ($idx[0]['id'])) {
	$this->o_csum = new Checksum($idx[0]['id']);
	$this->o_csum->fetchFromId();
	return true;
      }
    }
    return false;
  }

  public function fetchPrevious($all=2) {

    $this->fetchObsolated();

    /* First fetch previous revision of this patch */
    $this->a_previous = array();
    $table = "`patches`";
    $index = "`patch`, `revision`";
    $where = "WHERE `patch`='".$this->patch."' AND `revision`<".$this->revision;
    $where .= " ORDER BY `patches`.`revision` DESC";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['patch'], $t['revision']);
        if ($all==2) $k->fetchBugids();
        array_push($this->a_previous, $k);
      }
    }

    /* Then, for each of obsolated patch, fetch patch and previous releases */
    foreach ($this->a_obso as $op) {
      array_push($this->a_previous, $op);
      /* First fetch previous revision of this patch */
      $table = "`patches`";
      $index = "`patch`, `revision`";
      $where = "WHERE `patch`='".$op->patch."' AND `revision`<".$op->revision;
      $where .= " ORDER BY `patches`.`releasedate` DESC, `patches`.`revision` DESC";

      if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
      {
        foreach($idx as $t) {
          $k = new Patch($t['patch'], $t['revision']);
          if ($all) $k->fetchFromId();
          if ($all==2) $k->fetchBugids();
          array_push($this->a_previous, $k);
        }
      }
    }

    /* we should have everything we need ! */
    return 0;
  }

  public function isNew() {
    $now = time();
    if (($now - $this->releasedate) < 3600*24*5)
      return true;
    return false;
  }

  public static function browseDir($dir) {
    if (is_dir($dir)) {
      // get subdirs
      $sdirs = glob($dir."/*", GLOB_ONLYDIR);
      foreach($sdirs as $sd) {
        Patch::browseDir($sd);
      }
      if ($h = opendir($dir)) {
        while (false !== ($file = readdir($h))) {
	  $fp = $dir."/".$file;
	  if (!is_dir($fp) && preg_match("/^[0-9]{6}-[0-9]{2}/", $file)) {
	    echo "\t> $file detected\n";
	    $p = explode('.', $file);
	    $p = $p[0];
	    $p = explode('-', $p);
	    $patch = new Patch();
	    $patch->patch = $p[0];
	    $patch->revision = $p[1];
	    if ($patch->fetchFromId()) {
	      $patch->insert();
	      echo "[-] Added patch ".$patch->name()."\n";
	    }
	    if (!is_dir($patch->path())) {
	      mkdir($patch->path());
	    }
	    $a = $patch->findArchive();
	    if ($a) {
   	      echo "[-] Archive already there for ".$patch->name()."\n";
	      continue;
	    }
	    copy($fp, $patch->path()."/".$file);
	    $a = $patch->findArchive();
 	    if ($a) {
	      echo "[-] Successfully copied archive for patch ".$patch->name()."\n";
	    } else {
	      echo "[!] Unable to copy archive for patch ".$patch->name()."\n";
	      continue;
	    }
	    if ($patch->updateArchiveSize($a)) {
              echo "[-] Updated filesize: ".$patch->filesize."\n";
              $patch->update();
            }
            if (!file_exists($a.".md5sum")) {
              echo "[-] Generating MD5..";
              $patch->makeMD5($a, $a.".md5sum");
              echo "done\n";
            }
            if (!file_exists($a.".sha512sum")) {
              echo "[-] Generating SHA512..";
              $patch->makeSHA512($a, $a.".sha512sum");
              echo "done\n";
            }
            if (!file_exists($patch->readmePath())) {
              echo "[-] Trying to extract README for ".$patch->name()."..";
              $ret = $patch->extractReadme();
              if (!$ret) {
                echo "done\n";
              } else {
                echo "failed\n";
              }
            }
            if (file_exists($patch->readmePath())) {
	      echo "[-] Parsing readme file...\n";
              $fc = file_get_contents($patch->readmePath());
  	      $patch->fetchData();
              $ret = $patch->readme($fc);
              unset($fc);
              if ($ret > 0) {
                $patch->setData("readme_done", 1);
                $patch->update();
              }
	      echo "[-] Finished patch ".$patch->name()."\n";
	    }

	  }
	}
      }
    }
  }

  /* List mgmt */
  public function fetchAll($all=2) {

    if ($all) $this->o_latest = Patch::pLatest($this->patch);
    if ($this->o_latest && 
        intval($this->o_latest->patch) == intval($this->patch) &&
        intval($this->o_latest->revision) == intval($this->revision)) {
      $this->o_latest = false;
    }

    if ($all == 1) $this->fetchFiles($all);
    if ($all != 3) {
      $this->fetchKeywords($all);
      $this->fetchBugids($all);
      $this->fetchObsolated($all);
      $this->fetchRequired($all);
      $this->fetchConflicts($all);
      $this->fetchComments($all);
    }
    $this->fetchData();
  }

  public static function parseList($list, $format) {
    $plist = array();
    switch($format) {
      case "text":
        $patches = explode(PHP_EOL, $list);
        foreach($patches as $pid) {
          if (empty($pid) || !preg_match("/[0-9]{6}-[0-9]{2}/", $pid)) {
            continue;
          }
          $p = explode("-", $pid);
          if (count($p) != 2) continue;
          $pid = $p[0];
          $rev = $p[1];
          $plist[] = new Patch($pid, $rev);
        }
      break;
      case "pca":
        $lines = explode(PHP_EOL, $list);
        $curr = 1;
        foreach($lines as $line) {
          if(empty($line)) continue;
          if(!preg_match("/^[0-9]{6}/", $line)) continue;
          $f = preg_split("/[\s ]+/", $line);
          if (count($f) < 4) continue;
          $pid = $f[0];
          $rev = $f[3];
          $crev = $f[1];
          $patch = new Patch($pid, $rev);
          if (strcmp($crev, "--"))
            $patch->o_current = new Patch($pid, $crev);
          $plist[] = $patch;
 
        }
      break;
      case "showrev":
        $lines = explode(PHP_EOL, $list);
        foreach($lines as $line) {
          if (empty($line)) continue;
	  if(!preg_match("/^Patch: [0-9]{6}-[0-9]{2}/", $line)) continue;
	  $f = preg_split("/[\s ]+/", $line);
	  if (count($f) > 2) {
 	    $p = $f[1];
	    $p = explode("-", $p);
	    $patch = new Patch($p[0], $p[1]);
	    $plist[] = $patch;
	  }
        }
      break;
      default:
        return null;
      break;
    }
    return $plist;
  }

  public function updateArchiveSize($a) {
    global $config;

    $fsize = filesize($a);
    if ($this->filesize != $fsize) {
      $this->filesize = $fsize;
      return true;
    }
    return false;
  }

  public function parseDate($str) {
    $d = explode("/", $str);
    if (count($d) != 3) return 0;
    // Oct/13/2000
    if (preg_match("/[0-9]/", $d[0])) { // first is day
      $day = $d[0];
      $month = $d[1];
    } else {
      $day = $d[1];
      $month = $d[0];
    }
    $year = $d[2];
    $m = array(
      "Jan" => 1,
      "Feb" => 2,
      "Mar" => 3,
      "Apr" => 4,
      "May" => 5,
      "Jun" => 6,
      "Jul" => 7,
      "Aug" => 8,
      "Sep" => 9,
      "Oct" => 10,
      "Nov" => 11,
      "Dec" => 12
    );
    $month = $m[$month];
    return mktime(0,0,0,$month, $day, $year);
  }

  public static function pUpperThan($pid, $revmin=0) {
    $index = "`patch`, `revision`";
    $table = "`patches`";
    $where = "WHERE `patch`='".$pid."' and `revision`>$revmin AND releasedate!=0 ORDER BY `revision` ASC LIMIT 0,1";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (!count($idx)) {
        /* @TODO: If nothing found, find any superseeding other patch id if present... */
        return null;
      }
      return new Patch($idx[0]['patch'], $idx[0]['revision']);
    }
    return 0;

  }

  public static function pLatest($pid) {
    $index = "`patch`, `revision`";
    $table = "`patches`";
    $where = "WHERE `patch`='".$pid."' ORDER BY `revision` DESC LIMIT 0,1";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (!count($idx)) {
        return null;
      }
      return new Patch($idx[0]['patch'], $idx[0]['revision']);
    }
    return 0;

  }

  public static function addListFile($file) {
    global $config;
    if (!file_exists($file)) {
      return -1;
    }
    $lines = file($file);
    foreach ($lines as $line) {
      $np = false;
      if (empty($line)) {
        continue;
      }
      // we asssume that if the line is not empty, it's a correct patch number
      $p = explode("-", $line);
      if (count($p) != 2)
	continue;
      $po = new Patch($p[0], $p[1]);
      if ($po->fetchFromId()) {
	echo "[-] Adding ".$po->name()." as new patch.\n";
	$po->to_update = 1;
	$po->insert();
      } else {
        echo "[-] ".$po->name()." already there..\n";
      }
    }
  }

  public function makeSHA512($file, $outfile) {
    if (!file_exists($file))
      return -1;

    $cmd = "/usr/bin/sha512sum $file > $outfile";
    exec($cmd, $out, $ret);
    if ($ret) {
      unlink($outfile);
    }
    return $ret;
  }

  public function makeMD5($file, $outfile) {
    if (!file_exists($file))
      return -1;

    $cmd = "/usr/bin/md5sum $file > $outfile";
    exec($cmd, $out, $ret);
    if ($ret) {
      unlink($outfile);
    }
    return $ret;
  }

  public function checkPath() {
    $path = $this->path();
    if (!is_dir($path)) {
      mkdir($path, 0755, true);
    }
  }

  public function extPath() {

    global $config;
    $dir1 = substr($this->patch, 0, 2);
    $dir2 = substr($this->patch, 2, 2);
    return $config['extpath']."/$dir1/$dir2";
  }


  public function path() {

    global $config;
    $dir1 = substr($this->patch, 0, 2);
    $dir2 = substr($this->patch, 2, 2);
    return $config['ppath']."/$dir1/$dir2";
  }

  public function checkPresence() {

    $this->fetchCSum();
    if (!$this->o_csum)
      return false;

    $path = $this->path();
    $ctlfile = $path."/.".$this->name();
    if(file_exists($ctlfile)) {
      unlink($ctlfile);
      return true;
    }
    return false;
  }

  public function removeCtrlfile() {

    $path = $this->path();
    $ctlfile = $path."/.".$this->name();
    if (file_exists($ctlfile))
      return unlink($ctlfile);
    return true;
  }

  public function findExt() {
    global $config;

    $path = $this->path();
    $zipfile = $path."/".$this->name().".zip";
    $tarfile = $path."/".$this->name().".tar.Z";

    if (file_exists($zipfile)) {
      return "zip";
    } else if (file_exists($tarfile)) {
      return "tar.Z";
    } else {
      return "zip";
    }
  }

  public function findArchive() {
    global $config;
   
    $path = $this->path();
    $zipfile = $path."/".$this->name().".zip";
    $tarfile = $path."/".$this->name().".tar.Z";

    if (file_exists($zipfile)) {
      return $zipfile;
    } else if (file_exists($tarfile)) {
      return $tarfile;
    } else {
      return null;
    }
  }

  public function isAlreadyTried() {
    global $config;
    $path = $this->path();
    $ctlfile = $path."/.".$this->name();
    if (file_exists($ctlfile)) {
      return true;
    } else {
      return false;
    }
  }

  public function tryDownload() {
    global $config;

    $path = $this->path();
    $zipfile = $path."/".$this->name().".zip";
    $tarfile = $path."/".$this->name().".tar.Z";
    $ctlfile = $path."/.".$this->name();
    $zipurl = $config['patchurl']."/".$this->name().".zip";
    $tarurl = $config['patchurl']."/".$this->name().".tar.Z";

    $cmdzip = "/usr/bin/wget -q --no-check-certificate --user=\"".$config['MOSuser']."\" --password=\"".$config['MOSpass']."\" -O \"$zipfile\" \"$zipurl\"";
    $cmdtar = "/usr/bin/wget -q --no-check-certificate --user=\"".$config['MOSuser']."\" --password=\"".$config['MOSpass']."\" -O \"$tarfile\" \"$tarurl\"";

    echo "[-] Trying to download ZIP for ".$this->name()."..";
    $ret = `$cmdzip`;
    if (!file_exists($zipfile) || !filesize($zipfile)) {
      echo "failed\n";
      unlink($zipfile);
    } else {
      echo "success\n";
      return 0;
    }

    echo "[-] Trying to download tar.Z for ".$this->name()."..";
    $ret = `$cmdtar`;
    if (!file_exists($tarfile) || !filesize($tarfile)) {
      echo "failed\n";
      unlink($tarfile);
    } else {
      echo "success\n";
      return 0;
    }

    /* touch controlfile */
    touch($ctlfile);
    echo "[-] ".$this->name()." download failed, it will not be retried anymore...\n";
    
    return -1;
  }

  public function readmePath() {
    return $this->path()."/README.".$this->name();
  }

  public function downloadReadme() {
    global $config;
    $readmefile = $this->readmePath();
    $url = $config['readmeurl']."/README.".$this->name();
    $cmd = "/usr/bin/wget -q --no-check-certificate -U \":-)\" --user=\"".$config['MOSuser']."\" --password=\"".$config['MOSpass']."\" -O \"$readmefile\" \"$url\"";
    $ret = `$cmd`;
    if (file_exists($readmefile) && filesize($readmefile)) {
      return 0;
    } else {
      touch($this->path()."/.README.".$this->name());
      if (file_exists($readmefile)) {
        unlink($readmefile);
      }
      return -1; 
    }
  }

  public function extractReadme() {
    
    $path = $this->path();
    $zipfile = $path."/".$this->name().".zip";
    $tarfile = $path."/".$this->name().".tar.Z";
    if (file_exists($zipfile)) {
      $cmd = "/usr/bin/unzip -d $path/".$this->name()." -j $zipfile ".$this->name()."/README.".$this->name()." > /dev/null 2>&1";
      $cmd2 = "/usr/bin/unzip -d $path/".$this->name()." -j $zipfile ".$this->name()."/README > /dev/null 2>&1";
      $ret = `$cmd`;
      $ret = `$cmd2`;
    } else if (file_exists($tarfile)) {
      $cmd = "/bin/gzip -dc $tarfile | /bin/tar --no-recursion -C $path -xf - ".$this->name()."/README.".$this->name()." > /dev/null 2>&1";
      $cmd2 = "/bin/gzip -dc $tarfile | /bin/tar --no-recursion -C $path -xf - ".$this->name()."/README > /dev/null 2>&1";
      $ret = `$cmd`;
      $ret = `$cmd2`;

    } else {
      return -1;
    }

    if (file_exists($path."/".$this->name()."/README.".$this->name())) {
      rename($path."/".$this->name()."/README.".$this->name(), $path."/README.".$this->name());
    } else if (file_exists($path."/".$this->name()."/README")) {
      rename($path."/".$this->name()."/README", $path."/README.".$this->name());
    } else {
      if (file_exists($path."/".$this->name()."/README")) unlink($path."/".$this->name()."/README");
      if (file_exists($path."/".$this->name()."/README.".$this->name())) unlink($path."/".$this->name()."/README.".$this->name());
      if (is_dir($path."/".$this->name())) rmdir($path."/".$this->name());
      return -1;
    }

    // remove the directory
    if (file_exists($path."/".$this->name()."/README")) unlink($path."/".$this->name()."/README");
    if (file_exists($path."/".$this->name()."/README.".$this->name())) unlink($path."/".$this->name()."/README.".$this->name());
    if (is_dir($path."/".$this->name())) rmdir($path."/".$this->name());
    return 0;
  }

  public function __toString() {
    return $this->name();
  }

  public function name() {
    return sprintf("%d-%02d", $this->patch, $this->revision);
  }

  public function fetchObsby($minrev=null) {
    /* TODO: Fetch the obsoleted by patch */
    $this->o_obsby = null;
    $table = "`jt_patches_obsolated`";
    $index = "`patchid`, `revision`";
    if (!$minrev) {
      $where = "WHERE `obsoid`='".$this->patch."' AND `obsorev`='".$this->revision."'";
    } else {
      $where = "WHERE `obsoid`='".$this->patch."' AND `revision`>=$minrev";
    }

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (isset($idx[0])) {
        $t = $idx[0];
        $this->o_obsby = new Patch($t['patchid'], $t['revision']);
      }
    }
    return 0;
  }

  public function fetchBundles() {
    $this->a_bundles = array();

    $table = "`jt_bundles_patches`";
    $index = "`bid`";
    $where = "WHERE `pid`='".$this->patch."' AND `prev`='".$this->revision."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Bundle($t['bid']);
        $k->fetchFromId();
        array_push($this->a_bundles, $k);
      }
    }
    return 0;
  }

  /* Users comments */
  function fetchComments($all=1) {

    $lm = loginCM::getInstance();
    if (!isset($lm->o_login) || !$lm->o_login) {
      $id = -1;
    } else {
      $id = $lm->o_login->id;
    }
    

    $this->a_comments = array();
    $table = "`u_comments`";
    $index = "`id`";
    $where = "WHERE `type`='patch' AND `id_on`='".$this->name()."' AND (`is_private`=0 OR (`id_login`=$id AND `is_private`=1)) ORDER BY `added` ASC";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new UComment($t['id']);
        if ($all) $k->fetchFromId();
        array_push($this->a_comments, $k);
      }
    }
    return 0;
  }


  /* Conflicts patches with patch */
  function fetchConflicts($all=1) {
    $this->a_conflicts = array();
    $table = "`jt_patches_conflicts`";
    $index = "`confid`, `confrev`";
    $where = "WHERE `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['confid'], $t['confrev']);
        if ($all) $k->fetchFromId();
        array_push($this->a_conflicts, $k);
      }
    }
    return 0;
  }

  function addConflict($k) {

    $table = "`jt_patches_conflicts`";
    $names = "`confid`, `confrev`, `patchid`, `revision`";
    $values = "'$k->patch', '".$k->revision."', '".$this->patch."', '".$this->revision."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_conflicts, $k);
    return 0;
  }

  function delConflict($k) {

    $table = "`jt_patches_conflicts`";
    $where = " WHERE `confid`='".$k->patch."' AND `confrev`='".$k->revision."' AND `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_conflicts as $ak => $v) {
      if ($k->patch == $v->patch && $k->revision == $v->revision) {
        unset($this->a_conflicts[$ak]);
      }
    }
    return 0;
  }

  function isConflict($p) {
    foreach($this->a_conflicts as $po)
      if ($p->patch == $po->patch && $p->revision == $po->revision)
        return TRUE;
    return FALSE;
  }



  /* Obsolated patches by this patch */
  function fetchObsolated($all=1) {
    $this->a_obso = array();
    $table = "`jt_patches_obsolated`";
    $index = "`obsoid`, `obsorev`";
    $where = "WHERE `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['obsoid'], $t['obsorev']);
        if ($all) $k->fetchFromId();
        array_push($this->a_obso, $k);
      }
    }
    return 0;
  }

  function addObsolated($k) {

    $table = "`jt_patches_obsolated`";
    $names = "`obsoid`, `obsorev`, `patchid`, `revision`";
    $values = "'$k->patch', '".$k->revision."', '".$this->patch."', '".$this->revision."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_obso, $k);
    return 0;
  }

  function delObsolated($k) {

    $table = "`jt_patches_obsolated`";
    $where = " WHERE `obsoid`='".$k->patch."' AND `obsorev`='".$k->revision."' AND `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";
   
    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_obso as $ak => $v) {
      if ($k->patch == $v->patch && $k->revision == $v->revision) {
        unset($this->a_obso[$ak]);
      }
    }
    return 0;
  }

  function isObsolated($p) {
    foreach($this->a_obso as $po)
      if ($p->patch == $po->patch && $p->revision == $po->revision)
        return TRUE;
    return FALSE;
  }


  /* Required Patches */
  function fetchRequired($all=1) {
    $this->a_depend = array();
    $table = "`jt_patches_depend`";
    $index = "`depid`, `deprev`";
    $where = "WHERE `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['depid'], $t['deprev']);
        if ($all) $k->fetchFromId();
        array_push($this->a_depend, $k);
      }
    }
    return 0;
  }

  function addRequired($k) {

    $table = "`jt_patches_depend`";
    $names = "`depid`, `deprev`, `patchid`, `revision`";
    $values = "'$k->patch', '".$k->revision."', '".$this->patch."', '".$this->revision."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_depend, $k);
    return 0;
  }

  function delRequired($k) {

    $table = "`jt_patches_depend`";
    $where = " WHERE `depid`='".$k->patch."' AND `deprev`='".$k->revision."' AND `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_depend as $ak => $v) {
      if ($k->patch == $v->patch && $k->revision == $v->revision) {
        unset($this->a_depend[$ak]);
      }
    }
    return 0;
  }

  function isRequired($p) {
    foreach($this->a_depend as $po)
      if ($p->patch == $po->patch && $p->revision == $po->revision)
        return TRUE;
    return FALSE;
  }

  /* Files */
  function fetchFiles($all=1) {

    $this->a_files = array();
    $table = "`jt_patches_files` jt, `files` f";
    $index = "`name`, `fileid`, `size`, `pkg`, `md5`, `sha1`";
    $where = "WHERE `patchid`='".$this->patch."' AND `revision`='".$this->revision."' AND f.id=jt.fileid";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    { 
      foreach($idx as $t) {
        $k = new File($t['fileid']);
  	$k->name = $t['name'];
  	$k->size = $t['size'];
  	$k->md5 = $t['md5'];
  	$k->sha1 = $t['sha1'];
  	$k->pkg = $t['pkg'];
        array_push($this->a_files, $k);
      }
    }
    return 0;
  }

  function addFile($k) {

    $table = "`jt_patches_files`";
    $names = "`fileid`, `patchid`, `revision`";
    $values = "'$k->id', '".$this->patch."', '".$this->revision."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_files, $k);
    return 0;
  }

  function setFileAttr($k, $size = 0, $md5 = "", $sha1 = "", $pkg = "") {

    $file = null;
    foreach ($this->a_files as $ak => $v) {
      if (!strcmp($k, $v->name)) {
        $file = $v;
	break;
      }
    }
    if (!$file)
      return -1;
  
    $file->md5 = $md5;
    $file->sha1 = $sha1;
    $file->size = $size;
    $file->pkg = $pkg;

    $table = "jt_patches_files";
    $set = "`size`='".$file->size."', `md5`='".$file->md5."', `sha1`='".$file->sha1."', `pkg`='".$file->pkg."'";
    $where = " WHERE `fileid`='".$file->id."' AND `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (mysqlCM::getInstance()->update($table, $set, $where)) {
      return -1;
    }
    return 0;

  }

  function delFile($k) {

    $table = "`jt_patches_files`";
    $where = " WHERE `fileid`='".$k->id."' AND `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_files as $ak => $v) {
      if (!strcmp($k->name, $v->name)) {
	unset($this->a_files[$ak]);
      }
    }
    return 0;
  }

  function isFile($k) {
    foreach($this->a_files as $ko)
      if (!strcasecmp($ko->name, $k))
        return TRUE;
    return FALSE;
  }


  /* Readmes */
  function fetchReadmes($all=1) {

    $this->a_readmes = array();
    $table = "`p_readmes`";
    $index = "`when`";
    $where = "WHERE `patch`='".$this->patch."' AND `revision`='".$this->revision."' ORDER BY `when` ASC";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    { 
      foreach($idx as $t) {
        $k = new Readme();
        $k->patch = $this->patch;
        $k->revision = $this->revision;
        $k->when = $t['when'];
        if ($all) $k->fetchFromId();
        array_push($this->a_readmes, $k);
      }
    }
    if (count($this->a_readmes) > 1) {
      array_push($this->a_readmes, array_shift($this->a_readmes));
    }
    return 0;
  }


  /* Keywords */
  function fetchKeywords($all=1) {

    $this->a_keywords = array();
    $table = "`jt_patches_keywords`";
    $index = "`kid`";
    $where = "WHERE `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    { 
      foreach($idx as $t) {
        $k = new Keyword($t['kid']);
        $k->fetchFromId();
        array_push($this->a_keywords, $k);
      }
    }
    return 0;
  }

  function addKeyword($k) {

    $table = "`jt_patches_keywords`";
    $names = "`kid`, `patchid`, `revision`";
    $values = "'$k->id', '".$this->patch."', '".$this->revision."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_keywords, $k);
    return 0;
  }

  function delKeyword($k) {

    $table = "`jt_patches_keywords`";
    $where = " WHERE `kid`='".$k->id."' AND `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_keywords as $ak => $v) {
      if (!strcmp($k->keyword, $v->keyword)) {
	unset($this->a_keywords[$ak]);
      }
    }
    return 0;
  }

  function isKeyword($k) {
    foreach($this->a_keywords as $ko)
      if (!strcasecmp($ko->keyword, $k))
        return TRUE;
    return FALSE;
  }


  /* Bugids */
  function fetchBugids($all=1) {

    $this->a_bugids = array();
    $table = "`jt_patches_bugids`";
    $index = "`bugid`";
    $where = "WHERE `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";
   
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    { 
      foreach($idx as $t) {
        $k = new Bugid($t['bugid']);
        if ($all) $k->fetchFromId();
        array_push($this->a_bugids, $k);
      }
    }
    return 0;
  }

  function addBugid($k) {

    $table = "`jt_patches_bugids`";
    $names = "`bugid`, `patchid`, `revision`";
    $values = "'$k->id', '".$this->patch."', '".$this->revision."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_bugids, $k);
    return 0;
  } 
 
  function delBugid($k) {

    $table = "`jt_patches_bugids`";
    $where = " WHERE `bugid`='".$k->id."' AND `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_bugids as $ak => $v) {
      if ($k->id == $v->id) {
        unset($this->a_bugids[$ak]); 
      }
    }
    return 0;
  }

  function isBugid($k) {
    foreach($this->a_bugids as $ko)
      if ($ko->id == $k)
        return TRUE;
    return FALSE;
  }



  /* Overloads */
  public function delete() {

    parent::delete();
  }

  public function update() {
    $this->updated = time();
    parent::update();
  }

  public function insert() {
    $this->added = time();
    parent::insert();
  }

  /* parse readme contents */
  public function readme(&$c, $redo=false) {
    global $stats;

    $nb = 0;
    $mod = 0;

    $this->fetchAll(0);
    $this->fetchFiles();
    $this->fetchBugids();

    $u = 0;

    if (empty($this->status)) {
      $this->status = "RELEASED";
      $u++;
    }

    $stepFile = 0;
    $stepBugs = false;
    $cPatch = null;
    $c = explode(PHP_EOL, $c);
    foreach ($c as $line) {
      $line = trim($line);
      if (empty($line) && !$stepBugs && !$stepFile) {
        continue;
      } else if (empty($line) && $stepBugs == 2) {
	$stepBugs = false;
	unset($cPatch);
	$cPatch = null;
	continue;
      } else if (empty($line) && $stepBugs == 1) {
	$stepBugs++;
	continue;
      } else if (empty($line) && $stepFile == 2) {
        $stepFile = false;
        continue;
      } else if (empty($line) && $stepFile == 1) {
	$stepFile++;
	continue;
      }
      //if(($line[0] == "/"|| $line[0] == "<") && $stepFile) {
      if($stepFile) {
        $fn = explode(" ", $line);
	$deleted = 0;
	if (count($fn) > 1 && !strcmp($fn[1], "(deleted)")) { // but we do nothing with that shit !@#)(*
	  $deleted = 1;
	  continue;
        }
	$fn = $fn[0];
        if (!$this->isFile($fn)) {
          $file = new File();
	  $file->name = $fn;
	  if ($file->fetchFromField("name")) {
	    $file->insert();
	    echo "\t* ".$file->name." added to db\n";
	  }
	  $this->addFile($file);
	  echo "\t* ".$file->name." linked to patch\n";
	}
        continue;
      }
      if (preg_match('/^Problem Description:$/', $line)) { // link bugs below
        $stepBugs = 1;
        $cPatch = null;
	continue;
      }

      $f = explode(":", $line);
      if ($stepBugs && preg_match("/^[0-9]{6,8}[\s:]/", $line) && strlen($line) > 7) { // Bugid desc !
	$u++;
        $a = preg_split("/[:\s]/", $line, 2);
        $id = trim($a[0]);
        if (isset($a[1])) { 
          $synopsis = trim($a[1]);
        } else {
	  echo "[!] Wrong bugid line: $line\n";
          continue;
	}
        if (strlen($id) < 6 || !preg_match('/^[0-9]*$/', $id)) {
	  echo "[!] Wrong bugid line: $line\n";
          continue;
	}

	$bo = new Bugid($id);
        if ($bo->fetchFromId()) {
	  $bo->insert();
          $bo->flag_update();
	  echo "\t* New bugid $id\n";
        }
	if (strlen($bo->synopsis) < 15 && strcmp($bo->synopsis, $synopsis)) {
	  $bo->synopsis = $synopsis;
	  $bo->update();
          $bo->flag_update();
	  echo "\t* Bugid $id updated: $synopsis\n";
        }
	if ($stepBugs) {
          if ($cPatch) {
	    if (!$cPatch->isBugid($bo->id)) {
	      $cPatch->addBugid($bo);
	      echo "\t\t* bug $id linked to patch: ".$cPatch->name()."\n";
	      if(!$redo) {
	        $cPatch->to_update = 1;
 	        $cPatch->update();
	      }
            }
	  } else {
	    if (!$this->isBugid($bo->id)) {
	      $this->addBugid($bo);
              $bo->flag_update();
	      echo "\t\t* bugid linked to this patch: $id\n";
            }
	  }
	}
	unset($bo);

      } else if (preg_match("/^\(from [0-9]{6}-[0-9]{2}\)$/", $line)) {
	$u++;
        $p = substr($line, 6, 9);
        $p = explode("-", $p);
	$patch = new Patch($p[0], $p[1]);
        if ($patch->fetchFromId()) {
          echo "[-] Old revision of patch detected, inserting: ".$patch->name()."\n";
	  $patch->insert();
          $nb++;
	} else {
	      if(!$redo) {
	        $patch->to_update = 1;
 	        $patch->update();
	      }
	}
	$cPatch = $patch;
	$stepBugs = 1;
	$cPatch->fetchBugids();
	//unset($patch); // will be fetched next time
      } else if (preg_match("/^OBSOLETE Patch-ID# ".$this->name()."$/", $line)) {
        $this->status = "OBSOLETE";
      } else if (preg_match("/^WITHDRAWN Patch-ID# ".$this->name()."$/", $line)) {
        $this->status = "WITHDRAWN";
      } else if (count($f) > 1) {
	$u++;
        switch ($f[0]) {
          case "Relevant Architectures":
            $desc = substr($line, strpos($line, ":") + 2);
	    if (!empty($desc)) {
	      if (strcmp($this->data("arch"), $desc)) {
		$this->setData("arch", $desc);
	        echo "\t* Architecture: $desc\n";
	      }
            }
	  break;
          //case "Changes incorporated in this version":
	  //break;
          case "Unbundled Release":
            $desc = substr($line, strpos($line, ":") + 2);
	    if (!empty($desc)) {
	      if (strcmp($this->data("unbundled_release"), $desc)) {
		$this->setData("unbundled_release", $desc);
	        echo "\t* Unbundled release: $desc\n";
	      }
	    }
	  break;
          case "Xref":
            $desc = substr($line, strpos($line, ":") + 2);
	    if (!empty($desc)) {
	      if (strcmp($this->data("xref"), $desc)) {
		$this->setData("xref", $desc);
	        echo "\t* XRef: $desc\n";
	      }
            }
	  break;
          case "Solaris Release":
            $desc = substr($line, strpos($line, ":") + 2);
	    if (!empty($desc)) {
	      if (strcmp($this->data("solaris_release"), $desc)) {
		$this->setData("solaris_release", $desc);
	        echo "\t* Solaris release: $desc\n";
	      }
            }
	  break;
          case "SunOS Release":
            $desc = substr($line, strpos($line, ":") + 2);
	    if (!empty($desc)) {
	      if (strcmp($this->data("sunos_release"), $desc)) {
		$this->setData("sunos_release", $desc);
	        echo "\t* SunOs Release: $desc\n";
	      }
            }
	  break;
          case "Unbundled Product":
            $desc = substr($line, strpos($line, ":") + 2);
	    if (!empty($desc)) {
	      if (strcmp($this->data("unbundled_product"), $desc)) {
		$this->setData("unbundled_product", $desc);
	        echo "\t* Unbundled Product: $desc\n";
	      }
            }
	  break;
	  case "Synopsis":
	    $synopsis = trim(substr($line,strpos($line, ":") + 2));
	    $synopsis = $synopsis;
	    if (strcmp($this->synopsis, $synopsis)) {
	      echo "\t- Synopsis updated\n";
	      $this->synopsis = $synopsis;
	      $u++;
	    }
	  break;
          case "Date":
	    $r_date = trim($f[1]);
	    $r_date = $this->parseDate($r_date);
            if ($this->releasedate != $r_date) {
	      echo "\t- Date updated\n";
              $this->releasedate = $r_date;
              $u++;
	    }
          break;
          case "Patches required with this patch":
            $required = trim(substr($line,strpos($line, ":")));
            $required = preg_split("/[\s,]+/", $required);
            foreach ($required as $p) {
	      if (empty($p) || !preg_match("/[0-9]{6}-[0-9]{2}/", $p))
		continue;
 	      $p = Patch::strip($p);
              $po = new Patch();
              $patch = $p; 
              $p = explode("-", $p);
              $po->patch = $p[0];
              $po->revision = $p[1];
              if ($po->patch == 0 || $po->revision == 0) continue;
              if ($po->fetchFromId()) {
                $po->insert();
		$nb++;
              } else {
	        if(!$redo) {
		  $po->to_update = 1;
		  $po->update();
		}
	      }
              if (!$this->isRequired($po)) {
                $this->addRequired($po);
                echo "\t\t* Required by this patch: $patch\n";
              }
            }
	  break;
	  case "Patches which conflict with this patch":
            $conflicts = trim(substr($line,strpos($line, ":")));
            $conflicts = preg_split("/[\s,]+/", $conflicts);
            foreach ($conflicts as $p) {
	      if (empty($p) || !preg_match("/[0-9]{6}-[0-9]{2}/", $p))
                continue;
 	      $p = Patch::strip($p);
              $po = new Patch();
              $patch = $p;
	      $p = explode(",", $p);
	      $p = $p[0];
              $p = explode("-", $p);
              $po->patch = $p[0];
              $po->revision = $p[1];
              if ($po->patch == 0 || $po->revision == 0) continue;
              if ($po->fetchFromId()) {
                $po->insert();
		$nb++;
              } else {
	        if(!$redo) {
                  $po->to_update = 1;
                  $po->update();
		}
              }
              if (!$this->isConflict($po)) {
                $this->addConflict($po);
                echo "\t\t* Conflict with this patch: $patch\n";
              }
            }
	  break;
          case "Patches accumulated and obsoleted by this patch":
            $obso = trim(substr($line,strpos($line, ":")));
	    $obso = preg_split("/[\s,]+/", $obso);
	    foreach ($obso as $p) {
	      if (empty($p) || !preg_match("/[0-9]{6}-[0-9]{2}/", $p))
                continue;
 	      $p = Patch::strip($p);
              $po = new Patch();
	      $patch = $p;
	      $p = explode(",", $p);
	      $p = $p[0];
	      $p = explode("-", $p);
	      $po->patch = $p[0];
	      $po->revision = $p[1];
              if ($po->patch == 0 || $po->revision == 0) continue;
	      if ($po->fetchFromId()) {
                $po->insert();
		$nb++;
	      } else {
	        if(!$redo) {
                  $po->to_update = 1;
                  $po->update();
		}
              }
	      if (strcmp($po->status, "OBSOLETE")) {
                $po->status = "OBSOLETE";
	        $po->update();
  	      }
              if (!$this->isObsolated($po)) {
	        $this->addObsolated($po);
                echo "\t\t* Obsolated by this patch: $patch\n";
	      }
	    }
	  break;
	  case "Keywords":
	    $keys = trim(substr($line,strpos($line, ":")));
	    $keys = preg_split("/[\s,]+/", $keys);
	    foreach($keys as $k) {
	      if (empty($k) || !strcmp($k, ":"))
		continue;
 	      $k = Patch::strip($k);
	      if(!$this->isKeyword($k)) {
	        $kword = new Keyword();
		$kword->keyword = $k;
		if ($kword->fetchFromField("keyword")) {
		  $kword->insert();
		  echo "\t\t* new keyword: $k\n";
		}
		$this->addKeyword($kword);
	      }
	    }
	  break;
          case "Files included with this patch":
            $stepFile = 1;
          break;
	  default:
	    continue;
	  break;
	}
      }
    }
    if (isset($stats) && isset($stats['mod']) && isset($stats['new'])) {
      $stats['new'] += $nb;
      $stats['mod'] += $mod;
    }
  
    return $u;
  }

  public static function treatPatch($file, $dir) {

    if (preg_match("/^README\.[0-9]{6}-[0-9]{2}/", $file) && file_exists($dir."/".$file)) { // this is a readme file
      echo "[-] Patch detected: $file\n";
      $f = explode(".", $file);
      $patchname = $f[1];
      $p = explode("-", $patchname);
      $patch = new Patch($p[0], $p[1]);
      if ($patch->patch == 0 || $patch->revision == 0) {
        echo "[!] Malformed patch number: Patch: ".$patch->patch." | Revision: ".$patch->revision." (file: $file)\n";
        return;
      }
      if ($patch->fetchFromId()) {
        $patch->insert();
        echo "  > New patch: ".$patch->name()."\n";
      }
      $u = 0;
      if (file_exists($dir."/".$patchname.".zip")) {
        $ext = "zip";
      } else if (file_exists($dir."/".$patchname.".tar.Z")) {
        $ext = "tar.Z";
      } else {
        $ext = null;
      }
      if ($ext && file_exists($dir."/".$patchname.".$ext.md5sum")) {
        $md5sum = file_get_contents($dir."/".$patchname.".$ext.md5sum");
        $md5sum = explode(" ", $md5sum);
        $md5sum = $md5sum[0];
        if (strcmp($patch->md5sum, $md5sum)) {
          echo "\t- Updated md5sum: $md5sum\n";
          $patch->md5sum = $md5sum;
          $u++;
        }
      }
      if ($ext && file_exists($dir."/".$patchname.".$ext.sha512sum")) {
        $sha512sum = file_get_contents($dir."/".$patchname.".$ext.sha512sum");
        $sha512sum = explode(" ", $sha512sum);
        $sha512sum = $sha512sum[0];
        if (strcmp($patch->sha512sum, $sha512sum)) {
          $patch->sha512sum = $sha512sum;
          echo "\t- Updated sha512sum: $sha512sum\n";
          $u++;
        }
      }
      if ($ext) {
        $fsize = filesize($dir."/".$patchname.".$ext");
        if ($patch->filesize != $fsize) {
          $patch->filesize = $fsize;
          echo "\t- Updated file size: $fsize\n";
          $u++;
        }
      }
      /* Open readme file and parse it */
      if ($patch->data("readme_done") != 1) {
        $readme = file_get_contents($dir."/".$file);
        if ($patch->readme($readme) > 0) {
          $u++;
          $patch->setData("readme_done", 1);
        }
      }
      if ($u) {
        $patch->update();
      }
      unset($patch);
    }
  }

  /* browse patch repos */
  public static function browseDirectory($dir) {

    $added = 0;
    if (is_dir($dir)) {

	echo "[-] Entering to $dir..\n";
        if ($dh = opendir($dir)) {
          while (($file = readdir($dh)) !== false) {
            if (!strcmp($file, ".") || !strcmp($file, ".."))
              continue;

            if (is_dir($dir."/".$file)) {
	      $added += Patch::browseDirectory($dir."/".$file);
            } else {
	      /* file */
              Patch::treatPatch($file, $dir);
	    }
          }
          closedir($dh);
        }  
	echo "[-] Leaving $dir...\n";
      }
      return $added;
  }

  public static function strip($str) {
    $str = trim($str);
    $len = strlen($str);
    if ($str[0] == ',') {
      $str = substr($str, 1);
      $len = strlen($str);
    }
    if ($str[$len - 1] == ',') {
      $str = substr($str, 0, $len - 1);
      $len = strlen($str);
    }
    if ($str[0] == '#') {
      $str = substr($str, 1);
      $len = strlen($str);
    }
    if ($str[$len - 1] == '#') {
      $str = substr($str, 0, $len - 1);
      $len = strlen($str);
    }

    return $str;
  }
  public function flags() {
    $f = "";
    if ($this->pca_bad) $f .= 'B';
    if ($this->pca_obs) $f .= 'O';
    if ($this->pca_y2k) $f .= 'Y';
    if ($this->pca_sec) $f .= 'S';
    if ($this->pca_rec) $f .= 'R';
    return $f;
  }

  public function colora() {
    if ($this->pca_bad) {
      return "class=\"red\"";
    }
    if (!strcmp($this->status, 'OBSOLETE')) {
      return "class=\"brown\"";
    }
    if ($this->pca_sec) {
      return "class=\"orange\"";
    }
    if ($this->pca_rec) {
      return "class=\"green\"";
    }
  }

  public function svgColor() {
    if ($this->pca_bad) {
      return "color=red";
    }
    if (!strcmp($this->status, 'OBSOLETE')) {
      return "color=rowntd";
    }
    if ($this->pca_sec) {
      return "color=orange";
    }
    if ($this->pca_rec) {
      return "color=green";
    }
  }


  public function color() {
    if ($this->pca_bad) {
      return "class=\"redtd\"";
    }
    if (!strcmp($this->status, 'OBSOLETE')) {
      return "class=\"browntd\"";
    }
    if ($this->pca_sec) {
      return "class=\"orangetd\"";
    }
    if ($this->pca_rec) {
      return "class=\"greentd\"";
    }
  }

  public function checkSum() {
    global $config;
    $this->fetchCSum();
    if (!$this->o_csum)
      return -1;

    $archive = $this->findArchive();
    if (!$archive)
      return -2;

    if (!strcmp($this->md5sum, $this->o_csum->md5))
      return 0;
    else
      return -3;
  }

  public function viewed() {
     $q = 'UPDATE '.$this->_table.' SET `views`=`views`+1 WHERE `patch`='.$this->patch.' AND `revision`='.$this->revision;
     return MysqlCM::getInstance()->rawQuery($q);
  }


 /* static */

  public static function getMostviewed($nb = 10) {

    $res = array();
    $table = "`patches`";
    $index = "`patch`, `revision`";
    $where = " ORDER BY `patches`.`views` DESC LIMIT 0,$nb";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['patch'], $t['revision']);
        $k->fetchFromId();
        array_push($res, $k);
      }
    }
    return $res;
  }

  public static function getLastviewed($l) {

    $res = array();
    $table = "`u_history`";
    $index = "`id_link`";
    $where = "WHERE `id_login`=".$l->id." AND `what`='patch'";
    $where .= " ORDER BY `u_history`.`when` DESC LIMIT 0,10";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $w = $t['id_link'];
        $w = explode('-', $w);
        $k = new Patch($w[0], $w[1]);
        $k->fetchFromId();
        array_push($res, $k);
      }
    }
    return $res;
  }


  public function updateDBReadmes() {

    $fields = array("patch", "revision", "when");
    $this->getAllReadme();
    $orig = $this->readmePath();
    if (file_exists($orig)) {
      $ro = new Readme();
      $ro->patch = $this->patch;
      $ro->revision = $this->revision;
      $ro->when = 0; // orig readme == 0
      if ($ro->fetchFromFields($fields)) {
        $ro->txt = file_get_contents($orig);
        $ro->insert();
        echo "\t> latest readme added\n";
      } else {
        $c = file_get_contents($orig);
        if (strcmp(md5($ro->txt), md5($c))) {
          $ro->txt = $c;
          $ro->update();
          echo "\t> latest readme updated\n";
        }
      }
    }
    foreach($this->a_freadmes as $rfile) {
      $d = explode("-", $rfile);
      $d = $d[2];
      $c = file_get_contents($rfile);
      $ro = new Readme();
      $ro->patch = $this->patch;
      $ro->revision = $this->revision;
      $ro->when = $d;
      if ($ro->fetchFromFields($fields)) {
        $ro->txt = $c;
        $ro->insert();
        echo "\t> $d readme added\n";
      } else {
        if (strcmp(md5($ro->txt), md5($c))) {
          $ro->txt = $c;
          $ro->update();
          echo "\t> $d readme updated\n";
        }
      }
    }
    /* Make the diff ! */
    $this->fetchReadmes(0);
    if (count($this->a_readmes) > 1) { // only make the diff if more than one readme...
      $i = 0;
      $old = null;
      foreach($this->a_readmes as $ro) {
        $ro->fetchFromId();
        if (!$i) { /* first one */
          $i++;
 	  $ro->diff = 'Initial release';
          $old = $ro;
          continue;
        }
        $new = $ro;
        if ($new->when == 0) { // diffing the last readme
          $di = cli_diff($this->readmePath()."-".$old->when, $this->readmePath());
        } else {
          $di = cli_diff($this->readmePath()."-".$old->when, $this->readmePath()."-".$new->when);
        }
        $ro->diff = $di;
        $ro->update();
        $old = $new;
      }
    }
  }

  public function toXML(&$xml, $arg) {

    $xml->push('patch', array('id' => $this->patch, 'rev' => sprintf("%02d", $this->revision)));
    $xml->element('status', $this->status);
    $xml->element('synopsis', $this->synopsis);
    $xml->element('releasedate', $this->releasedate);
    $xml->element('filesize', $this->filesize);
    if ($this->pca_rec) $xml->emptyelement('recommended');
    if ($this->pca_sec) $xml->emptyelement('security');
    if ($this->pca_bad) $xml->emptyelement('bad');
    $xml->element('synopsis', $this->synopsis);
    $xml->element('readme', 'http://wesunsolve.net/readme/id/'.$this->name());
    $xml->element('download', 'https://getupdates.oracle.com/all_unsigned/'.$this->name().'.'.$this->findExt());

    /* Keywords */
    $this->fetchKeywords();
    if (count($this->a_keywords)) {
      $xml->push('keywords');
      foreach($this->a_keywords as $k) {
        $xml->emptyelement('keyword', array('value' => $k->keyword));
      }
      $xml->pop();
    }

    if ($arg) {

      /* Bundles where this patch is present */
      $this->fetchBundles();
      if (count($this->a_bundles)) {
        $xml->push('bundles');
        foreach($this->a_bundles as $b) {
          $xml->push('bundle', array('filename' => $b->filename));
          $xml->element('url', 'http://wesunsolve.net/bundle/id/'.$b->id);
          $xml->pop();
        }
        $xml->pop();
      }
  
  
      /* Patch requirements */
      $this->fetchRequired();
      if (count($this->a_depend)) {
        $xml->push('required');
        foreach($this->a_depend as $p) {
          $xml->emptyelement('patch', array('id' => $p->patch, 'rev' => $p->revision));
        }
        $xml->pop();
      }
  
      /* Patch obsoleted by this one */
      $this->fetchObsolated();
      if (count($this->a_obso)) {
        $xml->push('obsolete');
        foreach($this->a_obso as $p) {
          $xml->emptyelement('patch', array('id' => $p->patch, 'rev' => $p->revision));
        }
        $xml->pop();
      }
  
      /* Conflitcs */
      $this->fetchConflicts();
      if (count($this->a_conflicts)) {
	foreach($this->a_conflicts as $p) {
	  $xml->emptyelement('patch', array('id' => $p->patch, 'rev' => $p->revision));
	}
        $xml->push('conflicts');
        $xml->pop();
  
      }
  
      /* Bugids */
      $this->fetchBugids();
      if (count($this->a_bugids)) {
        $xml->push('bugs');
	foreach($this->a_bugids as $p) {
	  $xml->emptyelement('bug', array('id' => $p->id));
	}
        $xml->pop();
  
      }
  
      /* Bug ids with included patches */
      $this->fetchPrevious(2);
      if (count($this->a_previous)) {
        $xml->push('previousbugs');
        foreach($this->a_previous as $p) {
          if (count($p->a_bugids)) {
	    $xml->push('patch', array('id' => $p->patch, 'rev' => $p->revision));
            foreach($p->a_bugids as $b) {
	      $xml->emptyelement('bug', array('id' => $b->id));
   	    }
	    $xml->pop();
	  }
        }
        $xml->pop();
  
      }
    }
  
    $xml->pop();
  }

  public function extract() {
    global $config;

    $af = $this->findArchive();
    if (!$af) return -1;
    if (!file_exists($af)) return -1;
    if (is_dir($this->extPath().'/'.$this->name())) return 0; /* Already done */
    
    $odir = $this->extPath();
    $rc = extractTmp($af, $odir);

    if (!$rc) {
      return 0;
    }

    return -1;
  }

  public function mkFilesSum() {
    global $config;

    $this->fetchData();
    if ($this->data("cksum_done") == 1) {
      return 0;
    }
    $this->fetchFiles();
  
    $tp = $this->extPath().'/'.$this->name();

    if (!is_dir($tp)) {
      echo "[-] Can't find patch directory\n"; 
      return -1;
    }

    /* Gather packages modified by this patch */
    $pkgs = glob($tp."/*", GLOB_ONLYDIR);
    foreach($pkgs as $pkg) {
      if (!is_dir($pkg))
	continue;

      if (!file_exists($pkg."/pkgmap"))
	continue;

      $pkgname = explode("/", $pkg);
      $pkgname = $pkgname[count($pkgname)-1];
      echo "[>] Found $pkgname:\n";

      /* Find files modified by this package */
      $pkgmap = file($pkg."/pkgmap");
      foreach($pkgmap as $line) {
        $line = trim($line);

        if (empty($line))
	  continue;

        if($line[0] == '#')
	  continue;

	$fields = explode(" ", $line);
        if ($fields[1] != 'f')
	  continue;

	$fpath = $pkg."/reloc/".$fields[3];
        $fname = "/".$fields[3];

	/* Check that the file do exist inside the reloc/ dir */
	if (!file_exists($fpath)) {
          echo "[!] $fpath not inside reloc/\n";
	  continue;
        }

	/* Check that the file is already linked to this patch... */
	if (!$this->isFile($fname)) {
          echo "[!] linking $fname the patch\n";
          $file = new File();
	  $file->name = $fname;
          if ($file->fetchFromField("name")) {
            $file->insert();
	  }
          $this->addFile($file);
	}

	$size = filesize($fpath);
        $h_md5 = md5_file($fpath);
        $h_sha1 = sha1_file($fpath);

	$this->setFileAttr($fname, $size, $h_md5, $h_sha1, $pkgname);
        echo "[>] Updated $fname with:\n";
	echo "\t> size: $size\n";
	echo "\t> h_md5: $h_md5\n";
	echo "\t> h_sha1: $h_sha1\n";
	echo "\t> pkg: $pkgname\n";

      }
    }
    $this->setData("cksum_done", 1);
    return 0;
  }

  public function getFromMaster() {
    global $config;

    if (file_exists($this->path().'/.'.$this->name())) return -1;
    $remotef = $this->path().'/'.$this->name().".*";
    $localf = $this->path().'/';
    $this->checkPath();

    $cmd = "/usr/bin/scp -i ".$config['rsapath']." ".$config['ws2master'].":$remotef $localf";
    $ret = `$cmd`;

    $af = $this->findArchive();
    if ($af && file_exists($af)) {
      return 0;
    }
    return -1;
  }

  public function printPdiag($remObs = false) {
 
    $obs = $this->pca_obs;
    $synopsis = $this->synopsis;
    if ($remObs) {
      if ($obs) {
	$obs = 0;
      }
      $synopsis = preg_replace("/^Obsoleted by: [0-9]{6}-[0-9]{2} /", '', $synopsis);
    }

    $ret = $this->patch.'|';
    $ret .= sprintf("%02d", $this->revision).'|';
    $ret .= date("M/d/y", $this->releasedate).'|';
    if ($this->pca_rec) { $ret .= "R"; } else { $ret .= " "; }
    $ret .= '|';
    if ($this->pca_sec) { $ret .= "S"; } else { $ret .= " "; }
    $ret .= '|';
    if ($obs) { $ret .= "O"; } else { $ret .= " "; }
    $ret .= '|';
    if ($this->pca_bad) { $ret .= " B"; } else { $ret .= "  "; }
    $ret .= '|';
    $ret .= $this->dia_version.'|';
    $ret .= $this->dia_arch.'|';
    $ret .= $this->dia_pkgs.'|';
    $ret .= $synopsis;
    return $ret;
  }


 /**
  * Constructor
  */
  public function __construct($patch=-1,$rev=-1)
  {
    $this->patch = $patch;
    $this->revision = $rev;
    $this->_table = "patches";
    $this->_nfotable = "nfo_patches";
    $this->_my = array(
                        "patch" => SQL_INDEX,
                        "revision" => SQL_INDEX,
                        "status" => SQL_PROPE,
                        "releasedate" => SQL_PROPE,
                        "synopsis" => SQL_PROPE,
                        "requirements" => SQL_PROPE,
                        "filesize" => SQL_PROPE,
                        "md5sum" => SQL_PROPE,
                        "sha512sum" => SQL_PROPE,
                        "pca_rec" => SQL_PROPE,
                        "pca_sec" => SQL_PROPE,
                        "pca_bad" => SQL_PROPE,
                        "pca_obs" => SQL_PROPE,
                        "pca_y2k" => SQL_PROPE,
                        "dia_version" => SQL_PROPE,
                        "dia_pkgs" => SQL_PROPE,
                        "dia_arch" => SQL_PROPE,
                        "to_update" => SQL_PROPE,
                        "views" => SQL_PROPE,
                        "updated" => SQL_PROPE,
                        "added" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "patch" => "patch",
                        "revision" => "revision",
                        "status" => "status",
                        "releasedate" => "releasedate",
                        "synopsis" => "synopsis",
                        "requirements" => "requirements",
                        "filesize" => "filesize",
                        "sha512sum" => "sha512sum",
                        "md5sum" => "md5sum",
                        "pca_rec" => "pca_rec",
                        "pca_sec" => "pca_sec",
                        "pca_bad" => "pca_bad",
                        "pca_obs" => "pca_obs",
                        "pca_y2k" => "pca_y2k",
                        "dia_version" => "dia_version",
                        "dia_pkgs" => "dia_pkgs",
                        "dia_arch" => "dia_arch",
                        "to_update" => "to_update",
                        "views" => "views",
                        "updated" => "updated",
                        "added" => "added"
                 );
  }

}
?>
