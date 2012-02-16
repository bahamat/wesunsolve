<?php
/**
 * IPSToken object
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

    $up = false;
    if ($pkg && $pkg->o_ips && $pkg->o_ips->f_nofiles) return array();

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
    $path = IPSToken::getVar($vars, 'path');
    $size = IPSToken::getVar($vars, 'pkg.size');
    $elfarch = IPSToken::getVar($vars, 'elfarch');
    $variantarch = IPSToken::getVar($vars, 'variant.arch');
    $elfbits = IPSToken::getVar($vars, 'elfbits');
    $file = new File();
    $file->name = '/'.$path;

    if ($file->fetchFromField("name")) {
      $file->insert();
      echo "   |---> Added file $file to DB\n";
    }

    if ($elfarch && !empty($elfarch)) {
      $file->arch = $elfarch;
    }
    if (empty($file->arch) && !empty($variantarch)) {
      $file->arch = $variantarch;
    }
    if ($elfbits && !empty($elfbits)) {
      $file->bits = $elfbits;
    }

    if ($pkg->id && $pkg->id != -1) {
      if (!($fo = $pkg->isFile($file))) {
        $pkg->addFile($file);
        echo "  |---> linked $file to $pkg\n";
        $up = true;
      } else {
        $file = $fo;
      }
    }

    if (strcmp($file->sha1, $hash)) {
      $file->sha1 = $hash;
      $up = true;
    }
    if (empty($file->md5) || $file->md5 == -1) {
      $file->md5 = $pkg->o_ips->md5Sum($file);
      $up = true;
      echo "  |---> Updated md5 sum of $file to be ".$file->md5."\n";
    }
    if ($size && $size != 0) {
      $file->size = $size;
      $up = true;
    }
    if ($up) $pkg->setFileAttr($file);
    return $vars;
  }

  public static function getVar($vars, $name) {
    foreach($vars as $var) {
      foreach($var as $k => $v) {
        if (!strcmp($k, $name)) {
          return $v;
	}
      }
    }

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
	if ($po->fetchFromFields(array("name", "path", "fmri"))) {
          echo "  > Inserted ".$po."\n";
          $po->insert();
        }
	foreach($values as $v) {
 	  $b = new Bugid($v);
	  if ($b->fetchFromId()) {
 	    echo "  > New bugid found $b\n";
	    $b->insert();
	    $b->flag_update();
	  }
	  if (!$pkg->isBugid($b)) {
	    $pkg->addBugid($b, $po); // link bug fixed with this package
				   // second argument mention the affected package
            echo "  > Linked $b fixed by $pkg (affect $po)\n";
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

        if (empty($word)) continue;

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
          if (empty($word)) continue;
          if ($word[0] == '"') {

            $v_quoted = true;
	    $word = substr($word, 1); // skip the "

	  } else {

	    $f_state--; // back to Key

	  }

          $v = $word;
	} else { /* $v_quoted == true */

          if (empty($word)) {
            $v .= ' ';
	    continue;
	  }
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

?>
