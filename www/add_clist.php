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
//   $error = "You should be logged in to access this page...";
 //  $content->set("error", $error);
   goto screen;
 }

 if (isset($_GET['form']) && $_GET['form'] == 1) {
   if (!isset($_POST['name']) || empty($_POST['name'])) {
     $error = "List name not entered, this is a mandatory field...";
     $content = new Template("./tpl/add_clist.tpl");
     $content->set("error", $error);
     goto screen;
   }
   $s = new UCList();
   $s->id_login = $lm->o_login->id;
   $s->name = $_POST['name'];
   if (!$s->fetchFromFields(array("id_login", "name"))) {
     $error = "This List is already present into your account!";
     $content = new Template("./tpl/add_clist.tpl");
     $content->set("name", $s->name);
     $content->set("error", $error);
     goto screen;
   }
   $s->id_login = $lm->o_login->id;
   $s->name = $_POST['name'];
   $s->insert();
   $content = new Template("./tpl/message.tpl");
   $content->set("msg", "Thanks for adding the list, please check now your main panel...");
 } else {
   $content = new Template("./tpl/add_clist.tpl");
 }

screen:
  $index->set("content", $content);
  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  echo $index->fetch();
?>
