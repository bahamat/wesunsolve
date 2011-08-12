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

 $news = array();
 $table = "`rss_news`";
 $index = "`id`";
 $where = " ORDER BY `date` DESC LIMIT 0,10";

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
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $content = new Template("./tpl/main.tpl");

 $mvp = Patch::getMostviewed();
 $mvb = Bugid::getMostviewed();
 $com = UComment::getLastComments();
 $content->set("mvp", $mvp);
 $content->set("mvb", $mvb);
 $content->set("com", $com);
 $content->set("news", $news);

 $index->set("head", $head);
 $index->set("menu", $menu);
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();
?>
