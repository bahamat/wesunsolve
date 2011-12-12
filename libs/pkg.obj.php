<?php
/**
 * Pkg object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */

class IPSToken
{
  private $cb = null;
  private $keyword = "";

  public function key() { return $this->keyword; }

  public function call(&$pkg, $line) {
    return $this->{$this->cb}($pkg, $line);
  }

  public function __construct($keyword, $cb) {

    if (!method_exists("IPSToken", $cb)) {
      die("Unknown method IPSToken::$cb\n");
    }
    $this->cb = $cb;
    $this->keyword = $keyword;
  }

  public function t_file(&$pkg, $line) {

    //file 7a811afac8012ac87ef28aefe84dee0daa357d27 chash=9219214553f3cb56cae81c613268f76a7fa7be9c elfarch=i386 elfbits=64 elfhash=8fccdb24422f18fc87192d3313b52cd521bc10ea group=bin mode=0555 owner=root path=usr/bin/xmag_multivis pkg.csize=16564 pkg.size=46560 variant.arch=i386
    if (!$pkg || !$pkg->id || $pkg->id == -1) {
      return -1;
    }
    $pkg->fetchFiles();

    if (!($p = strpos($line, ' '))) {
      return -1;
    }
    $hash = substr($line, 0, $p);
    $nline = substr($line, $p + 1);
    $file = null;

    $vars = IPSToken::parseStringVars($nline);
    foreach($vars as $var) {
      foreach($var as $k => $v) {
        if (!strcmp($k, 'path')) {
          $file = new File();
	  $file->name = '/'.$v;
          $file->sha1 = $hash;
	  if ($file->fetchFromField("name")) {
	    $file->insert();
	    echo "   |---> Added file $v to DB\n";
	  }
          if (empty($file->md5) || $file->md5 == -1) {
	    $file->md5 = $pkg->o_ips->md5Sum($file);
	    echo "   |---> Updated md5 sum to be ".$file->md5."\n";
	  }

	  if ($pkg->id && $pkg->id != -1) {
            if (!$pkg->isFile($file)) {
	      $pkg->addFile($file);
	      echo "  |---> linked $v to $pkg\n";
	    }
	  }
	} else if (!strcmp($k, 'pkg.size')) {
          if ($file) {
	    $file->size = $v;
	  }
        }
	$pkg->setFileAttr($file);
      }
    }
    return $vars;
  }

  public function t_set(&$pkg, $line) {

    $vars = IPSToken::parseStringVars($line);
    
    /* find the name of the set action */
    $name = "";
    $values = array();
    foreach ($vars as $var) {
      foreach($var as $k => $v) {
        if (!strcmp($k, "name")) {
          $name = $v;
	  continue;
	}
        if (!strcmp($k, "value")) {
          $values[] = $v;
	  continue;
	}
      }
    }

    switch($name) {
      case "pkg.fmri";
	$value = $values[0]; // only one
        $value = explode('/', $value);
        $pkgstring = $value[count($value)-1]; // BRCMbnx@0.5.11,5.11-0.133:20101027T183107Z
        $fmri = explode("@", $pkgstring);
        $pkgname = $fmri[0];                  // BRCMbnx
        $fmri = $fmri[1];
        $pkg->name = $pkgname;
        $pkg->fmri = $fmri;
        if ($pkg->fetchFromFields(array("name", "fmri"))) {
          echo "  > Inserted $pkgname @ $fmri\n";
          $pkg->insert();
        }
        $pkg->parseFMRI();
      case "description":
      case "pkg.description":
	$value = $values[0];
        $value = strip_quote($value);
	if ($pkg) {
          if (strlen($value) > strlen($pkg->desc))
	    $pkg->desc = $value;
	 }
	break;
      case "pkg.summary":
	$value = $values[0];
        $value = strip_quote($value);
	if ($pkg) {
          if (strlen($value) > strlen($pkg->summary))
	    $pkg->summary = $value;
	}
	break;
      default:
	break;
    }

    return $vars;
  }

  public static function parseStringVars($str) {
    $ret = array();

    $f_state = 0;
    $v_quoted = false;
    $v = $k = ""; // Key-Value Pair
    $words = explode(' ', $str);
    foreach($words as $word) {
      if ($f_state == 0) { // set Key (finish with =)

        /* First check if we haven't already something inide $v and $k,
	 * if yes, add it to the result array
	 */
        if (!empty($k) || !empty($v)) {
          array_push($ret, array($k => $v));
	  $v = $k = "";
	}

        if (!($p = strpos($word, '='))) 
	  break;
     
        $k = substr($word, 0, $p);
        $f_state++; // goto value

        $p++;
        if ($p <= strlen($word)) {
          $word = substr($word, $p);
          if ($word[0] == '"') {
            $v_quoted = true;
	    $word = substr($word, 1); // skip the "
	  } else {
 	   $f_state--;
	  }
          $v = $word;
	}

      } else if ($f_state == 1) { // set Value (finish with space or with ")

        if (!$v_quoted) {
          if ($word[0] == '"') {

            $v_quoted = true;
	    $word = substr($word, 1); // skip the "

	  } else {

	    $f_state--; // back to Key

	  }

          $v = $word;
	} else { /* $v_quoted == true */

          if ($word[strlen($word)-1] == '"') { // the end of the value
	    $word = substr($word, 0, strlen($word) - 1);
	    $f_state--;
	  }

          $v .= ' '.$word;
	}

      }
    }
    if (!empty($k) || !empty($v)) {
      array_push($ret, array($k => $v));
      $v = $k = "";
    }
    return $ret;
  }
}

