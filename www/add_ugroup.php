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
   if (!isset($_POST['name']) || empty($_POST['name'])) {
     $error = "Name not entered, this is a mandatory field...";
     $content = new Template("./tpl/add_ugroup.tpl");
     $content->set("error", $error);
     goto screen;
   }
   $s = new UGroup();
   $s->name = $_POST['name'];
   if (!$s->fetchFromField("name")) {
     $error = "This group already exist somewhere, please try another name!";
     $content = new Template("./tpl/add_ugroup.tpl");
     $content->set("name", $s->name);
     $content->set("error", $error);
     goto screen;
   }
   $s->id_owner = $lm->o_login->id;
   $s->name = $_POST['name'];
   $s->desc = $_POST['desc'];
   $s->insert();
   $content = new Template("./tpl/message.tpl");
   $content->set("msg", "Thanks for adding this group, please check now your group list...");
   IrcMsg::add("[WWW] User created user group: ".$s->name." to his account (".$lm->o_login->username.")", MSG_ADM);

 } else {
   $content = new Template("./tpl/add_ugroup.tpl");
 }

screen:
  $back = array('name' => 'Panel', 'href' => '/panel');
  $content->set('back', $back);
  $index->set("content", $content);
  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  echo $index->fetch();
?>
