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

 if (!isset($_GET['id'])) {
   die("Cannot be called as-is");
 }

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

 header("Content-type: text/plain");
 foreach($patch->a_files as $f) {
   echo $f->name."\n";
 }

?>
