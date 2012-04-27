<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $lo = $lm->o_login;
 if (!$lm->isLogged || !$lo->is_admin || !isset($_GET['i']) || empty($_GET['i'])) {
   $content = new Template("./tpl/denied.tpl");
 } else {
   $lid = $_GET['i'];
   $content = new Template("./tpl/ap_umod.tpl");
   $l = new Login($lid);
   if ($l->fetchFromId()) {
     $content = new Template("./tpl/denied.tpl");
     goto screen;
   }
   $content->set('l', $l);

 }

screen:
 
 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 
 $index->set("head", $head);
 $index->set("menu", $menu);
 $head->set("title", "User edition");
 if (isset($error) && !empty($error)) {
   $content->set('error', $error);
 }
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();
?>
