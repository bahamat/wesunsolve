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
 
 $r->delete();
 IrcMsg::add("[WWW] ".$lm->o_login->username." removed report from his account", MSG_ADM);

 $content = new Template("./tpl/message.tpl");
 $content->set("msg", "Report has been removed.");

screen:
  $back = array('name' => 'Panel', 'href' => '/panel');
  $content->set('back', $back);
  $index->set("content", $content);
  echo $index->fetch();
?>
