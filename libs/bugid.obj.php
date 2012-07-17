<?php
/**
 * Bugid object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Bugid extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $synopsis = "";
  public $available = 0;
  public $tried = 0;
  public $category = "";
  public $subcat = "";
  public $product = "";
  public $state = "";
  public $severity = "";
  public $substate = "";
  public $submitter = "";
  public $sponsor = "";
  public $type = "";
  public $d_submit = "";
  public $d_created = 0;
  public $d_updated = 0;
  public $commit_tf = 0;
  public $duplicate_of = "";
  public $first_reported_bug_id = "";
  public $fixed_in = "";
  public $introduced_in = "";
  public $related_bugs = "";
  public $reported_against = "";
  public $is_raw = 0;
  public $views = 0;

  public $id_affect = -1;
  public $id_fixed = -1;
  public $o_fixed = null;
  public $o_affect = null;

  /* Fulltext */
  private $_ft;
  public $score = 0; // Score result from full text search

  public $a_comments = array();
  public $u_when = 0;

  public static function linkize($str) {
    $ret = $str;
    // match 7 digit as bugids
    $ret = preg_replace('/(^|\s| )([0-9]{7})/', '${1}<a href="/bugid/id/${2}">${2}</a>', $ret);
    return $ret;
  }

  public function name() {
    return $this->id;
  }

  public function link($full=0) {
    $link = "";
    if ($full) {
      $link = '<a href="http://wesunsolve.net/bugid/id/'.$this->id.'">'.$this->id.'</a>';
    } else {
      $link = '<a href="/bugid/id/'.$this->id.'">'.$this->id.'</a>';
    }
    return $link;
  }

  public function __toString() {
    return $this->id;
  }


  public function unflag_update() {
    $my = mysqlCM::getInstance();
   
    $table = "`bugs_update`";
    $where = " WHERE `bugid`=".$my->quote($this->id);

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    return 0;
  }

  public function flag_update() {
    $my = mysqlCM::getInstance();
   
    $table = "`bugs_update`";
    $names = "`bugid`";
    $values = $my->quote($this->id);

    if ($my->insert($names, $values, $table)) {
      return -1;
    }
    return 0;
  }

  /* Load bugids from MOS and store into db */
  public function dl($force = 0) {
    global $config;

    $d = $config['bidpath']."/".substr($this->id, 0, 2);
    if (!is_dir($d)) {
      mkdir($d);
    }
    $d .= "/".substr($this->id, 2, 2);
    if (!is_dir($d)) {
      mkdir($d);
    }
    $fp = $d."/".$this->id.".html";

    $there = 0;
    if (file_exists($fp)) {
      $size = filesize($fp);
      if (!$size || $size == 2009) {
	unlink($fp);
      }
      echo "Existing!";
      $raw = $this->ft("raw");
      if (!$force && !empty($raw)) {
        return -1;
      }
      $there = 1;
      if ($force) {
        unlink($fp);
        $there = 0;
      }
    }

    if (!$there) {
      $cmd = "/usr/bin/wget -q --no-check-certificate -U \":-)\" ";
      $cmd .= " --load-cookies /srv/sunsolve/tmp/cookies.txt ";
      $cmd .= "--save-cookies /srv/sunsolve/tmp/cookies.txt --keep-session-cookies ";
      $cmd .= " -O \"".$fp."\" \"".$config['bugurl'].$this->id."\"";
      passthru($cmd);
    }

    $this->tried = 1;
    $this->update();

    if (file_exists($fp)) {
      $size = filesize($fp);
      echo "($size bytes)";
      if (!$size || $size == 2009) {
	unlink($fp);
        echo "403";
        return -1;
      }
      if($size == 7997) {
        echo "404";
        return -1;
      }
    } else {
      echo "dont exist";
      return -1;
    }
    
    // Load into database...
    $content = file_get_contents($fp);
    if (preg_match("/Article or Bug cannot be displayed. Possible reasons are:/", $content))
      $content = "";
    // Already remove some xhtml junk.
    $content = preg_replace('/(<style>.+?)+(<\/style>)/i', '', $content); 
    $content = preg_replace('/<html>/i', '', $content); 
    $content = preg_replace('/<\\/html>/i', '', $content); 
    $content = preg_replace('/<head>/i', '', $content); 
    $content = preg_replace('/<\\/head>/i', '', $content); 
    $content = preg_replace('/<meta .*>/i', '', $content); 
    $content = preg_replace('/<body .*>/i', '', $content); 
    $content = preg_replace('/<\\/body>/i', '', $content); 
    $content = preg_replace('/<link .*>/i', '', $content); 
    $content = preg_replace('/<!DOCTYPE .*>/i', '', $content); 
    $content = preg_replace('/<!--.*-->/i', '', $content); 

    if (empty($content)) return -1;

    $this->is_raw = 1;
    $this->setft("raw", $content);
    $this->available = 1;
    return $this->update();
  }
 
  public function parseRaw($force = 0) {
    if ($this->_ft) {
      if ($this->is_raw) {
	// remove images
	$raw = preg_replace('/(<.+?\/CSP\/.+?)+(\/>)/i', '', $this->_ft->raw);	
	$raw = preg_replace('/(<.+?\/CSP\/.+?)+(>)/i', '', $raw);	
	$raw = preg_replace('/(<.+?\/CSP\/.+?)+(\/[a-z]*>)/i', '', $raw);	
	$this->setft("raw", $raw);
	$this->update();
        $raw_lines = explode(PHP_EOL, $this->_ft->raw);
	if (!count($raw_lines)) {
	  return false;
	}
/* @TODO ADD check for bugContent.XXX here */
        if (preg_match("/bugsContent.Sun/", $raw)) {
	   $vendor = "Sun";
        } else if (preg_match("/bugsContent.Oracle/", $raw)) {
           $vendor = "Oracle";
        } else {
           $vendor = "";
        }
	foreach($raw_lines as $line) {
	  $line = trim($line);
	  if(empty($line)) {
	    continue;
	  }
/*
	  if (preg_match("/<b>Related bugs/",$line)) {
            $rb = preg_replace('/.*<b>.*<\/b>:(.+)<br/>/i', '$1', $line);
	  }
*/
	  if (preg_match("/^bugsContent.".$vendor."/", $line)) {
	    $bcraw = preg_split("/bugsContent.".$vendor." = \"/", $line);
	    $bcraw = $bcraw[1];
	    $bcraw = substr($bcraw,0,strlen($bcraw)-2);
	    //$bcraw = stripslashes(substr($bcraw,0,strlen($bcraw)-2));
	    // :%s/>[\s]*</>\r\n</g
	    $bcraw = preg_replace('/>[\s]*</i','>'."\r\n".'<', $bcraw);
	    $this->setft("raw", $bcraw);
	    $this->update();
	    echo "\t> Selected sun bug product family and stripped raw output, reintering parseRaw..\n";
	    return $this->parseRaw(); // recurse to parse the new raw data
	  }
          if (preg_match("/^.*<STRONG>Bug ".$this->id.":/", $line)) {
	    $line = preg_replace('/^.*<STRONG>Bug '.$this->id.':/i', '', $line);
	    $line = preg_replace('/<\/STRONG>.*$/i', '', $line);
   	    $line = trim($line);
	    if (strcmp($this->synopsis, $line)) {
              $this->synopsis = $line;
              $this->setft("synopsis", $line);
	      echo "\t> Updated synopsis\n";
	      $this->update();
	    }
	  }
	  if (preg_match('/<h3 class="sbugH">/', $line)) {
	    /* on this line, we have almost every info we need... we're in the Fix Request */
	    /* description */
	    $lineDesc = preg_replace('/^.*<h3 class="sbugH">Description:<\/h3>/i', '', $line);
	    $lineDesc = preg_replace('/<table class="sbugFRAttrTABLE">.*$/i', '', $lineDesc);
   	    if (!empty($lineDesc)) {
	      $curdesc = $this->ft("description");
	      if (empty($curdesc) || strcmp($lineDesc, $curdesc) || $force) {
	        echo $lineDesc;
	        $lineDesc = str_replace('<br/>', "\r\n", $lineDesc);
	        $lineDesc = str_replace('<br>', "\r\n", $lineDesc);
	        $lineDesc = str_replace('&lt;br/&gt;&lt;br/&gt;', "\r\n", $lineDesc);
	        $this->setft("description", str_replace('<br/>', "\r\n", $lineDesc));
	        $this->update();
	        echo "\t> Updated description\n";
	      }
	    }
	    /* Various fields */
	    $line2 = preg_replace('/<\/td>/', '', $line);
	    $line2 = preg_replace('/<\/tr>/', '', $line2);
	    $line2 = preg_replace('/<tr class="sbugFRAttrTR">/', '', $line2);
	    $names = preg_split('/<td class="sbugFRAttrNameTD">/', $line2);
	    foreach($names as $val) {
		if (preg_match('/<h3 class="sbugH">/', $val)) {
		  continue;
		}
	        $val = preg_replace('/<\/table><br\/>/', '', $val);
		$f = preg_split('/<td class="sbugFRAttrValue">/', $val);
	        $name = $f[0];
		if (isset($f[1])) {
		   $value = $f[1];
		} else {
		   $value = null;
		}
		switch($name) {
		  case "Date Modified":
		    break;
		  case "Verified Version":
		    break;
		  case "Integrated Version":
		    break;
		  case "Fixed Version":
		    if (!empty($value)) {
		      if (empty($this->fixed_in) || strcmp($value, $this->fixed_in)) {
	                echo "\t> Updated fixed_in\n";
			$this->fixed_in = $value;
		      }
		    }
		    break;
		  case "Committed Version":
		    break;
		  case "Target":
		    break;
		  case "Customer Status":
		    break;
		  case "Severity":
		    if (!empty($value)) {
		      if (empty($this->severity) || strcmp($value, $this->severity)) {
			$this->severity = $value;
	                echo "\t> Updated severity\n";
		      }
		    }
		    break;
		  case "Duplicate Of":
		    if (!empty($value)) {
		      if (empty($this->duplicate_of) || strcmp($value, $this->duplicate_of)) {
			$this->duplicate_of = $value;
	                echo "\t> Updated duplicate_of\n";
		      }
		    }
		    break;
		  default:
		    break;
		}
	    }
	    /* Determine if we should use raw report or separate fields */
	    $this->update();
	  }
	}
      }
    }
    return false;
  }

  private function p_date($str) {
    $d = explode("-", $str);
    if (count($d) != 3) return 0;
    $day = $d[2];
    $year = $d[0];
    $month = $d[1];
    if ($month > 12)
      return false;
    return mktime(0,0,0,$month, $day, $year);
  }

  public function createDate($str) {
    $this->d_created = $this->p_date($str);
  }

  public function updateDate($str) {
    $this->d_updated = $this->p_date($str);
  }

  public function submitDate($str) {
    $this->d_submit = $this->p_date($str);
  }


  public function fetchFulltext() {
    $this->_ft = new FTBugid($this);
    if ($this->_ft->fetchFromId()) {
      $this->_ft->insert();
    }
  }

  public function ft($var) {
    if ($this->_ft) {
      if (isset($this->_ft->{$var})) {
        return $this->_ft->{$var};
      }
    }
    return false;
  }

  public function setft($var, $value = NULL) {
    if ($this->_ft) {
      $this->_ft->{$var} = $value;
      return true;
    }
    return false;
  }

  public function update() {
    $rc = parent::update();
    if ($this->_ft) {
      return $rc + $this->_ft->update();
    }
  }

  public function insert() {
    parent::insert();
    if ($this->_ft) {
      $this->_ft->insert();
    }
  }
  /* Keywords */
  function fetchKeywords($all=1) {

    $this->a_keywords = array();
    $table = "`jt_bug_keywords`";
    $index = "`kid`";
    $where = "WHERE `bugid`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    { 
      foreach($idx as $t) {
        $k = new Keyword($t['kid']);
        $k->fetchFromId();
        array_push($this->a_keywords, $k);
      }
    }
    return 0;
  }

  function addKeyword($k) {

    $table = "`jt_bug_keywords`";
    $names = "`kid`, `bugid`";
    $values = "'$k->id', '".$this->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_keywords, $k);
    return 0;
  }

  function delKeyword($k) {

    $table = "`jt_bug_keywords`";
    $where = " WHERE `kid`='".$k->id."' AND `bugid`='".$this->id."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_keywords as $ak => $v) {
      if (!strcmp($k->keyword, $v->keyword)) {
	unset($this->a_keywords[$ak]);
      }
    }
    return 0;
  }

  function isKeyword($k) {
    foreach($this->a_keywords as $ko)
      if (!strcasecmp($ko->keyword, $k))
        return TRUE;
    return FALSE;
  }


  
  /* Lists */
  public $a_patches = array();
  public $a_pkgs = array();
  public $a_keywords = array();

  public function fetchAll() {
    $this->fetchPatches();
    $this->fetchPkgs();
    $this->fetchComments();

  }

  public function details() {
    global $config;

    if (!$this->available)
      return -1;

    $dir1 = substr($this->id, 0, 2);
    $dir2 = substr($this->id, 2, 2);
    $file = $config['bidpath']."/$dir1/$dir2/".$this->id.".html";
    if (file_exists($file)) {
      return file_get_contents($file);
    }
  }

  public function fetchPatches() {
    $index = "`p`.`patch` as `patch`, `p`.`revision` as `revision`";
    $table = "`patches` as `p`, `jt_patches_bugids` as `jt1`";
    $where = "WHERE `p`.`patch`=`jt1`.`patchid` AND `p`.`revision`=`jt1`.`revision` AND `jt1`.`bugid`='".$this->id."'";
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Patch($t['patch'], $t['revision']);
        $k->fetchFromId();
        array_push($this->a_patches, $k);
      }
    }
    return 0;
  }

  public function fetchPkgs() {
    $index = "`id_pkg`";
    $table = "`jt_pkg_bugids`";
    $where = "WHERE `bugid`='".$this->id."'";
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Pkg($t['id_pkg']);
        $k->fetchFromId();
        array_push($this->a_pkgs, $k);
      }
    }
    return 0;
  }


  public function viewed() {
     $q = 'UPDATE '.$this->_table.' SET `views`=`views`+1 WHERE `id`='.$this->id;
     return MysqlCM::getInstance()->rawQuery($q);
  }

 /* static */

  public static function getMostviewed($nb = 10) {

    $res = array();
    $table = "`bugids`";
    $index = "`id`";
    $where = " ORDER BY `bugids`.`views` DESC LIMIT 0,$nb";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Bugid($t['id']);
        $k->fetchFromId();
        array_push($res, $k);
      }
    }
    return $res;
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
    $where = "WHERE `type`='bug' AND `id_on`='".$this->id."' AND (`is_private`=0 OR (`id_login`=$id AND `is_private`=1)) ORDER BY `added` ASC";

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

  public static function getLastviewed($l) {

    $res = array();
    $table = "`u_history`";
    $index = "`id_link`,`when`";
    $where = "WHERE `id_login`=".$l->id." AND `what`='bug'";
    $where .= " ORDER BY `u_history`.`when` DESC LIMIT 0,10";
 
    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Bugid($t['id_link']);
        $k->fetchFromId();
        $k->u_when = $t['when'];
        array_push($res, $k);
      }
    }
    return $res;
  }



 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "bugids";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "synopsis" => SQL_PROPE,
                        "category" => SQL_PROPE,
                        "subcat" => SQL_PROPE,
                        "product" => SQL_PROPE,
                        "state" => SQL_PROPE,
                        "substate" => SQL_PROPE,
                        "submitter" => SQL_PROPE,
                        "sponsor" => SQL_PROPE,
                        "type" => SQL_PROPE,
                        "d_submit" => SQL_PROPE,
                        "d_created" => SQL_PROPE,
                        "d_updated" => SQL_PROPE,
                        "commit_tf" => SQL_PROPE,
                        "duplicate_of" => SQL_PROPE,
                        "first_reported_bug_id" => SQL_PROPE,
                        "fixed_in" => SQL_PROPE,
                        "introduced_in" => SQL_PROPE,
                        "related_bugs" => SQL_PROPE,
                        "reported_against" => SQL_PROPE,
                        "severity" => SQL_PROPE,
                        "tried" => SQL_PROPE,
                        "is_raw" => SQL_PROPE,
                        "views" => SQL_PROPE,
                        "available" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "synopsis" => "synopsis",
                        "category" => "category",
                        "tried" => "tried",
                        "subcat" => "subcat",
                        "product" => "product",
                        "state" => "state",
                        "substate" => "substate",
                        "submitter" => "submitter",
                        "sponsor" => "sponsor",
                        "type" => "type",
                        "d_submit" => "d_submit",
                        "d_created" => "d_created",
                        "d_updated" => "d_updated",
                        "commit_tf" => "commit_tf",
                        "duplicate_of" => "duplicate_of",
                        "first_reported_bug_id" => "first_reported_bug_id",
                        "fixed_in" => "fixed_in",
                        "introduced_in" => "introduced_in",
                        "related_bugs" => "related_bugs",
                        "reported_against" => "reported_against",
                        "severity" => "severity",
                        "views" => "views",
                        "is_raw" => "is_raw",
                        "available" => "available"
                 );
  }

}
?>
