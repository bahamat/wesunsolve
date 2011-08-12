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

 if (!isset($lm->o_login) || !$lm->o_login) {
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 }

 if (isset($_GET['form']) && $_GET['form'] == 1) {
   if (!isset($_POST['comment']) || empty($_POST['comment']) ||
       !isset($_POST['type']) || empty($_POST['type']) ||
       !isset($_POST['id_on']) || empty($_POST['id_on'])) {

     $error = "Mandatory field not entered, please check and try again...";
     $content = new Template("./tpl/comment.tpl");
     $content->set("error", $error);
     goto screen;
   }
   $s = new UComment();
   $s->id_login = $lm->o_login->id;
   $s->comment = $_POST['comment'];
   if (isset($_POST['is_private'])) {
     $s->is_private = 1;
   } else {
     $s->is_private = 0;
   }
   $s->type = $_POST['type'];
   $s->id_on = $_POST['id_on'];
   $s->rate = 0;
   $s->insert();
   $content = new Template("./tpl/message.tpl");
   $content->set("msg", "Thanks for adding this comment...");
 } else {
   $content = new Template("./tpl/add_comment.tpl");
   if (isset($_POST['id_on'])) {
     $id_on = $_POST['id_on'];
   } else if (isset($_GET['id_on'])) {
     $id_on = $_GET['id_on'];
   } else {
     $type = null;
   }
   if (isset($_POST['type'])) {
     $type = $_POST['type'];
   } else if (isset($_GET['type'])) {
     $type = $_GET['type'];
   } else {
     $type = null;
   }

   $content->set("id_on", $id_on);
   $content->set("type", $type);
 }

screen:
  $index->set("content", $content);
  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  echo $index->fetch();
?>
