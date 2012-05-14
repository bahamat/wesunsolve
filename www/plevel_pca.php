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

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $index->set("head", $head);
 $index->set("foot", $foot);
 $index->set("menu", $menu);

 if (!isset($lm->o_login) || !$lm->o_login) {
   $content = new Template("./tpl/denied.tpl");
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

 if ($lm->o_login->id != $s->id_owner && !$lm->o_login->is_admin && $lm->o_login->checkServerAccess($s) === null) {
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
   if (isset($_GET['patchdiag']) && !empty($_GET['patchdiag'])) {
     $pdiag = new Patchdiag($_GET['patchdiag']);
     $pdiag->fetchFromId();
   } else { 
     $pdiag = null;
   }
   $pl->fetchFromId();
   $pl->fetchPatches(1);
   $pl->fetchSRV4Pkgs(1);
   $pl->fetchServer();
   if (!$pdiag) {
     $pdiag = Patchdiag::fetchLatest();
   }
   $content = new Template("./tpl/plevel_pca.tpl");
   $pl->parsePCA(null, $pdiag);
   $content->set('pl', $pl);
   $content->set('s', $s);
   $content->set('pdiag', $pdiag);
 } else {
   $s->fetchPLevels();
   $content = new Template("./tpl/plevel_list.tpl");
   $content->set("plevels", $s->a_plevel);
   $content->set("s", $s);
 }
screen:
  $index->set("content", $content);
  echo $index->fetch();
?>
