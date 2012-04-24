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

  public $a_lines = array();
  public $a_pp = array();
  public $a_raw = array();

  public static function fetchLatest() {

    $index = "`id`";
    $table = "`patchdiag`";
    $where = " ORDER BY `date` DESC LIMIT 0,1";
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (isset($idx[0]) && isset($idx[0]['id'])) {
        $ppd = new Patchdiag($idx[0]['id']);
        $ppd->fetchFromId();
        return $ppd;
      }
    }
    return null;
  }

  public function bEvent($pid, $rev) {
    $tEvent = new pTimeline();
    $tEvent->id_patchdiag = $this->id;
    $tEvent->id_patch = $pid;
    $tEvent->when = $this->date;
    $tEvent->id_revision = $rev;
    return $tEvent;
  }

  public function diffPrevious2() {
    global $config;

    /* Find previous patchdiag in database */
    $pPd = null;
    $index = "`id`";
    $table = "`patchdiag`";
    $where = " WHERE `date`<".$this->date." ORDER BY `date` DESC LIMIT 0,1";
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (isset($idx[0]) && isset($idx[0]['id'])) {
        $ppd = new Patchdiag($idx[0]['id']);
        $ppd->fetchFromId();
      }
    }

    /* Load both patchdiag from files */
    $this->loadFromFile(true);
    $ppd->loadFromFile(true);

    /**
     * build an array with only patches which aren't common to
     * both new and old arrays
     */
    $nr = array_keys($this->a_raw);
    $or = array_keys($ppd->a_raw);
    $fr = array();
    foreach($nr as $n) { if (!isset($ppd->a_raw[$n])) $fr[$n] = $n; }
    foreach($or as $o) { if (!isset($this->a_raw[$o])) $fr[$o] = $o; }

    /**
     * loop through new array:
     *  - if present inside the $fr array, this is a new patch
     *    so we must check if it obsolete or replace an old patch
     *  - if not, check for status differences (rec,sec,obs,bad,...)
     */
    foreach($nr as $p) {
      if (isset($fr[$p])) { /* New patch */
        $po = Patch::fromString($p);
	if ($po->fetchFromId()) {
	  // ERROR, we don't have this patch... (yet?)
	  echo "[!] Patch not found in DB: $p\n";
	  continue;
	}
        echo "[-] New patch found: $p\n";
	$op = $this->findObsolete($p);
        if ($op) { /* $op is being obsoleted by $p */
	  $tEvent = $this->bEvent($po->patch, $po->revision);
          $tEvent->f_obs = 1;
	  $tEvent->what = $op;
          $tEvent->insert();
	  echo $tEvent->tell()."\n";
	  /* Remove $op and $p from the diff list */
	  unset($fr[$op]);
	  unset($fr[$p]);
	  continue; // go on with next patch
	}

        /**
         * check if previous release 
         * of this patch were present
	 * inside the old patchdiag
         */
        if (isset($ppd->a_pp[$po->patch]) && count($ppd->a_pp[$po->patch])) {
	  $lr = max(array_keys($ppd->a_pp[$po->patch]));
	  $op = new Patch($po->patch, $lr);
	  if ($op->fetchFromId()) {
	    // ERROR: Previous revision of this patch is not found...
	    echo "[!] Patch ".$op->name()." is not found but present in the past patchdiag...\n";
	    continue;
	  }
	  /* We found a previous release,
	   * Check if it is obsoleted, if yes, this is an obsoletion, if not, just a replacement..
 	   */
 	  $tEvent = $this->bEvent($po->patch, $po->revision);
	  $tEvent->what = $op->name();
	  $l = '/Obsoleted by: '.$po->name().'/';
	  if (preg_match($l, $op->synopsis)) {
	    $tEvent->f_obs = 1;
	  } else {
	    $tEvent->f_replace = 1;
	  }
	  $tEvent->insert();
	  echo $tEvent->tell()."\n";
	  /* remove $p and $op from the diff list */
          unset($fr[$op->name()]);
          unset($fr[$p]);
          continue; // go on with next patch
	}

	/**
         * this is just a new patch...
         */
        $tEvent = $this->bEvent($po->patch, $po->revision);
        $tEvent->f_added = 1;
        $tEvent->insert();
        echo $tEvent->tell()."\n";

      } else { /* could just be a flag change */
	/* Parse both version of patchdiag patches */
        $op = Patchdiag::patchFromString($ppd->a_raw[$p]);
        $np = Patchdiag::patchFromString($this->a_raw[$p]);
	if (!$op || !$np) {
	  echo "[!] ERROR: Cannot parse either new or old patchdiag line\n";
	  continue;
	}
	if ($op->pca_rec && !$np->pca_rec) {
	  $tEvent = $this->bEvent($np->patch, $np->revision);
	  $tEvent->f_rec = -1; /* Not recommended anymore */
	  $tEvent->insert();
	  echo $tEvent->tell()."\n";
	}
	if ($op->pca_obs && !$np->pca_obs) {
	  $tEvent = $this->bEvent($np->patch, $np->revision);
	  $tEvent->f_obs = -1; /* Not obsolete anymore */
	  $tEvent->insert();
	  echo $tEvent->tell()."\n";
	}
	if ($op->pca_bad && !$np->pca_bad) {
	  $tEvent = $this->bEvent($np->patch, $np->revision);
	  $tEvent->f_bad = -1; /* Not BAD anymore */
	  $tEvent->insert();
	  echo $tEvent->tell()."\n";
	}
	if ($op->pca_y2k && !$np->pca_y2k) {
	  $tEvent = $this->bEvent($np->patch, $np->revision);
	  $tEvent->f_y2k = -1; /* Not Y2K anymore */
	  $tEvent->insert();
	  echo $tEvent->tell()."\n";
	}
	if (!$op->pca_rec && $np->pca_rec) {
	  $tEvent = $this->bEvent($np->patch, $np->revision);
	  $tEvent->f_rec = 1; /* Not recommended anymore */
	  $tEvent->insert();
	  echo $tEvent->tell()."\n";
	}
	if (!$op->pca_obs && $np->pca_obs) {
	  $tEvent = $this->bEvent($np->patch, $np->revision);
	  $tEvent->f_obs = 1; /* Not obsolete anymore */
	  $tEvent->insert();
	  echo $tEvent->tell()."\n";
	}
	if (!$op->pca_bad && $np->pca_bad) {
	  $tEvent = $this->bEvent($np->patch, $np->revision);
	  $tEvent->f_bad = 1; /* Not BAD anymore */
	  $tEvent->insert();
	  echo $tEvent->tell()."\n";
	}
	if (!$op->pca_y2k && $np->pca_y2k) {
	  $tEvent = $this->bEvent($np->patch, $np->revision);
	  $tEvent->f_y2k = 1; /* Not Y2K anymore */
	  $tEvent->insert();
	  echo $tEvent->tell()."\n";
	}
      }
    }
    

  }

  /**
   * search if there is a patch which
   * is obsoleted by the patch in argument
   */
  public function findObsolete($p) {
    $l = '/Obsoleted by: '.$p.'/';
    foreach($this->a_raw as $op => $line) {
      if (preg_match($l, $line)) {
        return $op;
      }
    }
    return null;
  }

  public function diffPrevious() {
    global $config;

    /* Find previous patchdiag in database */
    $pPd = null;
    $index = "`id`";
    $table = "`patchdiag`";
    $where = " WHERE `date`<".$this->date." ORDER BY `date` DESC LIMIT 0,1";
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (isset($idx[0]) && isset($idx[0]['id'])) {
        $ppd = new Patchdiag($idx[0]['id']);
        $ppd->fetchFromId();
      }
    }

    /* Load both patchdiag from files */
    $this->loadFromFile(true);
    $ppd->loadFromFile(true);

    foreach($ppd->a_pp as $pid => $revs) {
      if (!isset($this->a_pp[$pid])) {
        foreach($revs as $rev => $line) {
	  echo "[-] $pid-$rev\n";
 	  $tEvent = $this->bEvent($pid, $rev);
	  $tEvent->f_removed = 1;
	  $tEvent->insert();
	}
      }
    }

    /* @TODO: Check here if the patch which is replaced is also obsoleted by current patch */
    foreach($this->a_pp as $pid => $revs) {
      /* Check if patchid is present in the previous patchdiag.xref */
      if (!isset($ppd->a_pp[$pid])) {
	/* Has been added... */
        foreach($revs as $rev => $line) {
	  echo "[+] $pid-$rev\n";
 	  $tEvent = $this->bEvent($pid, $rev);
	  $tEvent->f_added = 1;
	  $tEvent->insert();
        }
      } else {
	/* let's now check for specific revisions */
	foreach($revs as $rev => $line) {
	  /* Check if this revision was already there in the previous file */
	  if (!isset($ppd->a_pp[$pid][$rev])) {
	    if (count($ppd->a_pp[$pid])) { /* there is some revision of this patch */
	      $pr = $ppd->getHighest($pid);
	      echo "[+] $pid-$rev (replace $pid-$pr)\n";
 	      $tEvent = $this->bEvent($pid, $rev);
	      $tEvent->f_replace = 1;
	      $tEvent->what = "$pid-$pr";
	      $tEvent->insert();
	    }
	  } else {
	    /* Revision of this patch is in both file
	     * We should now check description, flags and so on to
	     * check if something was updated...
	     */
	    $lineOld = $ppd->a_pp[$pid][$rev];
	    $lineNew = $this->a_pp[$pid][$rev];
	    $fOld = explode('|', $lineOld);
	    $fNew = explode('|', $lineNew);
	    if ($fOld[3] != $fNew[3]) { /* R flag */
	      if ($fOld[3] == "R") {
		echo "[!] $pid-$rev is no longer Recommended\n";
 	        $tEvent = $this->bEvent($pid, $rev);
	        $tEvent->f_rec = -1;
	        $tEvent->insert();
	      } else if ($lineNew[3] == "R") {
		echo "[!] $pid-$rev is now Recommended\n";
 	        $tEvent = $this->bEvent($pid, $rev);
	        $tEvent->f_rec = 1;
	        $tEvent->insert();
	      }
	    }
            if ($fOld[4] != $fNew[4]) { /* S flag */
              if ($fOld[4] == "S") {
                echo "[!] $pid-$rev is no longer Security\n";
 	        $tEvent = $this->bEvent($pid, $rev);
	        $tEvent->f_sec = -1;
	        $tEvent->insert();
              } else if ($fNew[4] == "S") {
                echo "[!] $pid-$rev is now Security\n";
 	        $tEvent = $this->bEvent($pid, $rev);
	        $tEvent->f_sec = 1;
	        $tEvent->insert();
              }
            }
            if ($fOld[5] != $fNew[5]) { /* O flag */
              if ($fOld[5] == "O") {
                echo "[!] $pid-$rev is no longer Obsoleted\n";
 	        $tEvent = $this->bEvent($pid, $rev);
	        $tEvent->f_obs = -1;
	        $tEvent->insert();
              } else if ($fNew[5] == "O") {
 		$syn = $fNew[count($fNew)-1];
 	        $tEvent = $this->bEvent($pid, $rev);
	        $tEvent->f_obs = 1;
   		if (preg_match('/Obsoleted by: ([0-9]{6}-[0-9]{2})/', $syn, $match)) {
                  echo "[!] $pid-$rev is now Obsoleted by ".$match[1]."\n";
                  $tEvent->what = $match[1];
	        } else {
                  echo "[!] $pid-$rev is now Obsolete\n";
	        }
	        $tEvent->insert();
              }
            }
	    if ($fOld[6] != $fNew[6]) { /* YB flag */
              if ($fOld[6][0] == 'Y' && $fNew[6][0] != 'Y') {
                echo "[!] $pid-$rev is no longer Y2K Patch\n";
                $tEvent = $this->bEvent($pid, $rev);
                $tEvent->f_y2k = -1;
                $tEvent->insert();
              } else if ($fNew[6][0] == 'Y' && $fOld[6][0] != 'Y') {
                echo "[!] $pid-$rev is now Y2K patch\n";
                $tEvent = $this->bEvent($pid, $rev);
                $tEvent->f_y2k = 1;
                $tEvent->insert();
              }
              if ($fOld[6][0] == 'B' && $fNew[6][0] != 'B') {
                echo "[!] $pid-$rev is no longer BAD Patch\n";
                $tEvent = $this->bEvent($pid, $rev);
                $tEvent->f_bad = -1;
                $tEvent->insert();
              } else if ($fNew[6] == 'B' && $fOld[6][0] != 'B') {
                echo "[!] $pid-$rev is now BAD patch\n";
                $tEvent = $this->bEvent($pid, $rev);
                $tEvent->f_bad = 1;
                $tEvent->insert();
              }
            }
	  }
	}
      }
    }
  }

  public function getHighest($pid) {
    if (isset($this->a_pp[$pid])) {
      $max = 0;
      foreach($this->a_pp[$pid] as $rev => $line) {
        if ($max < $rev) $max = $rev;
      }
      return sprintf("%02d", $max);
    }
    return "00";
  }

  public function getPath() {
    global $config;
    return $config['pdiagpath'].'/'.$this->filename;
  }

  public function loadFromFile($treat = false) {
    global $config;
    $this->a_lines = file($config['pdiagpath'].'/'.$this->filename);
    if ($treat) {
      $this->a_pp = array();
      foreach($this->a_lines as $line) {
        $line = trim($line);
        if (empty($line)) {
          continue;
        }
        if ($line[0] == '#' || $line[0] == '<') {
          continue;
        }
        $fields = explode("|", $line);
        if (count($fields) < 3)  /* skip bad lines */
	  continue;
        if (!isset($this->a_pp[$fields[0]])) {
          $this->a_pp[$fields[0]] = array();
	}
	if (!isset($this->a_pp[$fields[0]][$fields[1]])) {
          $this->a_pp[$fields[0]][$fields[1]] = $line;
	}
	$this->a_raw[$fields[0].'-'.$fields[1]] = $line;
      }
    }
    return true;
  }

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
    $a_lines = file($file);
    $nb=0;
    $mod=0;
    foreach ($a_lines as $line) {
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
        IrcMsg::void();
        if (!$oldone) Announce::getInstance()->msg(0, "[BATCH] New patch found in patchdiag.xref (".$patch->name().")", MSG_ADM);
      }
      if (!$oldone && ($patch->pca_rec != $pca_rec || $patch->pca_sec != $pca_sec || $patch->pca_bad != $pca_bad ||
                       $patch->pca_obs != $pca_obs || strcmp($patch->dia_version, $dia_version) ||
                       strcmp($patch->dia_arch, $dia_arch) || strcmp($patch->dia_pkgs, $dia_pkgs)) ||
		       (!strcmp($patch->status, 'OBSOLETE') && $pca_obs == 0)) {
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
 	if (!strcmp($patch->status, 'OBSOLETE') && $pca_obs == 0) {
	  $patch->status = 'RELEASED';
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
      IrcMsg::void();
      Announce::getInstance()->msg(0, "[BATCH] Updated patchdiag.xref (size: ".filesize($out).")", MSG_ADM);
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

  public static function patchFromString($line) {

    $fields = explode("|", $line);
    if (count($fields) < 3) // invalid line...
      return null;

    $pid = $fields[0];
    $rev = $fields[1];
    $patch = new Patch($pid, $rev);

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
    $patch->dia_version = $dia_version;
    $patch->dia_pkgs = $dia_pkgs;
    $patch->dia_arch = $dia_arch;
    $patch->synopsis = $synopsis;
    $r_date = $patch->parseDate($r_date);
    $patch->releasedate = $r_date;
    return $patch;
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
