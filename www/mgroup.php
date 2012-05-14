<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

  $h = HTTP::getInstance();
  $h->parseUrl();
  $h->sanitizeArray($_GET);

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $head_add = "<script type=\"text/javascript\" src=\"/js/ax_main.js\"></script>";
 $head_add .= "<script type=\"text/javascript\" src=\"/js/ax_patch.js\"></script>";
 $head->set("head_add", $head_add);
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
 $s->fetchUsers();
 if (!$s->isUser($lm->o_login)) {
   $content = new Template("./tpl/error.tpl");
   $error = "You have no rights to view this group!";
   $content->set("error", $error);
   goto screen;
 }

 $s->fetchFromId();
 $s->fetchSrv();
 $error = '';
 $msg = '';

 $content = new Template("./tpl/mgroup.tpl");
 $content->set("l", $lm->o_login);
 $content->set("mgroup", $s);

screen:
  $index->set("content", $content);
  echo $index->fetch();
?>
