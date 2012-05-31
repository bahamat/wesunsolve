<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
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
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 }

 if (isset($_GET['pl']) && isset($_GET['p']) &&
     !empty($_GET['pl']) && !empty($_GET['p'])) {
   $pl = new PLevel($_GET['pl']);
   $pd = new Patchdiag($_GET['p']);
   if ($pl->fetchFromId()) {
     $content = new Template("./tpl/error.tpl");
     $error = "Specified server patch level not found.";
     $content->set("error", $error);
     goto screen;
   }
   if ($pd->fetchFromId()) {
     $content = new Template("./tpl/error.tpl");
     $error = "Specified patchdiag not found.";
     $content->set("error", $error);
     goto screen;
   }
   $pl->fetchServer();
   if ($lm->o_login->id != $pl->o_server->id_owner && !$lm->o_login->is_admin && $lm->o_login->checkServerAccess($pl->o_server) === null) {
     $content = new Template("./tpl/error.tpl");
     $content->set("error", "You don't own this patch level, you little hacker!");
     goto screen;
   }
   $ur = new UReport();
   $ur->id_owner = $lm->o_login->id;
   $ur->id_plevel = $pl->id;
   $ur->pdiag_delay = $pd->age();
   $ur->lastrun = 0;
   $ur->run();
   $ur->sendMail(true);
   IrcMsg::add("[WWW] ".$lm->o_login->username." sent $r", MSG_ADM);
   $content = new Template("./tpl/message.tpl");
   $content->set("msg", "Report has been sent.");
   goto screen;
 }

 if (!isset($_GET['r']) || empty($_GET['r'])) {
   $content = new Template("./tpl/error.tpl");
   $error = "No report id specified.";
   $content->set("error", $error);
   goto screen;
 }
 $r = new UReport($_GET['r']);
 if ($r->fetchFromId()) {
   $content = new Template("./tpl/error.tpl");
   $error = "Report not found in database";
   $content->set("error", $error);
   goto screen;
 }

 if ($lm->o_login->id != $r->id_owner) {
   $content = new Template("./tpl/error.tpl");
   $error = "You have no rights to view this report!";
   $content->set("error", $error);
   goto screen;
 }
 
 $r->run();
 $r->sendMail(true);
 IrcMsg::add("[WWW] ".$lm->o_login->username." sent $r", MSG_ADM);

 $content = new Template("./tpl/message.tpl");
 $content->set("msg", "Report has been sent.");

screen:
  $back = array('name' => 'Panel', 'href' => '/panel');
  $content->set('back', $back);
  $index->set("content", $content);
  echo $index->fetch();
?>
