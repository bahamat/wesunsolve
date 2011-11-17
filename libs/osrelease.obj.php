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

@require_once($config['rootpath']."/libs/functions.lib.php");

class OSRelease extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $arch = "";
  public $major = "";
  public $update = "";
  public $ustring = "";
  public $dstring = "";

  public $a_files = array();

  public function countFiles() {
    $table = "`jt_osrelease_files`";
    $index = "count(`fileid`) as c";
    $where = "WHERE `id_release`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (isset($idx[0]) && isset($idx[0]['c'])) {
        return $idx[0]['c'];
      }
    }
    return 0;
  }

  public function countPackages() {
    $table = "`jt_osrelease_files`";
    $index = "count(distinct `pkg`) as c";
    $where = "WHERE `id_release`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (isset($idx[0]) && isset($idx[0]['c'])) {
        return $idx[0]['c'];
      }
    }
    return 0;

  }

  public function countSize() {
    $table = "`jt_osrelease_files`";
    $index = "round(sum(`size` / 1024 / 1024)) as c";
    $where = "WHERE `id_release`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (isset($idx[0]) && isset($idx[0]['c'])) {
        return $idx[0]['c'];
      }
    }
    return 0;
  }


  /* Files */
  public function fetchFiles($all=1) {

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

  function addFile($k, $silent=0) {

    $table = "`jt_osrelease_files`";
    $names = "`fileid`, `id_release`";
    $values = "'$k->id', '".$this->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    if (!$silent) array_push($this->a_files, $k);
    return 0;
  }

  function setFileAttr($k, $size = 0, $md5 = "", $sha1 = "", $pkg = "", $silent = 0) {

    $file = null;

    if ($silent) {
      $file = new File();
      $file->name = $k;
      if ($file->fetchFromField("name")) {
        return -1;
      }
    } else {
      foreach ($this->a_files as $ak => $v) {
        if (!strcmp($k, $v->name)) {
          $file = $v;
          break;
        }
      }
      if (!$file)
        return -1;
    }

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

  public static function extract($dir) {
    global $config;

    $pdirs = array(
			"Solaris_10",
			"Solaris_9"
		  );

    foreach($pdirs as $pdir) {
      $d = $dir.'/'.$pdir;
      if (is_dir($d)) {
        echo "[-] Found $d as release directory\n";
        if (!is_dir($d.'/Product')) {
          echo "[!] Product not found, skipping this dir\n";
	  continue;
	}
        foreach (glob($d.'/Product/*', GLOB_ONLYDIR) as $pkg) {
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
            $op = $pkg."/reloc";
            if (!is_dir($op)) mkdir($op);
            chdirmod($op, 0755);
            $cmd = "cd $op ; /bin/bzip2 -dc $pkg/archive/none.bz2 | /bin/cpio -id --no-preserve-owner --quiet";
            echo "\t* Extracting none.bz2 for $pkgname...\n";
            $r = `$cmd`;
          }

          /* is there an archive/none.7z file ? */
          if (file_exists($pkg."/archive/none.7z")) {
            $op = $pkg."/reloc";
            if (!is_dir($op)) mkdir($op);
            chdirmod($op, 0755);
            $cmd = "cd $op ; /usr/bin/7z x $pkg/archive/none.7z -so -bd -y 2>/dev/null | /bin/cpio -id --no-preserve-owner --quiet";
            echo "\t* Extracting none.7z for $pkgname...\n";
            $r = `$cmd`;
          }

        }
      }
    }
    return 0;
  }

  public static function checksum($dir, $osr) {
    global $config;

    if (!is_dir($dir))
      return -1;

    if (!$osr)
      return -1;

    $pdirs = array(
                        "Solaris_10",
                        "Solaris_9"
                  );

    foreach($pdirs as $pdir) {
      $d = $dir.'/'.$pdir;
      if (is_dir($d)) {
        echo "[-] Found $d as release directory\n";
        if (!is_dir($d.'/Product')) {
          echo "[!] Product not found, skipping this dir\n";
          continue;
        }
        foreach (glob($d.'/Product/*', GLOB_ONLYDIR) as $pkg) {
          if (!is_dir($pkg))
            continue;

          if (!file_exists($pkg."/pkgmap"))
            continue;

          $pkgname = explode("/", $pkg);
          $pkgname = $pkgname[count($pkgname)-1];
          echo "[>] Found $pkgname:\n";

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
            $fname = "/".$fields[3];
    
            /* Check that the file do exist inside the reloc/ dir */
            if (!file_exists($fpath)) {
              echo "[!] $fpath does not exist\n";
              continue;
            }

            /* It's faster to link the file to the release anyway! */
            $file = new File();
            $file->name = $fname;
            if ($file->fetchFromField("name")) {
              $file->insert();
              echo "\t* ".$file->name." added to db\n";
            }
            $osr->addFile($file, 1); // silent add

            $size = filesize($fpath);
            $h_md5 = md5_file($fpath);
            $h_pkg = $pkgname;
            $h_sha1 = sha1_file($fpath);

            $osr->setFileAttr($fname, $size, $h_md5, $h_sha1, $h_pkg, 1); // silent update
            echo "[>] Updated $fname with:\n";
            echo "\t> size: $size\n";
            echo "\t> h_md5: $h_md5\n";
            echo "\t> h_sha1: $h_sha1\n";
            echo "\t> pkg: $h_pkg\n";
          }
        }
      }
    }
  }
 
  public static function checksum_OLD($rdir) {
    global $config;

    if (!is_dir($rdir))
      return -1;  

    $rfiles10 = $rdir."/Solaris_10/Product/SUNWsolnm/reloc/etc/release";
    $rfiles9 = $rdir."/Solaris_9/Product/SUNWsolnm/reloc/etc/release";
    $rfiles8 = $rdir."/Solaris_8/Product/SUNWsolnm/reloc/etc/release";
    $rfiles8_2 = $rdir."/Trusted_Solaris_8/Product/SUNWsolnm/reloc/etc/release";

    $trusted = false;
    if (!file_exists($rfiles10)) {
      if (!file_exists($rfiles9)) {
        if (!file_exists($rfiles8)) {
	  if (!file_exists($rfiles8_2)) {
	    return -1;
          } else {
	    $rfile = $rfiles8_2;
	    $trusted = true;
  	  }
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
    if ($major == 8) {
      $r_date = $f[3];
    } else {
      $r_date = $f[2];
    }

    $arch = null;
    switch($f[count($f)-1]) {
      case "SPARC":
	$arch = "sparc";
	break;
      case "X86":
	$arch = "x86";
	break;
      default:
	$arch = "sparc";
	break;
    }
    if (!$arch) $arch = "sparc"; // default to sparc

    $osr = new OSRelease();
    $osr->major = $major;
    $osr->dstring = $r_date;
    $osr->arch = $arch;
    if ($osr->fetchFromFields(array("arch", "major", "dstring"))) {
      echo "[-] Unknown release, inserting $arch / $major / $r_date solaris release\n";
      $osr->insert();
    }

    $osr->fetchFiles();

    if ($trusted) {
      $pkgpath = $rdir."/Trusted_Solaris_".$osr->major."/Product/*";
    } else {
      $pkgpath = $rdir."/Solaris_".$osr->major."/Product/*";
    }

    foreach (glob($pkgpath, GLOB_ONLYDIR) as $pkg) {
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
	$cmd = "cd $op ; /bin/bzip2 -dc $pkg/archive/none.bz2 | /bin/cpio -id --no-preserve-owner --quiet";
        echo "[-] Extracting none.bz2 for $pkgname...\n";
	$r = `$cmd`;
      }

      /* is there an archive/none.7z file ? */
      if (file_exists($pkg."/archive/none.7z")) {
        $op = $config['tmppath']."/cksum/$pkgname";
        if (!is_dir($op)) mkdir($op);
	$cmd = "cd $op ; /usr/bin/7z x $pkg/archive/none.7z -so -bd -y 2>/dev/null | /bin/cpio -id --no-preserve-owner --quiet";
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
      unset($file);
      $file = null;
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
                        "arch" => SQL_PROPE,
                        "major" => SQL_PROPE,
                        "update" => SQL_PROPE,
                        "ustring" => SQL_PROPE,
                        "dstring" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "arch" => "arch",
                        "major" => "major",
                        "update" => "update",
                        "ustring" => "ustring",
                        "dstring" => "dstring"
                 );
  }

}
?>
