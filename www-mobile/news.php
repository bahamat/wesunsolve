<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
// $lm = loginCM::getInstance();
// $lm->startSession();

// $h = HTTP::getInstance();
// $h->parseUrl();

  $news = array();
  $table = "`rss_news`";
  $index = "`id`";
  $where = " ORDER BY `date` DESC LIMIT 0,15";

  if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new News($t['id']);
      $g->fetchFromId();
      array_push($news, $g);
    }
  }

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $head->set("title", "We Sun Solve! - Website news");
 $head->set("paget", "Site news");
 $foot = new Template("./tpl/foot.tpl");
 $content = new Template("./tpl/news.tpl");
 $content->set("news", $news);

 $index->set("head", $head);
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();
?>
