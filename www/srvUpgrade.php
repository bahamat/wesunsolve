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
 $index->set("head", $head);
 $index->set("foot", $foot);
 $index->set("menu", $menu);

 if (!isset($lm->o_login) || !$lm->o_login) {
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 }

 $lm->o_login->fetchServers();
 $content = new Template("./tpl/srvUpgrade.tpl");
 $content->set("as", $lm->o_login->a_servers);
screen:
  $index->set("content", $content);
  echo $index->fetch();
?>
