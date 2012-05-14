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

 if (!isset($_GET['id']) || empty($_GET['id'])) {
   $content = new Template("./tpl/error.tpl");
   $error = "No id of group specified.";
   $content->set("error", $error);
   goto screen;
 }
 $s = new UGroup();
 $s->id = $_GET['id'];
 if ($s->fetchFromId()) {
   $content = new Template("./tpl/error.tpl");
   $error = "Group not found in database";
   $content->set("error", $error);
   goto screen;
 }

 if ($lm->o_login->id != $s->id_owner) {
   $content = new Template("./tpl/error.tpl");
   $error = "You have no rights to view this group!";
   $content->set("error", $error);
   goto screen;
 }
 
 IrcMsg::add("[WWW] ".$lm->o_login->username." removed user group $s from his account", MSG_ADM);
 $s->delete();

 $content = new Template("./tpl/message.tpl");
 $content->set("msg", "Server has been removed.");

screen:
  $back = array('name' => 'Panel', 'href' => '/panel');
  $content->set('back', $back);
  $index->set("content", $content);
  echo $index->fetch();
?>
