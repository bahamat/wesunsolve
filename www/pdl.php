<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 if (!$lm->o_login) {
   die("Should be logged in");
 }

 if (!$lm->o_login->is_dl) {
   die("Not authorized");
 }

 if (!isset($_GET['p']) && !isset($_GET['b'])) {
   die("Cannot be called as-is");
 }

 if (isset($_GET['p'])) {
   $id = mysql_escape_string($_GET['p']);
   if (!preg_match("/[0-9]{6}-[0-9]{2}/", $id)) {
     die("Malformed patch ID");
   }
 
   $p = explode("-", $id);
   $patch = new Patch($p[0], $p[1]);
   if ($patch->fetchFromId()) {
     die("Patch not found in our database");
   }
   $archive = $patch->findArchive();
   if (!$archive) {
     die("File not found");
   }
   $fn = explode("/",$archive);
   $fn = $fn[count($fn) - 1]; 
 } else if (isset($_GET['b'])) {
   $id = mysql_escape_string($_GET['b']);
   if (!preg_match("/[0-9]*/", $id)) {
     die("Malformed bundle ID");
   }

   $bundle = new Bundle($id);
   if ($bundle->fetchFromId()) {
     die("Bundle not found in our database");
   }
   $archive = $bundle->findArchive();
   if (!$archive) {
     die("File not found");
   }
   $fn = $bundle->filename;
 }

 IrcMsg::add("[WWW] User ".$lm->o_login->username." requested download of ".$fn, MSG_ADM);

 $size = filesize($archive);
 $fileinfo = pathinfo($archive);
 $file_extension = strtolower($fileinfo['extension']);
 switch($file_extension)
 {
	case 'Z':   $ctype='application/x-compress'; break;
	case 'tar.Z':   $ctype='application/x-compress'; break;
        case 'zip': $ctype='application/zip'; break;
        default:    $ctype='application/force-download';
 }
 $range = '';

 if(isset($_SERVER['HTTP_RANGE']))
 {
   list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);

   if ($size_unit == 'bytes')
   {
     //multiple ranges could be specified at the same time, but for simplicity only serve the first range
     //http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
     list($range, $extra_ranges) = explode(',', $range_orig, 2);
   }
   else
   {
     $range = '';
   }
 }
 else
 {
   $range = '';
 }
 $r = explode('-', $range, 2);
 $seek_start = $r[0];
 if (isset($r[1])) {
   $seek_end = $r[1];
 } else {
   $seek_end = '';
 }
// list($seek_start, $seek_end) = explode('-', $range, 2);
 $seek_end = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)),($size - 1));
 $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);

 if ($seek_start > 0 || $seek_end < ($size - 1))
 {
   header('HTTP/1.1 206 Partial Content');
 }
 header('Accept-Ranges: bytes');
 header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$size);
 header('Content-Type: '.$ctype);
 header("Content-Disposition: attachment; filename=\"$fn\""); 
 header('Content-Transfer-Encoding: binary');
 //header('Content-Length: '.filesize($archive));
 header('Content-Length: '.($seek_end - $seek_start + 1));
 header('Pragma: no-cache'); 

 $handle = fopen($archive, 'r'); 
 fseek($handle, $seek_start);

 while (!feof($handle)) {
  set_time_limit(0);
  print(fread($handle, 1024*8));
  flush();
  ob_flush();
 }
 fclose($handle); 
 //echo file_get_contents($archive);


?>
