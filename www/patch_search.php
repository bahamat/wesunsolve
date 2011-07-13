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

 $title = "We Sun Solve - Advanced patch search form";

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $head->set("title", $title);
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $content = new Template("./tpl/patch_search.tpl");

 $index->set("head", $head);
 $index->set("menu", $menu);
 $index->set("foot", $foot);
 $index->set("content", $content);
 echo $index->fetch();
?>
