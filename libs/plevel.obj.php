<?php
/**
 * Patch Level object
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

  function isConflict($p) {
    foreach($this->a_conflicts as $po)
      if ($p->patch == $po->patch && $p->revision == $po->revision)
        return TRUE;
    return FALSE;
  }
 

  public function update() {
   $this->updated = time();
   parent::update();
  }

  public function insert() {
    $this->added = time();
    parent::insert();
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

    $pkga = array();
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
    $pkginfo_out = '';
    foreach($pkgv as $pkg => $ver) {
      $arch = $pkga[$pkg];
      $pkginfo_out .= "   PKGINST:  ".$pkg."\n";
      $pkginfo_out .= "      ARCH:  ".$arch."\n";
      $pkginfo_out .= "   VERSION:  ".$ver."\n";
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
