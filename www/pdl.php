<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
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

 header('Content-Type: application/octet-stream');
 header("Content-Disposition: attachment; filename=\"$fn\""); 
 header('Content-Transfer-Encoding: binary');
 header('Content-Length: '.filesize($archive));
 header('Pragma: no-cache'); 
 $handle = fopen($archive, 'r'); 
 while (!feof($handle)) {
  echo fread($handle, 8192);
 }
 fclose($handle); 
 //echo file_get_contents($archive);


?>
