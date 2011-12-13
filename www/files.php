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

 if (!isset($_GET['id']) && !isset($_GET['pid'])) {
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
   $patch->fetchFiles();
   $p = $patch;

 } else if (isset($_GET['pid'])) {
   $id = mysql_escape_string($_GET['pid']);
   if (!preg_match("/[0-9]{1,11}/", $id)) {
     die("Malformed package ID");
   }
   $p = new Pkg($id);
   $p->fetchFiles();
 }

 header("Content-type: text/plain");
 foreach($p->a_files as $f) {
   echo $f->name."\n";
 }

?>
