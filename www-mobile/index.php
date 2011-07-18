<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
// $lm = loginCM::getInstance();
// $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $head->set("paget", "Main");
 $foot = new Template("./tpl/foot.tpl");
 $content = new Template("./tpl/main.tpl");

 $index->set("head", $head);
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();
?>
