<?php
/**
 * Patch Level object
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


class PLevel extends mysqlObj
{
  public $id = -1;
  public $id_server = "";
  public $name = "";
  public $comment = "";
  public $is_current = 0;
  public $is_applied = 0;
  public $added = -1;
  public $updated = -1;
  
  public $o_server = null;
  public $a_patches = array();
  public $a_srv4pkgs = array();

  /* PCA Run output */
  public $a_ppatches = array(); // PCA Patches
  public $a_pcvep = array();	// PCA CVE Fix Patches
  public $a_apcvep = array();	// PCA Accumulated CVE Fix Patches

  public $cnt_pcap = 0; // patches to install
  public $cnt_pcar = 0; // recommended
  public $cnt_pcas = 0; // security
  public $cnt_pcaa = 0; // accumulated
  public $cnt_cvep = 0; // patch fixing a cve

  function __toString() {
    return $this->name;
  }

  function fetchFiles() {

    foreach($this->a_patches as $p) {
      $p->fetchFiles();
    }
    return 0;
  }

  function fetchServer() {
    $this->o_server = new Server($this->id_server);
    return $this->o_server->fetchFromId();
  }

  function fetchSRV4Pkgs($all=1) {
    $this->a_srv4pkgs = array();
    $table = "`jt_srv4pkg_plevel`";
    $index = "`id_srv4pkg`, `arch`, `version`";
    $where = "WHERE `id_plevel`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new SRV4Pkg($t['id_srv4pkg']);
        $k->fetchFromId();
        $k->arch = $t['arch'];
        $k->version = $t['version'];
        array_push($this->a_srv4pkgs, $k);
      }
    }
    return 0;
  }

  function addSRV4Pkg($k, $arch='', $version='') {

    $table = "`jt_srv4pkg_plevel`";
    $names = "`id_srv4pkg`, `arch`, `version`, `id_plevel`";
    $values = "'$k->id', '".$k->arch."', '".$k->version."', '".$this->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_srv4pkgs, $k);
    return 0;
  }

  function delSRV4Pkg($k) {

    $table = "`jt_srv4pkg_plevel`";
    $where = " WHERE `id_srv4pkg`='".$k->id."' AND `id_plevel`='".$this->id."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_srv4pkgs as $ak => $v) {
      if (!strcmp($k->name, $v->name)) {
        unset($this->a_srv4pkgs[$ak]);
	break;
      }
    }
    return 0;
  }

  function isSRV4Pkg($p) {
    foreach($this->a_srv4pkgs as $po)
      if (!strcmp($p->name, $po->name))
        return TRUE;
    return FALSE;
  }

  function fetchPatches($all=1) {
    $this->a_patches = array();
    $table = "`jt_patches_plevel`";
    $index = "`patch`, `rev`";
    $where = "WHERE `id_plevel`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['patch'], $t['rev']);
        if ($all) { 
          $k->fetchFromId();
	  if($all!=2) {
            $k->o_latest = Patch::pLatest($k->patch);
            if ($k->o_latest && $k->o_latest->patch == $k->patch && $k->o_latest->revision == $k->revision) $k->o_latest = false;
	  }
        }
        
        array_push($this->a_patches, $k);
      }
    }
    return 0;
  }

  function addPatch($k) {

    $table = "`jt_patches_plevel`";
    $names = "`patch`, `rev`, `id_plevel`";
    $values = "'$k->patch', '".$k->revision."', '".$this->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_patches, $k);
    return 0;
  }

  function delPatch($k) {

    $table = "`jt_patches_plevel`";
    $where = " WHERE `patch`='".$k->patch."' AND `rev`='".$k->revision."' AND `id_plevel`='".$this->id."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_patches as $ak => $v) {
      if ($k->patch == $v->patch && $k->revision == $v->revision) {
        unset($this->a_patches[$ak]);
	break;
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
 
  public function delete() {

    $this->fetchPatches(0);
    $this->fetchSRV4Pkgs(0);

    foreach($this->a_patches as $p) {
      $this->delPatch($p);
    }

    foreach($this->a_srv4pkgs as $p) {
      $this->delSRV4Pkg($p);
    }

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

  public function parsePCA($output = null, $pdiag = null) {

    if (!$output) {
      $output = $this->runPCA($pdiag);
    }

    $this->a_ppatches = array();
    $this->a_pcve = array();

    foreach($output as $line) {
      $line = trim($line);
      if (empty($line))
        continue;

      $tmp = explode(' ', $line, 7);
      if (count($tmp) != 7)
	continue;

      $patch = $tmp[0];
      $irev = $tmp[1];
      $crev = $tmp[3];

      $p = new Patch($patch, $crev);
      if (!$p->fetchFromId()) {
        $p->fetchCVE();
        $p->o_latest = Patch::pLatest($p->patch);
        if ($p->o_latest && $p->o_latest->patch == $p->patch && $p->o_latest->revision == $p->revision) $p->o_latest = false;
        if ($irev > 0) $p->o_current = new Patch($patch, $irev);
        $p->fetchUntil($p->o_current);

        if (count($p->a_cve)) {
          $this->cnt_cvep++; // This patch fix some CVEs
          foreach($p->a_cve as $cve) {
            $pp = new Patch($p->patch, $p->revision);
	    $pp->fetchFromId();
	    $pp->o_cve = $cve;
	    $this->a_pcvep[] = $pp;
	  }
	}
        foreach($p->a_previous as $pp) {
          $this->cnt_pcaa++;
	  $pp->fetchCVE();
	  foreach($pp->a_cve as $cve) {
	    $ppp = new Patch($pp->patch, $pp->revision);
	    $ppp->fetchFromId();
	    $ppp->o_cve = $cve;
	    $this->a_apcvep[] = $ppp;
	  }
        }
      }
      $p->u_irev = $irev;

      $this->a_ppatches[] = $p;
      $this->cnt_pcap++;
      if ($p->pca_sec) $this->cnt_pcas++;
      if ($p->pca_rec) $this->cnt_pcar++;
    }
  }

  public function runPCA($pdiag=null) {
    global $config;

    /* Check patchdiag.xref presence */
    if (!$pdiag) {
      $pdiag = Patchdiag::fetchLatest();
    }
    if (!file_exists($pdiag->getPath())) {
      return false;
    }

    /**
     * Fill 3 data file output required by PCA
     */
    $showrev_out = '';
    $pkginfo_out = '';
    $uname_out = 'SunOS HNAME 5.10 Generic_000000-00 cputype arch SUNW,Model';

    foreach($this->a_patches as $p) {
      $p->fetchFromId();
      $line = 'Patch: '.$p.' Obsoletes: ';
      $p->fetchObsolated(0);
      $i=0;
      foreach($p->a_obso as $po) {
	if ($i) {
	  $line .= ', ';
	}
        $line .= $po;
	$i++;
      }
      $line .= ' Requires: ';
      $p->fetchRequired(0);
      $i=0;
      foreach($p->a_depend as $po) {
        if ($i) {
          $line .= ', ';
        }
        $line .= $po;
        $i++;
      }
      $line .= ' Incompatibles: ';
      $p->fetchConflicts(0);
      $i=0;
      foreach($p->a_conflicts as $po) {
        if ($i) {
          $line .= ', ';
        }
        $line .= $po;
        $i++;
      }
      $line .= ' Packages: ';
      $i=0;
      foreach ($p->getPkgArray() as $pkg => $ver) {
	if (empty($pkg)) continue;
        if ($i) {
          $line .= ', ';
        }
        $line .= $pkg;
        $i++;
        if (!isset($pkga[$pkg])) {
          $pkgv[$pkg] = $ver;
          $pkga[$pkg] = $p->dia_arch;
        }
      }
      $showrev_out .= "$line\n";
    }

    foreach($this->a_srv4pkgs as $spkg) {
      $pkginfo_out .= "   PKGINST:  ".$spkg->name."\n";
      $pkginfo_out .= "      ARCH:  ".$spkg->arch."\n";
      $pkginfo_out .= "   VERSION:  ".$spkg->version."\n";
      $pkginfo_out .= "\n";
    }

    /* Create the temp directory */
    $tmpdir = make_temp_folder();
    @mkdir($tmpdir.'/patch+pkg');
    @mkdir($tmpdir.'/sysconfig');
    file_put_contents($tmpdir.'/sysconfig'.'/uname-a.out', $uname_out);
    file_put_contents($tmpdir.'/patch+pkg'.'/showrev-p.out', $showrev_out);
    file_put_contents($tmpdir.'/patch+pkg'.'/pkginfo-l.out', $pkginfo_out);

    /* Copy patchdiag.xref into this directory */
    copy($pdiag->getPath(), $tmpdir.'/patchdiag.xref');
    
    /* Execute PCA against this directory */

    $cmd = '/usr/bin/perl '.$config['rootpath'].'/bin/pca ';
    $cmd .= '--xrefdir='.$tmpdir.' ';				// Specify Xref path
    $cmd .= '-y ';						// Do not check xref for accuracy
    $cmd .= '--fromfiles='.$tmpdir.' ';				// Source data directory
    $cmd .= '-H ';						// No Headers
    $cmd .= '-l ';						// List only

    @exec($cmd, $rc);

    /* cleanup */
    @unlink($tmpdir.'/patch+pkg'.'/pkginfo-l.out');
    @unlink($tmpdir.'/patch+pkg'.'/showrev-p.out');
    @unlink($tmpdir.'/sysconfig'.'/uname-a.out');
    @unlink($tmpdir.'/patchdiag.xref');
    @rmdir($tmpdir.'/sysconfig');
    @rmdir($tmpdir.'/patch+pkg');
    @rmdir($tmpdir);

    return $rc;
  }

  public function buildFromFiles($showrev = array(), $pkginfo = array()) {
    
    if (empty($showrev) && empty($pkginfo)) {
      return $rc;
    }

    $this->fetchPatches();
    $this->fetchSRV4Pkgs();

    foreach($showrev as $line) {
      $line = trim($line);
      if (empty($line))
        continue;
      if(!preg_match("/^Patch:[\s]*[0-9]{6}-[0-9]{2}/", $line)) 
        continue;
      $f = preg_split("/[\s ]+/", $line);
      if (count($f) > 2) {
        $p = $f[1];
        $p = explode("-", $p);
        $patch = new Patch($p[0], $p[1]);
	if ($patch->fetchFromId()) { /* unknown one */
          $patch->insert();
	}
	if (!$this->isPatch($patch)) {
	  $this->addPatch($patch);
	}
      }

    }
    
    $pkgname = '';
    $spkg = null;
    foreach($pkginfo as $line) {
      $line = trim($line);
      if (empty($line))
        continue;
      
      $tmp = explode(':', $line, 2);
      if (count($tmp) != 2)
	continue;
      $n = trim($tmp[0]);
      $v = trim($tmp[1]);

      switch($n) {
        case 'PKGINST':
	  /* we are potentially looping,
	   * so if $spkg is not null, treat the previous
	   * package before doing this one...
	   */
	  if ($spkg) {
	    if (!$this->isSRV4Pkg($spkg)) {
	      $this->addSRV4Pkg($spkg, $spkg->arch, $spkg->version);
	    }
	    $spkg = null;
	  }
	  $pkgname = $v;
	  $spkg = new SRV4Pkg();
	  $spkg->name = $v;
	  if ($spkg->fetchFromField('name')) { /* unknown package, add it anyway, we don't care... */
	    $spkg->insert();
	  }
	  break;
	case 'NAME':
	  if (empty($spkg->description)) {
	    $spkg->description = $v;
	    $spkg->update();
	  }
	  break;
	case 'CATEGORY':
	  if (empty($spkg->category)) {
	    $spkg->category = $v;
	  }
	  break;
	case 'ARCH':
	  $spkg->arch = $v;
	  break;
	case 'VERSION':
	  $spkg->version = $v;
	  break;
	default:
	  break;
      }
    }
    return 0;
  }


 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "u_plevel";
    $this->_nfotable = NULL;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "id_server" => SQL_PROPE|SQL_EXIST,
                        "name" => SQL_PROPE,
                        "comment" => SQL_PROPE,
                        "is_current" => SQL_PROPE,
                        "is_applied" => SQL_PROPE,
                        "added" => SQL_PROPE,
                        "updated" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "id_server" => "id_server",
                        "name" => "name",
                        "comment" => "comment",
                        "is_current" => "is_current",
                        "is_applied" => "is_applied",
                        "added" => "added",
                        "updated" => "updated"
                 );
  }

}
?>
