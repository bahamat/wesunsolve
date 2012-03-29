<?php
 /**
  * CVE object
  * @author Gouverneur Thomas <tgo@espix.net>
  * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage job
  * @category classes
  * @filesource
  */

class CVE extends mysqlObj
{
  public $id = -1;		/* ID in the MySQL table */
  public $name = '';
  public $affect = '';
  public $desc = '';
  public $score = '';
  public $severity = '';
  public $revised = -1;
  public $released = -1;
  public $txtfix = '';
  public $added = -1;
  public $updated = -1;

  public $a_patches = array();
  public $a_comments = array();

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
    $where = "WHERE `type`='cve' AND `id_on`='".$this->id."' AND (`is_private`=0 OR (`id_login`=$id AND `is_private`=1)) ORDER BY `added` ASC";

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


  function link() {
    return '<a href="/cve/id/'.$this->id.'">'.$this->name.'</a>';
  }

  function __toString() {
    return $this->name;
  }

  function fetchAll($all) {
    $this->fetchPatches($all);
    $this->fetchComments($all);
  }

  function isNew() {
    /*placeholder*/
  }
  function color() {
    switch($this->severity) {
      case "LOW":
        return 'class="greentd"';
        break;
      case "MEDIUM":
        return 'class="orangetd"';
        break;
      case "HIGH":
	return 'class="redtd"';
        break;
      default:
        return '';
    }
  }

  function viewed() {
    /*placeholder*/
  }

  function addPatch($k) {

    $table = "`jt_patches_cve`";
    $names = "`cveid`, `patchid`, `revision`";
    $values = "'$this->id', '".$k->patch."', '".$k->revision."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_patches, $k);
    return 0;
  }

