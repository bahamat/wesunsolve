<?php
/**
 * Various functions
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2009-2011, Gouverneur Thomas
 * @version 1.0
 * @package includes
 * @subpackage libraries
 * @category libraries
 * @filesource
 */

function cli_diff($old, $new) {
  if (!file_exists($old)) return false;
  if (!file_exists($new)) return false;

  $cmd = "/usr/bin/diff -w $old $new";
  $out = array();
  exec($cmd, $out, $ret);
  $ret = "";
  foreach($out as $line) { $ret .= "$line\n"; };
  return $ret;
}

function getData($obj, $arg) {

  if (is_array($arg)) {
    $first = $arg[0];
    $newarg = array();
    if (count($arg) == 2) {
      $newarg = $arg[1];
    } else {
      for ($i=1,$j=0; $i<count($arg); $i++,$j++) {
        $newarg[$j] = $arg[$i];
      }
    }
    if (strstr($first, "()")) { /* need to call function */
      if (is_array($newarg)) {
        return call_user_func_array(array($obj, str_replace("()", "", $first)), $newarg);
      } else {
        return call_user_func(array($obj, str_replace("()", "", $first)), $newarg);
      }
    } else {
      return getData($obj->{$first}, $newarg);
    }
  } else {
    return $obj->{$arg};
  }
}

function extractTmp($a, $odir) {

  if (!is_dir($odir)) 
    mkdir ($odir, 0755, true);

  $e = explode(".", $a);
  $ext = $e[count($e)-1];
  switch($ext) {
    case "zip":
    case "ZIP":
      $cmd = "/usr/bin/unzip -d $odir $a > /dev/null 2>&1";
      $ret = `$cmd`;
    break;
    case "Z":
      $cmd = "/bin/gzip -dc $a | /bin/tar -C $odir -xf - > /dev/null 2>&1";
      $ret = `$cmd`;
    break;
    default:
      return -1;
    break;
  }
  return 0;
}

function chdirmod($dir, $mod) {
  if (is_dir($dir)) {
    chmod($dir, $mod);
    foreach(glob($dir.'/*', GLOB_ONLYDIR) as $d) {
      chdirmod($d, $mod);
    }
  }
}

function strip_quote($str) {
  if ($str[0] == '"') {
    $str = substr($str, 1);
  }
  if ($str[strlen($str) - 1] == '"') {
    $str = substr($str, 0, strlen($str) - 1);
  }
  return $str;
}

?>
