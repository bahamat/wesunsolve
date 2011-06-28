#!/usr/bin/php
<?php
/**
 * Big Batch Job
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package CLI
 * @category utils
 * @subpackage list
 * @filesource
 */

  require_once("../libs/config.inc.php");
  require_once("../libs/autoload.lib.php");
  require_once("../libs/functions.lib.php");

  $stats['new'] = 0;
  $stats['mod'] = 0;

  $shortopts = "i::hxpdrntfc";
  $opts = getopt($shortopts);

  if (isset($opts['h'])) {
    echo $argv[0]." [-hxp] [-i <patchid>]\n";
    echo "\t -h\tHelp\n";
    echo "\t -c\tupdate the CHECKSUM file\n";
    echo "\t -x\tDo not update the patchdiag.xref\n";
    echo "\t -p\tDo not parse the patchdiag.xref\n";
    echo "\t -d\tDo not download anything\n";
    echo "\t -n\tDo not parse anything\n";
    echo "\t -r\tParse the readme anyway\n";
    echo "\t -t\tTreat the To Add queue\n";
    echo "\t -f\tForce README extract/download\n";
    echo "\t -i=<id>\tOnly treat this patch\n";
    exit();
  }

  echo "[-] Connecting to MySQL...";
  $m = mysqlCM::getInstance();
  if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
  }
  echo "done\n";

  /* Update CHECKSUM file */
  if (isset($opts['c'])) {
    echo "[-] Downloading CHECKSUMS file...";
    if (Checksum::downloadFile()) {
      echo "failed\n";
      exit;
    } else {
      echo "done\n";
    }
    echo "[-] Parsing CHECKSUMS for new entries...\n";
    Checksum::updateFile();
    return;
  }

  if (!isset($opts['x']) && !isset($opts['d']) && !isset($opts['t'])) {
    echo "[-] Updating the patchdiag.xref file...";
    if (Patch::updatePatchdiag()) {
      echo "failed\n";
      exit;
    } else {
      echo "done\n";
    }
  }

  if (!isset($opts['p']) && !isset($opts['t'])) {
    echo "[-] Parsing patchdiag.xref for new patches...\n";
    Patch::parsePatchdiag();
  }
  
  $patches = array();
  if (isset($opts['t'])) {
    echo "[-] Fetching the toadd queue from databases.";
    $table = "`toadd`";
    $index = "`patch`, `rev`";
    $where = "";
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      $i=0;
      foreach($idx as $t) {
        if ($i==1000) { echo "."; $i=0; }
        $i++;
        $g = new Patch($t['patch'], $t['rev']);
        array_push($patches, $g);
      }
    }
    echo "done\n";
  } else {
    echo "[-] Fetching patches from databases.";
    $table = "`patches`";
    $index = "`patch`, `revision`";
    $where = "";
    if (isset($opts['i'])) {
      $p = explode("-", $opts['i']);
      if (count($p) != 2) { die("Malformed patch id\n"); }
      $where .= "WHERE `patch`='".$p[0]."' AND `revision`='".$p[1]."'";
    }
    $where .= " ORDER BY `releasedate` DESC";
  
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      $i=0;
      foreach($idx as $t) {
        if ($i==1000) { echo "."; $i=0; }
        $i++;
        $g = new Patch($t['patch'], $t['revision']);
        array_push($patches, $g);
      }
    }
    echo "done\n";
  }

  echo "[-] Trying to treat ".count($patches)." patches:\n";
  for ($i=0,$cnt=0, $c = count($patches); $i<$c; $i++,$cnt++) {
    if ($cnt == 100) {
       echo "[-] Still left ".($c-$i)." to treat...\n";
       $cnt=0;
    }
    $p = $patches[$i];
    if ($p->fetchFromId() && isset($opts['t'])) {
      echo "[-] Added new patch: ".$p->name()."\n";
      $p->insert();
    }

    $archive = $p->findArchive();
    if ($archive == null && !$p->isAlreadyTried()) { /* New patch to download */
      if (!isset($opts['d'])) {
        $p->checkPath();
        $p->tryDownload();
      }
    }
    if (!$archive) $archive = $p->findArchive();
    if ($archive != null) {
      
      if ($p->updateArchiveSize($archive)) {
        echo "[-] Updated filesize: ".$p->filesize."\n";
	$p->update();
      }

      if (!file_exists($archive.".md5sum")) {
        echo "[-] Generating MD5..";
        $p->makeMD5($archive, $archive.".md5sum");
        echo "done\n";
      }
      if (!file_exists($archive.".sha512sum")) {
        echo "[-] Generating SHA512..";
        $p->makeSHA512($archive, $archive.".sha512sum");
        echo "done\n";
      }
      if (!file_exists($p->readmePath())) {
        echo "[-] Trying to extract README for ".$p->name()."..";
	$ret = $p->extractReadme();
	if (!$ret) {
	  echo "done\n";
        } else {
	  echo "failed\n";
        }
      }
    }
    if (file_exists($p->readmePath()) && !filesize($p->readmePath())) {
      unlink($p->readmePath());
    }
    if (!file_exists($p->readmePath()) && (!file_exists($p->path()."/.README.".$p->name()) || isset($opts['f']))) { /* try to download the readme file */
      if (!isset($opts['d'])) {
        echo "[-] Trying to download README for ".$p->name()."..";
        $ret = $p->downloadReadme();
        if ($ret) {
    	  echo "failed\n";
        } else {
  	  echo "done\n";
        }
      }
    }
    if (file_exists($p->readmePath()) && !isset($opts['n'])) {
      $p->fetchData();
      if ($p->data("readme_done") != 1 || isset($opts['r']) || isset($opts['f'])) {
        echo "[-] Loading ".$p->name()." patch datas...";
        $p->fetchFromId();
        $p->fetchAll();
        echo "done\n";
        echo "[-] Parsing the readme file...\n";
	$fc = file_get_contents($p->readmePath());
        $ret = $p->readme($fc);
        unset($fc);
        if ($ret > 0) {
	  $p->setData("readme_done", 1);
	  $p->update();
	}
      }
    }
    if (isset($opts['t'])) {
      /* Remove from toadd */
      $q = "DELETE FROM `toadd` WHERE `patch`='".$p->patch."' AND `rev`='".$p->revision."'";
      $m->rawQuery($q);
    }
    unset($p);
    $patches[$i] = null;
  }

  IrcMsg::add("[BATCH] Finished batch run. ".$stats['new']." new patches | ".$stats['mod']." patches updated.");

?>
