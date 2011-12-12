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

    if (!($p = strpos($line, ' '))) {
      return -1;
    }
    $hash = substr($line, 0, $p);
    $nline = substr($line, $p + 1);
    $file = null;

    $vars = IPSToken::parseStringVars($nline);
    foreach($vars as $var) {
      foreach($var as $k => $v) {
	$up = false;
        if (!strcmp($k, 'path')) {
          $file = new File();
	  $file->name = '/'.$v;
	  if ($file->fetchFromField("name")) {
	    $file->insert();
	    echo "   |---> Added file $v to DB\n";
	  }
	  if (strcmp($file->sha1, $hash)) {
            $file->sha1 = $hash;
	    $up = true;
	  }
          if (empty($file->md5) || $file->md5 == -1) { 
	    $file->md5 = $pkg->o_ips->md5Sum($file);
	    $up = true;
	    echo "   |---> Updated md5 sum to be ".$file->md5."\n";
	  }
	  if ($pkg->id && $pkg->id != -1) {
            if (!$pkg->isFile($file)) {
	      $pkg->addFile($file);
	      echo "  |---> linked $v to $pkg\n";
	    }
	  }
	} else if (!strcmp($k, 'pkg.size')) {
          if ($file && $file->size != $v) {
	    $file->size = $v;
	    $up = true;
	  }
        }
	if ($up) $pkg->setFileAttr($file);
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
// set last-fmri=system/file-system/zfs@0.5.11,5.11-0.175.0.0.0.2.1:20111019T072820Z name=com.oracle.service.bugid value=6890231 value=7006046 value=7091693 value=7092930 value=7094901

    switch($name) {
      case "pkg.fmri";
	$value = $values[0]; // only one
        $pkg->fromString($value);
        if ($pkg->fetchFromFields(array("name", "path", "fmri"))) {
          echo "  > Inserted $pkg\n";
          $pkg->insert();
        }
	$pkg->fetchFiles();
        $pkg->fetchBugids();
        $pkg->parseFMRI();
        $pkg->update();
      case "com.oracle.service.bugid":
        /* Find affected fmri */
	$lastFMRI = "";
	foreach($vars as $var) {
	  foreach($var as $k => $v) {
	    if (!strcmp($k, "last-fmri")) {
	      $lastFMRI = $v;
	      break;
	    }
	  }
	}
	if (empty($lastFMRI)) {
	  return -1;
	}
	$po = new Pkg();
	$po->fromString($lastFMRI);
	if ($pkg->fetchFromFields(array("name", "path", "fmri"))) {
          echo "  > Inserted ".$po."\n";
          $po->insert();
        }
	foreach($values as $v) {
 	  $b = new Bugid($v);
	  if ($b->fetchFromId()) {
 	    echo "  > New bugid found $b\n";
	    $b->flag_update();
	  }
	  if (!$pkg->isBugid($b)) {
	    $pkg->addBugid($b, $po); // link bug fixed with this package
				   // second argument mention the affected package
            echo "  > Linked $b fixed by $pkg\n";
          }
	}
	break;
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
  public $path = "";
  public $fmri = "";
  public $version = "";
  public $buildver = "";
  public $branchver = "";
  public $pstamp = "";
  public $desc = "";
  public $summary = "";
  public $arch = "";
  public $updated = 0;
  public $added = 0;
  public $views = 0;

  /* Lists */
  public $a_patches = array();
  public $a_files = array();
  public $a_rls = array();
  public $a_comments = array();
  public $a_bugids = array();

  /* Obj */
  public $o_ips = null;

  /* Parsing */

  public static $a_tokens = array();

  public function fromString($str) {
    //last-fmri=system/file-system/zfs@0.5.11,5.11-0.175.0.0.0.2.1:20111019T072820Z
    $value = preg_replace('/pkg:\/\/solaris/', '', $str);
    $value = preg_replace('/pkg:/', '', $value);
    $value = explode('/', $value);
    $path = "";
    $np = count($value) - 1; $i=0;
// pkg://solaris
    foreach($value as $val) { if($i >= $np) continue; $path .= $val.'/'; $i++; }
    $pkgstring = $value[count($value)-1]; // BRCMbnx@0.5.11,5.11-0.133:20101027T183107Z
    $fmri = explode("@", $pkgstring);
    $pkgname = $fmri[0];                  // BRCMbnx
    $fmri = $fmri[1];
    $this->name = $pkgname;
    $this->path = $path;
    $this->fmri = $fmri;
    if (empty($this->path)) $this->path = "/";
    return true;
  }

  public function fetchAll($all=1) {
    $this->fetchComments();
    if ($all == 1) $this->fetchFiles(); 
    $this->fetchBugids(); 
  }

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
    $where = "WHERE `type`='pkg' AND `id_on`='".$this->id."' AND (`is_private`=0 OR (`id_login`=$id AND `is_private`=1)) ORDER BY `added` ASC";

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

  public function update() {
    $this->updated = time();
    parent::update();
  }

  public function insert() {
    $this->added = time();
    parent::insert();
  }

  public function viewed() {
     $q = 'UPDATE '.$this->_table.' SET `views`=`views`+1 WHERE `id`='.$this->id;
     return MysqlCM::getInstance()->rawQuery($q);
  }

  public function parseFMRI() {
    $fmri = preg_split("/[\,\-\:]/", $this->fmri);
    $this->version = $fmri[0];
    if (isset($fmri[3])) {
      $this->pstamp = $fmri[3];
      $this->pstamp = strtotime($fmri[3]);
    }
    if (isset($fmri[1])) {
      $this->buildver = $fmri[1];
    }
    if (isset($fmri[2])) {
      $this->branchver = $fmri[2];
    }
  }

  public function __toString() {
    return $this->path.$this->name."@".$this->fmri;
  }

  public function shortName() {
    return $this->name;
  }

  public function name() {
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

  /* Bugids */
  function fetchBugids($all=1) {

    $this->a_bugids = array();
    $table = "`jt_pkg_bugids`";
    $index = "`bugid`, `id_fixed`";
    $where = "WHERE `id_pkg`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Bugid($t['bugid']);
	$k->id_fixed = $t['id_fixed'];
        if ($all) $k->fetchFromId();
        array_push($this->a_bugids, $k);
      }
    }
    return 0;
  }

  function addBugid($k) {

    $table = "`jt_pkg_bugids`";
    $names = "`bugid`, `id_pkg`, `id_fixed`";
    $values = "'$k->id', '".$this->id."', '".$k->id_fixed."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_bugids, $k);
    return 0;
  }

  function delBugid($k) {

    $table = "`jt_pkg_bugids`";
    $where = " WHERE `bugid`='".$k->id."' AND `id_pkg`='".$this->id."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_bugids as $ak => $v) {
      if ($k->id == $v->id) {
        unset($this->a_bugids[$ak]);
      }
    }
    return 0;
  }

  function isBugid($k) {
    foreach($this->a_bugids as $ko)
      if ($ko->id == $k)
        return TRUE;
    return FALSE;
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
                        "path" => SQL_PROPE,
                        "fmri" => SQL_PROPE,
                        "version" => SQL_PROPE,
                        "buildver" => SQL_PROPE,
                        "branchver" => SQL_PROPE,
                        "pstamp" => SQL_PROPE,
                        "desc" => SQL_PROPE,
                        "summary" => SQL_PROPE,
                        "arch" => SQL_PROPE,
                        "added" => SQL_PROPE,
                        "updated" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "path" => "path",
                        "fmri" => "fmri",
                        "version" => "version",
                        "buildver" => "buildver",
                        "branchver" => "branchver",
                        "pstamp" => "pstamp",
                        "desc" => "desc",
                        "summary" => "summary",
                        "arch" => "arch",
                        "added" => "added",
                        "updated" => "updated"
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
