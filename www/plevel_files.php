<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

  $h = HTTP::getInstance();
  $h->parseUrl();
  $h->sanitizeArray($_POST);
  $h->sanitizeArray($_GET);

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 if (!isset($lm->o_login) || !$lm->o_login) {
   $error = "You should be logged in to access this page...";
   goto screen;
 }

 if (!isset($_GET['s']) || empty($_GET['s'])) {
   $error = "No id of server specified.";
   goto screen;
 }
 $s = new Server();
 $s->id = $_GET['s'];
 if ($s->fetchFromId()) {
   $error = "Server not found in database";
   goto screen;
 }

 if ($lm->o_login->id != $s->id_owner) {
   $error = "You have no rights to view this server!";
   goto screen;
 }

 if (isset($_GET['p']) || !empty($_GET['p'])) {
   $pl = new PLevel();
   $pl->id = $_GET['p'];
   if ($pl->fetchFromId()) {
     $error = "Patch level not found in database";
     goto screen;
   }
   if ($pl->id_server != $s->id) {
     $error = "You can't view this patch level !";
     goto screen;
   }
   $pl->fetchFromId();
   $pl->fetchPatches(0);
   header("Content-type: text/plain");
   foreach($pl->a_patches as $p) {
     $p->fetchFiles();
     foreach($p->a_files as $f) {
       echo $p->name().":".$f->name."\n";
     }
     flush();
   }
 } else {
   $error = "not found.";
   goto screen;
 }
screen:
 die($error);
?>
