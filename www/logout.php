<?php
  require_once("../libs/config.inc.php");
  require_once("../libs/autoload.lib.php");

  $h = HTTP::getInstance();
  $h->parseUrl();
  $h->sanitizeArray($_POST);
  $h->sanitizeArray($_GET);

  $m = mysqlCM::getInstance();
  if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
  }

  $lm = loginCM::getInstance();
  $lm->startSession();
  $lm->logout();

  $center = new Template("./tpl/logout.tpl");
  $page = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");

  $page->set("head", $head);
  $page->set("menu", $menu);
  $page->set("foot", $foot);
  $page->set("content", $center);
  echo $page->fetch();
  $m->disconnect();
?>
