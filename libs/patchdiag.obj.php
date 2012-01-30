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
     $ret .= $p->printPdiag()."\n";
    }
    return $ret;
  }

  public static function genFromPatches($patches, &$ret = array()) {
   foreach($patches as $p) {
     if ($p->fetchFromId()) {
       /* If patch is not found, try to find any release upper than this one... */
       $p = Patch::pUpperThan($p->patch, $p->revision);
       if ($p->fetchFromId()) {
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
