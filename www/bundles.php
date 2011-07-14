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
 
  $bundles = array();
  $table = "`bundles`";
  $index = "`id`";
  $where = " ORDER BY `lastmod` DESC, `synopsis` DESC";

  if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new Bundle($t['id']);
      $g->fetchFromId();
      array_push($bundles, $g);
    }
  }

 $head_add = '<link rel="alternate" type="application/rss+xml" title="Last Bundles" href="http://wesunsolve.net/rss/bundles" />';
 $title = 'Latest updates of bundles for Solaris';

  $index = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $head->set("title", $title);
  $head->set("head_add", $head_add);
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $foot->set("start_time", $start_time);
  $content = new Template("./tpl/bundles.tpl");
  $content->set("bundles", $bundles);

  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  $index->set("content", $content);
  echo $index->fetch();
?>