  function delPatch($k) {

    $table = "`jt_patches_cve`";
    $where = " WHERE `cveid`='".$this->id."' AND `patchid`='".$k->patch."' AND `revision`='".$k->revision."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_patches as $ak => $v) {
      if ($k->patch == $v->patch &&
	  $k->revision == $v->revision) {
        unset($this->a_patches[$ak]);
      }
    }
    return 0;
  }

  function isPatch($k) {
    foreach($this->a_patches as $ko)
      if ($ko->patch == $k->patch &&
	  $ko->revision == $k->revision)
        return TRUE;
    return FALSE;
  }

  function fetchPatches($all=1) {

    $this->a_patches = array();
    $table = "`jt_patches_cve`";
    $index = "`patchid`, `revision`";
    $where = "WHERE `cveid`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['patchid'], $t['revision']);
        if ($all) $k->fetchFromId();
        array_push($this->a_patches, $k);
      }
    }
    return 0;
  }


  public function update() {
    $this->updated = time();
    parent::update();
  }

  public function insert() {
    $this->added = time();
    parent::insert();
  }

  public function refresh() {
    global $config;
    $cmd = $config['rootpath'].'/bin/cveInfos.py '.$this->name;
    $infos = array();
    exec('/srv/sunsolve/bin/cveInfos.py '.$this->name, $infos);
    if (!count($infos)) {
      echo "[!] No information could be found on the CVE.. something wrong happened\n";
      return false;
    }
    $i=0;
    $desc = '';
    $score = '';
    $severity = '';
    $revised = '';
    $released = '';
    foreach($infos as $line) {
      switch($i) {
        case 0:
          $released = explode('/', preg_replace('/Original release date:/', '', $line));
          if (count($released) < 3) {
            $released = '';
	    break;
          }
          $released = mktime(0,0,0,$released[0], $released[1], $released[2]);
          break;
        case 1:
          $revised = explode('/', preg_replace('/Last revised:/', '', $line));
          if (count($revised) < 3) {
            $revised = '';
	    break;
          }
          $revised = mktime(0,0,0,$revised[0], $revised[1], $revised[2]);
          break;
        case 2:
          $score = $line;
          break;
        default:
          $line = trim($line);
          if (empty($line)) continue;
          $desc .= ' '.$line;
          break; 
      }
      $i++;
    }
    $score = preg_replace('/CVSS v2 Base Score:([0-9]{1,2}\.[0-9]{1})\(([A-Z]*)\).*$/', '${1} ${2}', $score);
    $score = explode(' ', $score);
    if (count($score) >= 2) {
      $severity = $score[1];
    }
    $score = $score[0];
    $this->desc = $desc;
    $this->score = $score;
    $this->severity = $severity;
    $this->released = $released;
    $this->revised = $revised;
    echo "\t> Found desc: $desc\n";
    echo "\t> Found score: $score\n";
    echo "\t> Found severity: $severity\n";
    echo "\t> Found released: $released\n";
    echo "\t> Found revised: $revised\n";
    $this->update();
  }

  public static function detectNew() {
    global $config;
    $cmdDetect = array(
		       $config['rootpath'].'/bin/cveDetect.py',
    		       $config['rootpath'].'/bin/cveDetectAlt.py'
		      );
    foreach($cmdDetect as $cmd) {
      echo "[-] Detecting CVE from $cmd...\n";
      $cves = array();
      exec($cmd, $cves);
      if (!count($cves)) {
        echo "[!] No CVE detected\n";
        return false;
      }
      foreach($cves as $line) {
        $line = trim($line);
        if (empty($line)) {
          continue;
        }
        $f = explode(';', $line);
        if (count($f) < 3)
          continue;
        $cvename = $f[0];
        $affect = $f[1];
        $fix = $f[2];
        $cve = new CVE();
        $cve->name = $cvename;
        if ($cve->fetchFromField("name")) {
          $cve->insert();
          echo "[-] Found new CVE: $cvename\n";
        }
        if (strcmp($cve->affect, $affect)) {
          $cve->affect = $affect;
          $cve->update();
          echo "[-] $cve affect $affect\n";
        }
        if (empty($cve->desc) || empty($cve->score) || empty($cve->severity) ||
            $cve->released <= 0 || $cve->revised <= 0) {
    
          $cve->refresh();
        }
        $cve->fetchPatches(1);
        $fixes = explode(',', $fix);
        foreach($fixes as $fix) { 
          $fix = trim($fix);
          if (empty($fix)) continue;
          if (!preg_match("/[0-9]{6}-[0-9]{2}/", $fix)) { // this is not a patch
            echo "[!] Found fix for $cvename which is not a patch: $fix\n";
            continue;
          }
          $p = explode('-', $fix);
          $p = new Patch($p[0], $p[1]);
          if ($p->fetchFromId()) {
            echo "[!] Patch not found $p\n";
            continue;
          }
          if (!$cve->isPatch($p)) {
            $cve->addPatch($p);
            echo "[-] Linked $p to $cve\n";
          }
        }
      }
    }
  }

  /* ctor */
  public function __construct($id=-1, $daemon=null)
  { 
    $this->id = $id;
    $this->_table = "cve";

    $this->added = time();

    $this->_my = array( 
			"id" => SQL_INDEX, 
		        "name" => SQL_PROPE,
			"affect" => SQL_PROPE,
			"score" => SQL_PROPE,
			"severity" => SQL_PROPE,
			"desc" => SQL_PROPE,
			"released" => SQL_PROPE,
			"revised" => SQL_PROPE,
			"txtfix" => SQL_PROPE,
			"added" => SQL_PROPE,
			"updated" => SQL_PROPE
 		 );


    $this->_myc = array( /* mysql => class */
			"id" => "id", 
			"name" => "name",
			"affect" => "affect",
			"score" => "score",
			"desc" => "desc",
			"severity" => "severity",
			"released" => "released",
			"revised" => "revised",
			"txtfix" => "txtfix",
			"added" => "added",
			"updated" => "updated"
 		 );
  }
}

?>
