<?php
/**
 * IPS object
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

class IPS extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $root = "";

  public $a_pkgs = array();

  public function __toString() {
  }

  public function md5Sum($file) {

    $fp = $this->root.'/file/'.substr($file->sha1, 0, 2).'/'.$file->sha1;

    if (!file_exists($fp)) {
      return -1;
    }
    $cmd = "/bin/gzip -dc $fp | /usr/bin/sha1sum - | /usr/bin/cut -f 1 -d' '";
    $ret = `$cmd`;
    return trim($ret);
  }

  public function browse($pkg = "") {
    global $config;

    if (!is_dir($this->root))
      return -1;

    $d_pkg = $this->root.'/pkg';

    /* Browse packages and update db info on them */
    if (!is_dir($d_pkg)) {
      echo " > pkg/ subdir not found inside IPS repo\n";
      return -1;
    }
    $filter = "";
    if (!empty($pkg)) {
      $filter = $d_pkg.'/*'.$pkg.'*';
    } else {
      $filter = $d_pkg.'/*';
    }

    foreach (glob($filter, GLOB_ONLYDIR) as $pkg) {
      foreach(glob($pkg.'/*') as $pstamp) {
        $content = file_get_contents($pstamp);
        $po = new Pkg();
        $po->o_ips = $this;
	$po->parseIPS($content);
        echo "[-] Found package $po\n";
      }
    }
  }

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "ips";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "root" => SQL_PROPE,
                        "updated" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "root" => "root",
                        "updated" => "updated"
                 );
  }

}
?>