class Pkg extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $name = "";
  public $fmri = "";
  public $version = "";
  public $buildver = "";
  public $branchver = "";
  public $pstamp = "";
  public $desc = "";
  public $summary = "";
  public $arch = "";

  /* Lists */
  public $a_patches = array();
  public $a_files = array();
  public $a_rls = array();

  /* Obj */
  public $o_ips = null;

  /* Parsing */

  public static $a_tokens = array();

  public function parseFMRI() {
    $fmri = preg_split("/[\,\-\:]/", $this->fmri);
    $this->version = $fmri[0];
    if (isset($fmri[3])) {
      $this->pstamp = $fmri[3];
      $this->pstamp = strtotime($fmri[3]);
    }
    $this->buildver = $fmri[1];
    if (isset($fmri[2])) {
      $this->branchver = $fmri[2];
    }
  }

  public function __toString() {
    return $this->name."@".$this->fmri;
  }


  public function parseIPSLine($line) {

    $line = trim($line);
    if (empty($line)) 
      return -1;

    /* take keyword */
    if (!($pos = strpos($line, ' ')))
      return -1;
  
    $keyword = substr($line, 0, $pos);

    /* Prepare newline for calling method */
    $nline = substr($line, $pos + 1);

    foreach($this->a_tokens as $token) {
      if (!strcmp($token->key(), $keyword))
        return $token->call($this, $nline);
    }

    return -1; /* not found */
  }

  public function parseIPS($c) {

    $po = null;
    $lines = explode(PHP_EOL, $c);
    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) 
        continue;

      $ret = $this->parseIPSLine($line);
    }   
    if (isset($po)) {
      $po->update();
      return $po;
    }
    return null;
  }

  /* Files */
  public function fetchFiles($all=1) {

    $this->a_files = array();
    $table = "`jt_pkg_files` jt, `files` f";
    $index = "`name`, `fileid`, `size`, `md5`, `sha1`";
    $where = "WHERE `id_pkg`='".$this->id."' AND f.id=jt.fileid";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new File($t['fileid']);
        $k->name = $t['name'];
        $k->size = $t['size'];
        $k->md5 = $t['md5'];
        $k->sha1 = $t['sha1'];
        array_push($this->a_files, $k);
      }
    }
    return 0;
  }

  public function addFile($k) {

    $table = "`jt_pkg_files`";
    $names = "`fileid`, `id_pkg`";
    $values = "'$k->id', '".$this->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_files, $k);
    return 0;
  }

  public function setFileAttr($file) {

    if (!$file)
      return -1;

    $table = "jt_pkg_files";
    $set = "`size`='".$file->size."', `md5`='".$file->md5."', `sha1`='".$file->sha1."'";
    $where = " WHERE `fileid`='".$file->id."' AND `id_pkg`='".$this->id."'";

    if (mysqlCM::getInstance()->update($table, $set, $where)) {
      return -1;
    }
    return 0;

  }

  public function delFile($k) {

    $table = "`jt_pkg_files`";
    $where = " WHERE `fileid`='".$k->id."' AND `id_pkg`='".$this->id."'";

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

  public function isFile($k) {
    foreach($this->a_files as $ko)
      if (!strcasecmp($ko->name, $k->name))
        return TRUE;
    return FALSE;
  }

  public function isNew() {
    $now = time();
    if (($now - $this->pstamp) < 3600*24*15)
      return true;
    return false;
  }

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "pkg";
    $this->_nfotable = "nfo_pkg";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE,
                        "fmri" => SQL_PROPE,
                        "version" => SQL_PROPE,
                        "buildver" => SQL_PROPE,
                        "branchver" => SQL_PROPE,
                        "pstamp" => SQL_PROPE,
                        "desc" => SQL_PROPE,
                        "summary" => SQL_PROPE,
                        "arch" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "fmri" => "fmri",
                        "version" => "version",
                        "buildver" => "buildver",
                        "branchver" => "branchver",
                        "pstamp" => "pstamp",
                        "desc" => "desc",
                        "summary" => "summary",
                        "arch" => "arch"
                 );


    /* Init IPS fmri parser */
    $this->a_tokens = array();
    array_push($this->a_tokens, new IPSToken("set", "t_set"));
    array_push($this->a_tokens, new IPSToken("file", "t_file"));
    //array_push($this->a_tokens, new IPSToken("depend", ""));
    //array_push($this->a_tokens, new IPSToken("dir", ""));
    //array_push($this->a_tokens, new IPSToken("driver", ""));
    //array_push($this->a_tokens, new IPSToken("hardlink", ""));
    //array_push($this->a_tokens, new IPSToken("legacy", ""));
    //array_push($this->a_tokens, new IPSToken("license", ""));
    //array_push($this->a_tokens, new IPSToken("link", ""));
    //array_push($this->a_tokens, new IPSToken("signature", ""));
    //array_push($this->a_tokens, new IPSToken("group", ""));
    //array_push($this->a_tokens, new IPSToken("user", ""));
  }

}
?>
