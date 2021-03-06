<?php
/**
 * Bundle object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Bundle extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $synopsis = "";
  public $filename = "";
  public $md5 = "";
  public $content = "";
  public $updated = -1;
  public $added = -1;
  public $size = -1;
  public $lastmod = -1;

  public $a_patches = array();
  public $a_comments = array();

  public $a_freadmes = array();
  public $a_readmes = array();

  public function fetchAll($all=2) {
    $this->fetchData();
    $this->fetchPatches($all);
  }

  /* patches contents */
    function addPatch($k) {

    $table = "`jt_bundles_patches`";
    $names = "`bid`, `pid`, `prev`";
    $values = "'$this->id', '".$k->patch."', '".$k->revision."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_patches, $k);
    return 0;
  }

  function delPatch($k) {

    $table = "`jt_bundles_patches`";
    $where = " WHERE `pid`='".$k->patch."' AND `prev`='".$k->revision."' AND `bid`='".$this->id."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_patches as $ak => $v) {
      if ($k->patch == $v->patch && $k->revision == $v->revision) {
        unset($this->a_patches[$ak]);
      }
    }
    return 0;
  }

  function isPatch($p) {
    foreach($this->a_patches as $po)
      if ($p->patch == $po->patch && $p->revision == $po->revision)
        return TRUE;
    return FALSE;
  }

  public static function parseDate($str) {
    if (preg_match("/[0-9]{4}.[0-9]{2}.[0-9]{2}/", $str)) { // 2011.06.13

      $r_date = explode(".", $str);
      return mktime(0,0,0,$r_date[1], $r_date[2], $r_date[0]);

    } else if (preg_match("@[A-Z][a-z]{2}/[0-9]{2}/[0-9]{2}@", $str)) { // Apr/06/09

      $d = explode("/", $str);
      if (count($d) != 3) return 0;
      // Oct/13/2000
      $day = $d[1];
      $year = $d[2];
      $month = $d[0];
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
  }

  function fetchPatches($all=1) {
    $this->a_patches = array();
    $table = "`jt_bundles_patches`";
    $index = "`pid`, `prev`";
    $where = "WHERE `bid`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['pid'], $t['prev']);
        if ($all) $k->fetchFromId();
        array_push($this->a_patches, $k);
      }
    }
    return 0;
  }


  public function parseReadme() {

    $rp = $this->readmePath(); 
    if (!$rp) {
      return false;
    }
    if (!file_exists($rp) || !filesize($rp)) {
      return false;
    }
    $this->fetchData();
    $this->fetchPatches();
    $patches = array();
    $stepPatch = false;

    /* Open readme file and parse it */
    if ($this->data("readme_done") != 1) {
      $readme = file_get_contents($rp);
      $c = explode(PHP_EOL, $readme);
      foreach ($c as $line) {
        $line = trim($line);
        if (empty($line)) {
          continue;
        }
        if ($stepPatch) {
  	  if (preg_match("/^sparc:$/", $line)) {
	    if (preg_match("/sparc/",$this->filename)) { // this is a sparc bundle, we can link the patches
	      $stepPatch = 1;
	    } else {
	      $stepPatch = 2; // wait for next arch
	    }
	  } else if (preg_match("/^x86:$/", $line) && $stepPatch) {
	    if (preg_match("/x86/", $this->filename)) {
	      $stepPatch = 1;
	    } else {
	      $stepPatch = 2; // wait for next arch
	    }
	  } else if ((preg_match("/^[-]*$/", $line) ||
		      preg_match("/Note that the patch list order below reflects the patch install order./", $line)) && $stepPatch) {
	    continue;
	  } else if (preg_match("/^[0-9]{6}-[0-9]{2}/", $line) && $stepPatch && $stepPatch != 2) {
	    $f = preg_split("/[\s,]+/", $line);
            if (isset($f[0]) && !empty($f[0])) {
              $p = explode("-", $f[0]);
	      $patch = new Patch($p[0], $p[1]);
	      if ($patch->fetchFromId()) {
 	        $patch->insert();
	        echo "[-] New patch inserted: ".$patch->name()."\n";
	      }
	      if (!$this->isPatch($patch)) {
	        $this->addPatch($patch);
	        echo "[-] Patch added to the bundle: ".$patch->name()."\n";
	      }
	      $patches[] = $patch;
	    }
	  } else if ($stepPatch == 2) {
	    continue;
          } else {
	    $stepPatch = false;
	  }
	}
        $f = explode(":", $line);
        if (count($f)) {
	  switch($f[0]) {
	    case "PASSCODE":
	      $passcode = trim($f[1]);
	      $passcode = $passcode;
	      if (strcmp($this->data("passcode"), $passcode)) {
	        $this->setData("passcode", $passcode);
	      }
	      echo "  > Updated passcode to be $passcode\n";
	    break;
            case "DATE":
	      $r_date = $f[1];
	      $r_date = trim($r_date);
	      /*
2011.06.13
Apr/06/09
	      */
	      if (preg_match("@[A-Z][a-z]{2}/[0-9]{2}/[0-9]{2}@", $r_date) || preg_match("/[0-9]{4}.[0-9]{2}.[0-9]{2}/", $r_date)) { // Apr/06/09

		$r_date = Bundle::parseDate(trim($r_date));

	      } else { // unknown date format
		break;
	      }
	      if ($r_date != $this->lastmod) {
	        $this->lastmod = $r_date;
		$this->update();
	        echo "  > Updated release date to be $r_date\n";
	      }
	    break;
            case "NAME":
	      $synopsis = trim($f[1]);
	      $synopsis = $synopsis;
	      if (strcmp($this->synopsis, $synopsis)) {
                $this->synopsis = $synopsis;
		$this->update();
	      }
	      echo "  > Updated synopsis to be $synopsis\n";
	    break;
	    case "PATCHES INCLUDED":
	      $stepPatch = 1;
	    break;
	  }
	}
      }
      /* Loop through detected patch to sse if there were some removal... */
      $c = count($this->a_patches);
      for ($i=0;$i<$c;$i++) {
	$ptmp = $this->a_patches[$i];
        $found = 0;
	foreach($patches as $pa) {
	  if ($pa->patch == $ptmp->patch &&
	      $pa->revision == $ptmp->revision) {
	    $found = 1;
	    break;
	  }
	}
        if (!$found) { /* we should remove this patch from bundle's list */
	  $this->delPatch($ptmp);
          echo "[-] Patch removed from the bundle: ".$ptmp->name()."\n";
	}
      }
      $this->setData("readme_done", 1);
    }

  }

  public function findArchive() {

    $archive = $this->getFileName();

    if (file_exists($archive)) {
      return $archive;
    } else {
      return null;
    }
  }

  public static function detectBundles() {

    $bundles = array();
    $table = "`checksums`";
    $index = "`id`";
    $cindex = "COUNT(`id`)";
    $where = "";
    $it = new mIterator("Checksum", $index, $table, $where, $cindex);
    echo "[-] Detecting potential bundles...\n";
    while(($checksum = $it->next())) {

      $checksum->fetchFromId();
      $checksum->fetchData();

      if (!preg_match('/[0-9]{6}-[0-9]{2}/', $checksum->name)
      && (preg_match('/\.zip$/', $checksum->name)
      || preg_match('/\.tar\.Z$/', $checksum->name))
      && !preg_match('/part[0-9].zip/', $checksum->name)) {

	$b = new Bundle();
	$b->filename = $checksum->name;
        if ($b->fetchFromField("filename")) {
          $b->insert();
	  echo "[>] New bundle detected: ".$b->filename."\n";
	  Announce::getInstance()->msg(2, "[BATCH] Bundle ".$b->filename." has been added !");
	}
	$b->fetchData();
	if (strcmp($b->md5, $checksum->md5)) {
          echo "    > MD5 changed for ".$b->filename."\n";
	  Announce::getInstance()->msg(2, "[BATCH] Bundle ".$b->filename." has been updated!");
          $b->lastmod = time(); // New release of this bundle
	  $b->md5 = $checksum->md5;
	  $b->setData("readme_done", 0);
          $b->update();
	}
        $size = preg_split("/[\s ]+/", $checksum->sysv);
        $size = $size[1] * 512;
        if ($b->size != $size) {
          echo "    > size changed for ".$b->filename."\n";
          $b->size = $size;
          $b->update();
	}

      }
    }
  }

  public static function rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") Bundle::rrmdir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
  }

  public function checkReadme() {
    global $config;

    echo "[-] Checking README file for ".$this->filename."\n";
    $rp = $this->readmePath();
    $archive = $this->findArchive();
    if (!$archive) { // No arm, no chocolates.
      return false;
    }
    $ext = explode(".", $this->filename);
    $ext = $ext[count($ext) - 1];
    $op = $config['bndlpath']."/".$this->filename."/";
    if (!file_exists($rp) && file_exists($archive)) {
      $path = $config['bndlpath'];
      switch($ext) {
        case "zip":
          $cmd = "/usr/bin/unzip -o -j $archive \"*README*\" -x */*/* -d $op > /dev/null 2>&1";
//9_Recommended_CPU_2011-07.README
	break;
	case "Z":
	  $cmd = "/bin/gzip -dc $archive | /bin/tar --wildcards --no-recursion -C $op -xf - \"*README*\" > /dev/null 2>&1"; 
          $cmd2 = "find $op -type f|while read f; do mv \"\$f\" $op; done > /dev/null 2>&1";
	break;
      }
      echo "[-] Extracting README for ".$this->filename."..";
      $ret = `mkdir -p $op`;
      $ret = `$cmd`;
      if(isset($cmd2)) $ret = `$cmd2`;

      /* New readme file name scheme, thx oracle */
      $rfn = explode(".", $this->filename);
      $rfn = $rfn[0].".README";

      if (file_exists($op."CLUSTER_README")) {
        rename($op."CLUSTER_README", $rp);
        echo "done\n";
      } else if (file_exists($op."README")) {
	rename($op."README", $rp);
	echo "done\n";
      } else if (file_exists($op.$rfn)) {
	rename($op.$rfn, $rp);
	echo "done\n";
      } else {
        /* TODO: Add mechanism not to retry readme extraction each time batch runs */
        echo "failed\n";
      }
      Bundle::rrmdir($op); // Clean temp files
      
    }
  }

  public static function downloadMissing() {

    echo "[-] Loading bundles .";
    $bundles = array();
    $table = "`bundles`";
    $index = "`id`";
    $where = "";
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $g = new Bundle($t['id']);
        $g->fetchFromId();
        array_push($bundles, $g);
      }
    }
    echo "[-] Browsing the bundle array for missing archives...\n";
    foreach($bundles as $bundle) {
      echo "[-] Checking for ".$bundle->filename." ...";
      if (file_exists($bundle->getFileName())) {
        echo "present\n";
      } else {
        echo "not found\n";
        echo "[>] Trying to download ".$bundle->filename."..";
        $bundle->download();
        if (file_exists($bundle->getFileName())) {
	  echo "success\n";
	} else {
	  echo "failed\n";
	}
      }
    }
  }

  public function readmePath() {
    global $config;
    if (empty($this->md5) || empty($this->filename))
      return false;

    return $config['bndlpath']."/README.".$this->filename."-".$this->md5;
  }

  public function getFileName() {
    global $config;
    if (empty($this->md5) || empty($this->filename)) 
      return false;

    return $config['bndlpath']."/".$this->filename."-".$this->md5;
  }

  public static function checkUpdates() {
    global $config;

    $bundles = array();
    $table = "`bundles`";
    $index = "`id`";
    $where = "";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $g = new Bundle($t['id']);
        $g->fetchFromId();
        array_push($bundles, $g);
      }
    }

    echo "[-] Checking if there are bundles which need an update ...\n";
    foreach($bundles as $bundle) {
      echo "[-] Checking for ".$bundle->filename." ...";
      if (($d = $bundle->checkReadmeUpdate()) >= 0) {
        echo "Updated! ($d vs ".$bundle->lastmod.")\n";
        echo "[>] Trying to download ".$bundle->filename."..";
        $r = rand(1,60000);
        $out =  $config['bndlpath']."/".$bundle->filename.'-'.$r;
        $bundle->download($r);
        if (file_exists($out)) {
	  echo "success\n";
          $md5_new = md5_file($out);
	  if (!strcmp($md5_new, $bundle->md5)) {
	    /* update checksum update... */
	    $cs = new Checksum();
	    $cs->name = $bundle->filename;
	    if ($cs->fetchFromField("name")) {
	      $cs->md5 = $md5_new;
	      $cs->sysv = "";
	      $cs->sum = "";
	      $cs->cs_async = 1; // async from the CHECKSUMS file
	      $cs->update();
	    }
	    echo "[-] Updated checksum: $md5_new\n";
	    $bundle->md5 = $md5_new;
	    $bundle->size = filesize($out);
	    rename($out, $bundle->getFileName());
	    $bundle->fetchData();
	    $bundle->setData("readme_done", 0);
            $bundle->checkReadme();
            $bundle->parseReadme();
	    $bundle->update();
	    echo "[-] Updated and renamed bundle to ".$bundle->getFileName()."\n";
	  } else {
	    /* It was a fucking mistake, rm everything... */
	    unlink($out);
	    echo "[!] MD5 sum is the same... we're fucked...\n";
	    continue;
	  }
	} else {
	  echo "failed\n";
	}
      } else { 
	echo "Up to date ($d vs ".$bundle->lastmod.")\n"; 
      }
    }

  }


 public function checkReadmeUpdate() {
   global $config;

   $r = rand(1, 60000);

   $rfn = explode(".", $this->filename);
   $rfn = $rfn[0];

   $out = $config['tmppath'].'/'.$r.'.'.$rfn;

   $url = $config['readmeurl'].'/'.$rfn;
   $cmd = "/usr/bin/wget -q -O \"$out\"  --user=\"".$config['MOSuser']."\" --password=\"".$config['MOSpass']."\" --no-check-certificate \"$url\"";
   $ret = `$cmd`;

   if (!file_exists($out))
     return -2;

   $f = file($out, FILE_IGNORE_NEW_LINES);
   unlink($out);

   foreach($f as $line) {
     if (empty($line)) continue;
     if (preg_match('/^DATE: /', $line)) {
       $d = explode(' ', $line);
       $last = Bundle::parseDate(trim($d[1]));

       if ($last > $this->lastmod) {
         $this->lastmod = $last;
	 return $last;
       }
       break;
     }
   }
   return -1;
 }

 public function download($str = null) {
    global $config;

    if (!$str) {
      $out = $this->getFileName();
      if (!$out) 
        return false;
    } else {
      $out =  $config['bndlpath']."/".$this->filename.'-'.$str;
    }

    if (file_exists($out)) {
      unlink($out);
    }

    $cmd = "/usr/bin/wget -q -O \"$out\"  --user=\"".$config['MOSuser']."\" --password=\"".$config['MOSpass']."\" --no-check-certificate \"".$config['bndlurl']."/".$this->filename."\"";
    $ret = `$cmd`;

    if (file_exists($out) && filesize($out)) {
      return 0;
    } else {
      if (!filesize($out)) {
        unlink($out);
      }
      return -1;
    }
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
    $where = "WHERE `type`='bundle' AND `id_on`='".$this->id."' AND (`is_private`=0 OR (`id_login`=$id AND `is_private`=1)) ORDER BY `added` ASC
"; 

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

  public function getAllReadme() {
    global $config;
    $this->a_freadmes = array();
    foreach(glob($config['bndlpath']."/README.".$this->filename."-*") as $r) {
      $dd = explode('-', $r);
      if (strlen($dd[1]) < 20)
        $this->a_freadmes[] = $r;
    }
  }

  /* Readmes */
  function fetchReadmes($all=1) {

    $this->a_readmes = array();
    $table = "`b_readmes`";
    $index = "`when`";
    $where = "WHERE `id`='".$this->id."' ORDER BY `when` ASC";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new BReadme();
        $k->id = $this->id;
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


/*
 * @TODO: Should be adapted as current name scheme of bundle is
 * filename-md5 and patches were patchid-rev-tstamp
 */
 public function updateDBReadmes() {
    global $config;

    $fields = array("id", "when");
    $this->getAllReadme();
    $orig = $this->readmePath();
    if (file_exists($orig)) {
      $ro = new BReadme();
      $ro->id = $this->id;
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
      $d = $d[1];
      $c = file_get_contents($rfile);
      $ro = new BReadme();
      $ro->id = $this->id;
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
        echo "\t> diffing \n";
        if ($new->when == 0) { // diffing the last readme
          $di = cli_diff($config['bndlpath']."/README.".$this->filename."-".$old->when, $this->readmePath());
        } else {
          $di = cli_diff($config['bndlpath']."/README.".$this->filename."-".$old->when, $config['bndlpath']."/README.".$this->filename."-".$new->when);
        }
        $ro->diff = $di;
        $ro->update();
        $old = $new;
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
    $this->_table = "bundles";
    $this->_nfotable = "nfo_bundles";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "filename" => SQL_PROPE,
                        "synopsis" => SQL_PROPE,
                        "md5" => SQL_PROPE,
                        "added" => SQL_PROPE,
                        "updated" => SQL_PROPE,
                        "size" => SQL_PROPE,
                        "lastmod" => SQL_PROPE,
			"content" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "synopsis" => "synopsis",
                        "md5" => "md5",
                        "size" => "size",
                        "lastmod" => "lastmod",
                        "filename" => "filename",
                        "updated" => "updated",
                        "added" => "added",
                        "content" => "content"
                 );
  }

}
?>
