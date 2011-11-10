<?php
/**
 * OSRelease object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class OSRelease extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $major = "";
  public $u_date = "";
  public $u_number = -1;

  public $a_files = array();

  /* Files */
  function fetchFiles($all=1) {

    $this->a_files = array();
    $table = "`jt_osrelease_files`";
    $index = "`fileid`, `size`, `pkg`, `md5`, `sha1`";
    $where = "WHERE `id_release`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new File($t['fileid']);
        $k->fetchFromId();
        $k->size = $t['size'];
        $k->md5 = $t['md5'];
        $k->pkg = $t['pkg'];
        $k->sha1 = $t['sha1'];
        array_push($this->a_files, $k);
      }
    }
    return 0;
  }

  function addFile($k) {

    $table = "`jt_osrelease_files`";
    $names = "`fileid`, `id_release`";
    $values = "'$k->id', '".$this->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_files, $k);
    return 0;
  }

  function setFileAttr($k, $size = 0, $md5 = "", $sha1 = "", $pkg = "") {

    $file = null;
    foreach ($this->a_files as $ak => $v) {
      if (!strcmp($k, $v->name)) {
        $file = $v;
        break;
      }
    }
    if (!$file)
      return -1;

    $file->pkg = $pkg;
    $file->md5 = $md5;
    $file->sha1 = $sha1;
    $file->size = $size;

    $table = "jt_osrelease_files";
    $set = "`size`='".$file->size."', `pkg`='".$file->pkg."', `md5`='".$file->md5."', `sha1`='".$file->sha1."'";
    $where = " WHERE `fileid`='".$file->id."' AND `id_release`='".$this->id."'";

    if (mysqlCM::getInstance()->update($table, $set, $where)) {
      return -1;
    }
    return 0;
  }

  function delFile($k) {

    $table = "`jt_osrelease_files`";
    $where = " WHERE `fileid`='".$k->id."' AND `id_release`='".$this->id."'";

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

 
  public static function checksum($rdir) {
    global $config;

    if (!is_dir($rdir))
      return -1;  

    $rfiles10 = $rdir."/Solaris_10/Product/SUNWsolnm/reloc/etc/release";
    $rfiles9 = $rdir."/Solaris_9/Product/SUNWsolnm/reloc/etc/release";
    $rfiles8 = $rdir."/Solaris_8/Product/SUNWsolnm/reloc/etc/release";

    if (!file_exists($rfile10)) {
      if (!file_exists($rfiles9)) {
        if (!file_exists($rfiles8)) {
	  return -1;
	} else {
	  $rfile = $rfiles8;
	}
      } else {
        $rfile = $rfiles9;
      }
    } else {
      $rfile = $rfiles10;
    }

    $release = file($rfile);
    $release = trim($release[0]); // first line
    
    $f = explode(" ", $release);
    $major = $f[1];
    $r_date = $f[2];

    $osr = new OSRelease();
    $osr->major = $major;
    $osr->u_date = $r_date;
    if ($osr->fetchFromFields(array("major", "u_date"))) {
      echo "[-] Unknown release, inserting $major / $r_date solaris release\n";
      $osr->insert();
    }

    $osr->fetchFiles();

    foreach (glob($rdir."/Solaris_10/Product/*", GLOB_ONLYDIR) as $pkg) {
      if (!is_dir($pkg))
        continue;

      if (!file_exists($pkg."/pkgmap"))
        continue;

      $pkgname = explode("/", $pkg);
      $pkgname = $pkgname[count($pkgname)-1];
      echo "[>] Found $pkgname:\n";

      /* is there an archive/none.bz2 file ? */
      $op = null;
      if (file_exists($pkg."/archive/none.bz2")) {
        $op = $config['tmppath']."/cksum/$pkgname";
        if (!is_dir($op)) mkdir($op);
	$cmd = "cd $op ; /bin/bzip2 -dc $pkg/archive/none.bz2 | cpio -id --no-preserve-owner --quiet";
        echo "[-] Extracting none.bz2 for $pkgname...\n";
	$r = `$cmd`;
      }

      /* Find files modified by this package */
      $pkgmap = file($pkg."/pkgmap");
      foreach($pkgmap as $line) {
        $line = trim($line);

        if (empty($line))
          continue;

        if($line[0] == '#')
          continue;

        $fields = explode(" ", $line);
        if ($fields[1] != 'f')
          continue;

        $fpath = $pkg."/reloc/".$fields[3];
        $fpnone = $op."/".$fields[3];
        $fname = "/".$fields[3];

        /* Check that the file do exist inside the reloc/ dir */
        if (!file_exists($fpath) && !file_exists($fpnone)) {
          echo "[!] $fpath does not exist\n";
          continue;
        }
        if (!file_exists($fpath) && file_exists($fpnone)) {
          $fpath = $fpnone;
	}

        /* Check that the file is already linked to this patch... */
        if (!$osr->isFile($fname)) {
          $file = new File();
          $file->name = $fname;
          if ($file->fetchFromField("name")) {
            $file->insert();
            echo "\t* ".$file->name." added to db\n";
          }
	  $osr->addFile($file);
          echo "\t* linking $fname to this release\n";
        }

        $size = filesize($fpath);
        $h_md5 = md5_file($fpath);
        $h_pkg = $pkgname;
        $h_sha1 = sha1_file($fpath);

        $osr->setFileAttr($fname, $size, $h_md5, $h_sha1, $h_pkg);
        echo "[>] Updated $fname with:\n";
        echo "\t> size: $size\n";
        echo "\t> h_md5: $h_md5\n";
        echo "\t> h_sha1: $h_sha1\n";
        echo "\t> pkg: $h_pkg\n";
      }
      if (isset($op) && $op && is_dir($op)) {
        echo "[-] Cleaning up none dir for $pkgname\n";
        $cmd = "rm -rf \"$op\"";
        $r = `$cmd`;
      }
    }

    return 0;
  }

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "osrelease";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "major" => SQL_PROPE,
                        "u_date" => SQL_PROPE,
                        "u_number" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "major" => "major",
                        "u_date" => "u_date",
                        "u_number" => "u_number"
                 );
  }

}
?>
