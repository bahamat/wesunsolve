<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
 }

 $h = HTTP::getInstance();
 $h->parseUrl();
 $h->sanitizeArray($_POST);
 $h->sanitizeArray($_GET);

 $lm = loginCM::getInstance();
 $lm->startSession();

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $index->set("head", $head);
 $index->set("foot", $foot);
 $index->set("menu", $menu);

 if (!isset($lm->o_login) || !$lm->o_login) {
   $content = new Template("./tpl/error.tpl");
   $error = "You should be logged in to access this page...";
   $content->set("error", $error);
   goto screen;
 }

 if (!isset($_GET['s']) || empty($_GET['s'])) {
   $content = new Template("./tpl/error.tpl");
   $error = "No id of server specified.";
   $content->set("error", $error);
   goto screen;
 }
 $s = new Server();
 $s->id = $_GET['s'];
 if ($s->fetchFromId()) {
   $content = new Template("./tpl/error.tpl");
   $error = "Server not found in database";
   $content->set("error", $error);
   goto screen;
 }

 if ($lm->o_login->id != $s->id_owner) {
   $content = new Template("./tpl/error.tpl");
   $error = "You have no rights to view this server!";
   $content->set("error", $error);
   goto screen;
 }

 if (isset($_GET['p']) || !empty($_GET['p'])) {
   $pl = new PLevel();
   $pl->id = $_GET['p'];
   if ($pl->fetchFromId()) {
     $content = new Template("./tpl/error.tpl");
     $error = "Patch level not found in database";
     $content->set("error", $error);
     goto screen;
   }
   if ($pl->id_server != $s->id) {
     $content = new Template("./tpl/error.tpl");
     $error = "You can't view this patch level !";
     $content->set("error", $error);
     goto screen;
   }
   $pl->fetchFromId();
   $pl->fetchPatches(1);
   foreach($pl->a_patches as $p) {
     $pl->delPatch($p);
   }
   $pl->delete();
   $content = new Template("./tpl/message.tpl");
   $content->set("msg", "Patch level has been removed.");
 } else {
   $content = new Template("./tpl/error.tpl");
   $error = "PLevel id not provided";
   $content->set("error", $error);
   goto screen;

 }
screen:
  $index->set("content", $content);
  echo $index->fetch();
?>
