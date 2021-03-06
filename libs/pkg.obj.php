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
  public $f_irc = 1;
  public $f_twitter = 1;

  /* Lists */
  public $a_patches = array();
  public $a_files = array();
  public $a_rls = array();
  public $a_comments = array();
  public $a_bugids = array();
  public $a_affect = array();
  public $a_previous = array();
  public $a_ips = array();

  /* Obj */
  public $o_ips = null;
  public $o_latest = null;

  public static function fetchToAnnounce() {
    $ret = array();
    $table = "`pkg`";
    $index = "`id`";
    $where = "WHERE `f_irc`='0' OR `f_twitter`='0' LIMIT 0,5";
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    { 
      foreach($idx as $t) {
	$po = new Pkg($t['id']);
        $po->fetchFromId();
	$ret[] = $po;
      }
    }

    return $ret;
  }

  public function fetchFromFMRI() {
    $rc = $this->fetchFromFields(array("name", "fmri"));
    
/*
    if ($rc) {
      if ($this->path = "/") {
        return $this->fetchFromFields(array("name", "fmri"));
      }
    }
*/
    return $rc;
  }

  public static function countPkgPath($path) {
    $index = "count(`id`) as c";
    $table = "`pkg`";
    $where = "WHERE `path`='".$path."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (!count($idx)) {
        return null;
      }
      return $idx[0]['c'];
    }
    return 0;
  }

  private static function addDirToTree(&$tree, $path) {
    $path = explode('/', $path);
    $curr = &$tree['/'];
    foreach($path as $dir) {
      if (empty($dir))
	continue;
      if (!isset($curr[$dir])) {
        $curr[$dir] = array();
      }
      $curr = &$curr[$dir];
    }
  }

  public static function mkTree() {

    $tree = array('/' => array());
    $table = "`pkg`";
    $index = "distinct `path` as p";
    $where = "order by `path`";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        Pkg::addDirToTree($tree, $t['p']);
      }
    }

    return $tree;
  }

  public function fetchLatest() {
    $index = "`id`";
    $table = "`pkg`";
    $where = "WHERE `name`='".$this->name."' AND `path`='".$this->path."' AND `pstamp`>".$this->pstamp." ORDER BY `pstamp` DESC LIMIT 0,1";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (!count($idx)) {
        return null;
      }
      $this->o_latest = new Pkg($idx[0]['id']);
      if ($this->o_latest->id == $this->id) {
	$this->o_latest = null;
	return 0;
      }
      $this->o_latest->fetchFromId();
    }
    return 0;
  }

  /* Parsing */

  public static $a_tokens = array();

  public function fromString($str) {
    //last-fmri=system/file-system/zfs@0.5.11,5.11-0.175.0.0.0.2.1:20111019T072820Z
    if ($this->o_ips) {
      $v = '/pkg:\/\/'.$this->o_ips->publisher.'/';
      $str = preg_replace($v, '', $str);
    }
    $value = preg_replace('/pkg:\/\//', '', $str);
    $value = preg_replace('/pkg:\//', '', $value);
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
    if ($this->path[0] != '/') $this->path = '/'.$this->path;
    return true;
  }

  public function fetchAll($all=1) {
    $this->fetchComments();
    if ($all == 1) $this->fetchFiles(); 
    $this->fetchBugids(); 
    $this->fetchAffect(); 
    $this->fetchLatest(); 
    $this->fetchIPS(); 
    $this->fetchPrevious(); 
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
  
  public function shortLink() {
    return '<a href="/pkg/id/'.$this->id.'">'.$this->shortName().'</a>';
  }

  public function link() {
    return '<a href="/pkg/id/'.$this->id.'">'.$this->name().'</a>';
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

  /* IPS */
  public function fetchIPS($all=1) {

    $this->a_ips = array();
    $table = "`jt_pkg_ips`";
    $index = "`id_ips`";
    $where = "WHERE `id_pkg`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new IPS($t['id_ips']);
        $k->fetchFromId();
        array_push($this->a_ips, $k);
      }
    }
    return 0;
  }


  /* Files */
  public function fetchFiles($all=1) {

    $this->a_files = array();
    $table = "`jt_pkg_files` jt, `files` f";
    $index = "`name`, `arch`, `bits`, `fileid`, `size`, `md5`, `sha1`";
    $where = "WHERE `id_pkg`='".$this->id."' AND f.id=jt.fileid ORDER BY `name` ASC";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new File($t['fileid']);
        $k->name = $t['name'];
        $k->size = $t['size'];
        $k->arch = $t['arch'];
        $k->bits = $t['bits'];
        $k->md5 = $t['md5'];
        $k->sha1 = $t['sha1'];
        array_push($this->a_files, $k);
      }
    }
    return 0;
  }

  public function addFile($k) {

    $table = "`jt_pkg_files`";
    $names = "`fileid`, `id_pkg`, `arch`, `bits`";
    $values = "'$k->id', '".$this->id."', '".$k->arch."', '".$k->bits."'";

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
    $where = " WHERE `fileid`='".$k->id."' AND `id_pkg`='".$this->id."' AND `arch`='".$this->arch."' AND `bits`='".$this->bits."'";

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
    foreach($this->a_files as $ko) {
      if (!strcasecmp($ko->name, $k->name) && !strcmp($ko->arch, $k->arch) && $ko->bits == $k->bits) {
        return $ko;
      }
    }
    return FALSE;
  }

  public function isNew() {
    $now = time();
    if (($now - $this->pstamp) < 3600*24*31)
      return true;
    return false;
  }

  function fetchAffect($all=1) {

    $this->a_affect = array();
    $table = "`jt_pkg_bugids`";
    $index = "`bugid`, `id_pkg`";
    $where = "WHERE `id_affect`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Bugid($t['bugid']);
        if ($all) $k->fetchFromId();
        array_push($this->a_affect, $k);
      }
    }
    return 0;
  }

  /* Bugids */
  function fetchBugids($all=1) {

    $this->a_bugids = array();
    $table = "`jt_pkg_bugids`";
    $index = "`bugid`, `id_affect`";
    $where = "WHERE `id_pkg`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Bugid($t['bugid']);
	$k->id_affect = $t['id_affect'];
        if ($all) $k->fetchFromId();
        array_push($this->a_bugids, $k);
      }
    }
    return 0;
  }

  function addBugid($k, $po) {

    $table = "`jt_pkg_bugids`";
    $names = "`bugid`, `id_pkg`, `id_affect`";
    $values = "'$k->id', '".$this->id."', '".$po->id."'";

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

  public function fetchPrevious() {
    $this->a_previous = array();
    $table = "`pkg`";
    $index = "`id`";
    $where = "WHERE `name`='".$this->name."' AND `path`='".$this->path."' AND `pstamp`<".$this->pstamp." ORDER BY `pstamp` DESC";
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Pkg($t['id']);
        $k->fetchFromId();
        $k->fetchBugids();
        array_push($this->a_previous, $k);
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
                        "views" => SQL_PROPE,
                        "f_irc" => SQL_PROPE,
                        "f_twitter" => SQL_PROPE,
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
                        "views" => "views",
                        "f_irc" => "f_irc",
                        "f_twitter" => "f_twitter",
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
