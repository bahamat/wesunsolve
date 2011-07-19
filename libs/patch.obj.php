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

class Patch extends mysqlObj
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
  public $to_update = 0;
  public $updated = 0;
  public $added = 0;

  /* Fulltext search */
  public $score = 0;

  /* Others */
  public $o_latest = null;
  public $o_current = null;
  
  /* Lists */
  public $a_files = array();
  public $a_keywords = array();
  public $a_bugids = array();
  public $a_releases = array();
  public $a_depend = array();
  public $a_obso = array();
  public $a_conflicts = array();

  public $a_previous = array();

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

  /* Patch file management */
  public static function updatePatchdiag() {
    global $config;

    $out = $config['tmppath']."/patchdiag.xref";
    if (file_exists($out)) {
      unlink($out);
    }

    $cmd = "/usr/bin/wget -q -O \"$out\" --no-check-certificate ".$config['patchdiag'];
    $ret = `$cmd`;

    if (file_exists($out) && filesize($out)) {
      $fn = $config['pdiagpath']."/patchdiag.xref-".(date("dmY"));
      if (file_exists($fn))
	unlink($fn);
      copy($out, $fn);
      Announce::getInstance()->msg(0, "[BATCH] Updated patchdiag.xref (size: ".filesize($out).")");
      return 0;
    } else {
      return -1;
    }
  }

  public static function parsePatchdiag() {
    global $config, $stats;

    $file = $config['tmppath']."/patchdiag.xref";
    if (!file_exists($file)) {
      return -1;
    }
    $lines = file($file);
    $nb=0;
    $mod=0;
    foreach ($lines as $line) {
      if (empty($line)) {
	continue;
      }
      if ($line[0] == "#") {
	continue;
      }
      $fields = explode("|", $line);
      $pid = $fields[0];
      $rev = $fields[1];
      $pca_rec = 0;
      $pca_sec = 0;
      $pca_bad = 0;
      if ($fields[4] == "S") {
        $pca_sec = 1;
      }
      if ($fields[3] == "R") {
        $pca_rec = 1;
      }
      if (!strcmp(trim($fields[5]), "B") || !strcmp(trim($fields[5]), "YB")) {
        $pca_bad = 1;
      }
      $r_date = $fields[2];
      $synopsis = $fields[count($fields) - 1];
      $patch = new Patch($pid, $rev);
      if ($patch->fetchFromId()) {
        echo "   > New patch: ".$patch->name()."\n";
        $ip = new Ircnp();
	$ip->p = $patch->patch;
	$ip->r = $patch->revision;
	Announce::getInstance()->nPatch($ip);
	$patch->insert();
        $nb++;
        Announce::getInstance()->msg(0, "[BATCH] New patch found in patchdiag.xref (".$patch->name().")");
      }
      if ($patch->pca_rec != $pca_rec || $patch->pca_sec != $pca_sec || $patch->pca_bad != $pca_bad) {
        $patch->pca_rec = $pca_rec;
        $patch->pca_sec = $pca_sec;
        $patch->pca_bad = $pca_bad;
        if($pca_bad) {
          $patch->status = "WITHDRAWN";
	} else {
	  if (strcmp($patch->status, 'OBSOLETE')) {
            $patch->status = "RELEASED";
	  }
        }
	$patch->update();
        $mod++;
	echo "   > Updated PCA flags for ".$patch->name()."\n";
      }
      if (strlen($patch->synopsis) < 10 && strcmp($patch->synopsis, $synopsis)) {
        $patch->synopsis = $synopsis;
        $patch->update();
        $mod++;
	echo "   > Updated synopsis for ".$patch->name()."\n";
      }
      $r_date = $patch->parseDate($r_date);
      if (!$patch->releasedate && $r_date) {
	$patch->releasedate = $r_date;
	$patch->update();
        $mod++;
	echo "   > Updated release date for ".$patch->name()."\n";
      }
    }
    echo "[-] Done parsing patchdiag.xref, $nb new patches\n";

    if (isset($stats) && isset($stats['new']) && isset($stats['mod'])) {
      $stats['new'] += $nb;
      $stats['mod'] += $mod;
    }

    return 0;
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

  public function path() {

    global $config;

    $dir1 = substr($this->patch, 0, 2);
    $dir2 = substr($this->patch, 2, 2);
    return $config['ppath']."/$dir1/$dir2";
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

  public function name() {
    return sprintf("%d-%02d", $this->patch, $this->revision);
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
    $table = "`jt_patches_files`";
    $index = "`fileid`";
    $where = "WHERE `patchid`='".$this->patch."' AND `revision`='".$this->revision."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    { 
      foreach($idx as $t) {
        $k = new File($t['fileid']);
        $k->fetchFromId();
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
  public function readme(&$c) {
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
      if (empty($line) && !$stepFile) {
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
      if (preg_match("/^[0-9]{7}[\s:]/", $line) && strlen($line) > 7) { // Bugid desc !
	$u++;
        if ($line[7] == ":") {
          $synopsis = substr($line, strpos($line, ":") + 2);
          $id = trim($f[0]);
        } else if ($line[7] == " ") {
	  $synopsis = substr($line, strpos($line, " ") + 1);
	  $id = trim(substr($line, 0, strpos($line, " ")));
	} else if ($line[7] == "\t") {
	  $synopsis = substr($line, strpos($line, "\t") + 1);
	  $id = trim(substr($line, 0, strpos($line, "\t")));
        } else {
	  echo "[!] Wrong bugid line: $line\n";
	  continue;
	}

	$bo = new Bugid($id);
        if ($bo->fetchFromId()) {
	  $bo->insert();
          $bo->flag_update();
	  echo "\t* New bugid $id\n";
        }
	if (strlen($bo->synopsis) < 10 && strcmp($bo->synopsis, $synopsis)) {
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
	}
	$cPatch = $patch;
	$stepBugs = 1;
	$cPatch->fetchBugids();
	//unset($patch); // will be fetched next time
      } else if (preg_match("/^OBSOLETE Patch-ID# ".$this->name()."$/", $line)) {
        $this->status = "OBSOLETE";
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
                        "to_update" => SQL_PROPE,
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
                        "to_update" => "to_update",
                        "updated" => "updated",
                        "added" => "added"
                 );
  }

}
?>
