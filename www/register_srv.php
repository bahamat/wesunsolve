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
   if (!isset($_POST['sname']) || empty($_POST['sname'])) {
     $error = "Server name not entered, this is a mandatory field...";
     $content = new Template("./tpl/register_srv.tpl");
     $content->set("error", $error);
     goto screen;
   }
   $s = new Server();
   $s->id_owner = $lm->o_login->id;
   $s->name = $_POST['sname'];
   if (!$s->fetchFromFields(array("id_owner", "name"))) {
     $error = "This server is already present into your account!";
     $content = new Template("./tpl/register_srv.tpl");
     $content->set("sname", $s->name);
     $content->set("error", $error);
     goto screen;
   }
   $s->id_owner = $lm->o_login->id;
   $s->name = $_POST['sname'];
   $s->comment = $_POST['comment'];
   $s->insert();
   $content = new Template("./tpl/message.tpl");
   $content->set("msg", "Thanks for adding the server, please check now your main panel...");
   IrcMsg::add("[WWW] User added server: ".$s->name." to his account (".$lm->o_login->username.")", MSG_ADM);

 } else {
   $content = new Template("./tpl/register_srv.tpl");
 }

screen:
  $index->set("content", $content);
  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  echo $index->fetch();
?>
