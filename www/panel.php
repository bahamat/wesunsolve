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
 if (!$lm->isLogged) {
   $content = new Template("./tpl/denied.tpl");
 } else {
   $lo->fetchServers();
   foreach($lo->a_servers as $srv) $srv->fetchPLevels();
  
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
   $content = new Template("./tpl/panel.tpl");
   $content->set("servers", $lo->a_servers);
   $content->set("news", $news);
 }

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);

 $index->set("head", $head);
 $index->set("menu", $menu);
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();
?>
