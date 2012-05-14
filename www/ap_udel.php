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
 if ($lm->o_login) $lo = $lm->o_login;

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $index->set("head", $head);
 $index->set("foot", $foot);
 $index->set("menu", $menu);

 if (!$lm->isLogged || !$lo->is_admin || !isset($_GET['id']) || empty($_GET['id'])) {
   $content = new Template("./tpl/denied.tpl");
//   $error = "You should be logged in to access this page...";
 //  $content->set("error", $error);
   goto screen;
 }

 $l = new Login($_GET['id']);
 if ($l->fetchFromId()) {
   $content = new Template("./tpl/error.tpl");
   $content->set('error', 'User cannot be found');
   goto screen;
 }

 /* First remove patch levels */

 IrcMsg::add("[WWW] ".$lm->o_login->username." removed user: ".$l->username, MSG_ADM);
 $l->delete();

 $content = new Template("./tpl/message.tpl");
 $content->set("msg", "User has been removed.");

screen:
  $back = array('name' => 'Panel', 'href' => '/panel');
  $content->set('back', $back);
  $index->set("content", $content);
  echo $index->fetch();
?>
