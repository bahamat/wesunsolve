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
   $error = "No id of comment specified.";
   $content->set("error", $error);
   goto screen;
 }
 $s = new UComment();
 $s->id = $_GET['id'];
 if ($s->fetchFromId()) {
   $content = new Template("./tpl/error.tpl");
   $error = "Comment not found in database";
   $content->set("error", $error);
   goto screen;
 }

 if ($lm->o_login->id != $s->id_login) {
   $content = new Template("./tpl/error.tpl");
   $error = "You have no rights to del this comment!";
   $content->set("error", $error);
   goto screen;
 }
 $s->delete();
 $content = new Template("./tpl/message.tpl");
 $content->set("msg", "Comment has been removed.");

screen:
  $index->set("content", $content);
  echo $index->fetch();
?>
