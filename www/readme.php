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

 if (!isset($_GET['id']) && !isset($_GET['bn'])) {
   die("Cannot be called as-is");
 }

 if (isset($_GET['id'])) {
   $id = mysql_escape_string($_GET['id']);
   if (!preg_match("/[0-9]{6}-[0-9]{2}/", $id)) {
     die("Malformed patch ID");
   }
 
   $p = explode("-", $id);
   $patch = new Patch($p[0], $p[1]);
   if ($patch->fetchFromId()) {
     die("Patch not found in our database");
   }

   $dir1 = substr($patch->patch, 0, 2);
   $dir2 = substr($patch->patch, 2, 2);
   if (!file_exists($config['ppath']."/$dir1/$dir2/README.".$patch->name())) {
     die("Patch readme not found on our server");
   }

   header("Content-type: text/plain");
   header("Content-Disposition: filename=README.".$patch->name());
   echo file_get_contents($config['ppath']."/$dir1/$dir2/README.".$patch->name());
 } else if (isset($_GET['bn'])) {
   $id = mysql_escape_string($_GET['bn']);
   if (!preg_match("/[0-9]*/", $id)) {
     die("Malformed bundle ID");
   }
   $bundle = new Bundle($id);
   if ($bundle->fetchFromId()) {
     die("Bundle not found in our database");
   }
   $rp = $bundle->readmePath();
   if (file_exists($rp)) {
     header("Content-type: text/plain");
     header("Content-Disposition: filename=README.".$bundle->filename);
     echo file_get_contents($rp);
   }

 }

?>
