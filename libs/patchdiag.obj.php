<?php
/**
 * Patchdiag object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Patchdiag extends mysqlObj
{
  public $id = -1;
  public $date = -1;
  public $filename = "";
  public $size = "";
  public $csum = -1;
  public $nb_patch = -1;
  public $added = -1;

  public static function parsePatchdiag($file=NULL, $force=false) {
    global $config, $stats;

    $oldone = true;
    if (!$file) {
      $oldone = false;
      $file = $config['tmppath']."/patchdiag.xref";
    }
    if ($force) $oldone = false;
    if (!file_exists($file)) {
      return -1;
    }
    $lines = file($file);
    $nb=0;
    $mod=0;
    foreach ($lines as $line) {
      $np = false;
      if (empty($line)) {
        continue;
      }
      if ($line[0] == "#") {
        continue;
      }
      $fields = explode("|", $line);
      if (count($fields) < 3) // invalid line...
        continue;
      $pid = $fields[0];
      $rev = $fields[1];
      $pca_rec = 0;
      $pca_sec = 0;
      $pca_bad = 0;
      $pca_obs = 0;
      $pca_y2k = 0;
      $dia_version = "";
      $dia_arch = "";
      $dia_pkgs = "";
      if (isset($fields[4]) && $fields[4] == "S") {
        $pca_sec = 1;
      }
      if (isset($fields[3]) && $fields[3] == "R") {
        $pca_rec = 1;
      }
      if (isset($fields[5]) && $fields[5] == "O") {
        $pca_obs = 1;
      }
      if (strlen($fields[5]) == 2) {
        if ($fields[5][1] == 'B') $pca_bad = 1;
        if ($fields[5][0] == 'Y') $pca_y2k = 1;
      } else if (strlen($fields[6]) == 2) {
        if ($fields[6][1] == 'B') $pca_bad = 1;
        if ($fields[6][0] == 'Y') $pca_y2k = 1;
      }
      if (isset($fields[7]) && strlen($fields[7])) {
        $dia_version = $fields[7];
      }
      if (isset($fields[8]) && strlen($fields[8])) {
        $dia_arch = $fields[8];
      }
      if (isset($fields[9]) && strlen($fields[9])) {
        $dia_pkgs = trim($fields[9]);
      }
      if (isset($fields[2])) {
        $r_date = $fields[2];
      }
      $synopsis = $fields[count($fields) - 1];
      $patch = new Patch($pid, $rev);
      $new = false;
      if ($patch->fetchFromId()) {
        $np = true;
        echo "   > New patch: ".$patch->name()."\n";
        $ip = new Ircnp();
        $ip->p = $patch->patch;
        $ip->r = $patch->revision;
        $new = true;
        if (!$oldone) Announce::getInstance()->nPatch($ip);
        $patch->insert();
        $nb++;
        if (!$oldone) Announce::getInstance()->msg(0, "[BATCH] New patch found in patchdiag.xref (".$patch->name().")");
      }
      if (!$oldone && ($patch->pca_rec != $pca_rec || $patch->pca_sec != $pca_sec || $patch->pca_bad != $pca_bad ||
                       $patch->pca_obs != $pca_obs || strcmp($patch->dia_version, $dia_version) ||
                       strcmp($patch->dia_arch, $dia_arch) || strcmp($patch->dia_pkgs, $dia_pkgs))) {
        $patch->pca_rec = $pca_rec;
        $patch->pca_sec = $pca_sec;
        $patch->pca_bad = $pca_bad;
        $patch->pca_obs = $pca_obs;
        if($pca_bad) {
          $patch->status = "WITHDRAWN";
        } else {
          if (strcmp($patch->status, 'OBSOLETE')) {
            $patch->status = "RELEASED";
          }
        }
        if (strcmp($patch->dia_version, $dia_version)) {
          $patch->dia_version = $dia_version;
          echo "   > Updated version: $dia_version\n";
        }
        if (strcmp($patch->dia_pkgs, $dia_pkgs)) {
          $patch->dia_pkgs = $dia_pkgs;
          echo "   > Updated pkgs: $dia_pkgs\n";
        }
        if (strcmp($patch->dia_arch, $dia_arch)) {
          $patch->dia_arch = $dia_arch;
          echo "   > Updated arch: $dia_arch\n";
        }
        if (!$new) $patch->to_update = 1;
        $patch->update();
        $mod++;
        echo "   > Updated PCA flags for ".$patch->name()."\n";
      }
      if (strlen($patch->synopsis) < 10 && strcmp($patch->synopsis, $synopsis)) {
        $patch->synopsis = $synopsis;
        if (!$new) $patch->to_update = 1;
        $patch->update();
        $mod++;
        echo "   > Updated synopsis for ".$patch->name()."\n";
      }
      $r_date = $patch->parseDate($r_date);
      if (!$patch->releasedate && $r_date) {
        $patch->releasedate = $r_date;
        if (!$new) $patch->to_update = 1;
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

  public function insert() {
    $this->added = time();
    parent::insert();
  }

  public function format() {
    global $config;

    $str = date($config['dateFormat'], $this->date)." | ".$this->nb_patch." patches | ".round($this->size / 1024 / 1024, 2)." MB";
    return $str;
  }

  public static function listFiles() {
    $table = "patchdiag";
    $index = "`id`";
    $list = array();
    $where = " ORDER BY `date` DESC";
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patchdiag($t['id']);
        $k->fetchFromId();
        array_push($list, $k);
      }
    }
    return $list;
  }

  public static function updateDB() {
    global $config;
    if (!is_dir($config['pdiagpath'])) {
      return false;
    }
    if ($h = opendir($config['pdiagpath'])) {
      while (false !== ($file = readdir($h))) {
        $fp = $config['pdiagpath']."/".$file;
        if (!is_dir($fp) && preg_match("/^patchdiag.xref-[0-9]{8}/", $file)) {
	  $pd = explode("-", $file);
	  $pdiag = new Patchdiag();
          $day = substr($pd[1], 0, 2);
          $month = substr($pd[1], 2, 2);
          $year = substr($pd[1], 4, 4);
          $pdiag->filename = $file;
	  $pdiag->date = mktime(0,0,0,$month, $day, $year);
          $pdiag->size = filesize($fp);
          if ($pdiag->fetchFromField("filename")) {
            $pdiag->insert();
            echo "[-] Found new patchdiag: $file\n";
	    // checksum calculation
	    // TODO
            // Count number of patches
            $cmd = "/bin/grep -cv '^#' $fp";
	    $o = exec($cmd, $out = array(), $ret);
	    $pdiag->nb_patch = $o;
	    $pdiag->update();
	  }
	}
      }
      return true;
    }
    return false;
  }

  public static function genFromArray($patches) {
    $ret = "";
    foreach($patches as $p) {
     if ($p->pca_obs) { /* Obsoleted */
       /* Should check if the patch that obsolete this one
	  is also present.. If not, remove obsoletion mention
          and if it is, don't touch anything...
	*/
       if (preg_match("/^Obsoleted by: /", $p->synopsis)) {
         $pp = explode(' ', $p->synopsis);
	 if (preg_match("/[0-9]{6}-[0-9]{2}/", $pp[2])) {
           $pp = explode('-', $pp[2]);
           $patch = new Patch();
           $patch->patch = $pp[0];
           $patch->revision = $pp[1];
	   if (isset($patches[$patch->patch])) {
             if ($patches[$patch->patch]->revision >= $patch->revision) {
	       $ret .= $p->printPdiag(false)."\n"; /* Let this obsoletion mention */
	       continue;
	     }
	   }
	 }
       }
     }
     $ret .= $p->printPdiag(true)."\n"; /* Remove the obsoletion mention as we don't have included the superseeding patch */
    }
    return $ret;
  }
 
  public static function cleanObsolated(&$patches) {
    foreach($patches as $p) {
     $p->fetchObsolated(0);
     foreach($p->a_obso as $o) {
       if (isset($patches[$o->patch])) {
         if ($o->revision >= $patches[$o->patch]->revision) {
	   /* Remove $o->patch from patchdiag */
	   unset($patches[$o->patch]);
	 }
       }
     }
    }
    return true;
  }

  public static function genFromPatches($patches, &$ret = array()) {
   foreach($patches as $p) {
     if ($p->fetchFromId()) {
       /* If patch is not found, try to find any release upper than this one... */
       $p = Patch::pUpperThan($p->patch, $p->revision);
       if (!$p || $p->fetchFromId()) {
	 continue; // skip this one @TODO raise a warning
       }
     }
     if (!$p->releasedate) { /* no releasedate means unresolved */
       $p = Patch::pUpperThan($p->patch, $p->revision);
       if (!$p || $p->fetchFromId()) {
	 continue; // skip this one @TODO raise a warning
       }
     }
     if ($p->pca_bad) { /* this is a bad patch */
       /* If patch is bad, try to find any release upper than this one... */
       $p = Patch::pUpperThan($p->patch, $p->revision);
       if ($p->fetchFromId()) {
	 continue; // skip this one @TODO raise a warning
       }
     }
     $p->fetchRequired(0);
     if (isset($ret[$p->patch])) {
       if ($ret[$p->patch]->revision < $p->revision) {
	 $ret[$p->patch] = $p;
       }
     } else {
       $ret[$p->patch] = $p;
     }
     Patchdiag::genFromPatches($p->a_depend, &$ret);
   }
   return $ret;
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "patchdiag";
    $this->_nfotable = NULL;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "date" => SQL_PROPE,
                        "filename" => SQL_PROPE,
                        "size" => SQL_PROPE,
                        "csum" => SQL_PROPE,
                        "nb_patch" => SQL_PROPE,
                        "added" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "date" => "date",
                        "filename" => "filename",
                        "size" => "size",
                        "csum" => "csum",
                        "added" => "added",
                        "nb_patch" => "nb_patch"
                 );
  }

}
?>
