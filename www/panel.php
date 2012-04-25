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
   $lo->fetchUCLists();
   $lo->fetchUReports();

   foreach($lo->a_uclists as $l) $l->fetchPatches(0);
   foreach($lo->a_servers as $srv) $srv->fetchPLevels(0);
  
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
   $content->set("uclists", $lo->a_uclists);
   $content->set("ureports", $lo->a_ureports);
   $content->set("news", $news);
   $content->set('lvp', Patch::getLastviewed($lo));
   $content->set('lvb', Bugid::getLastviewed($lo));
 }

 $error = '';
 if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
   $error .= 'The connection is not secured, consider using <a href="https://wesunsolve.net/panel">HTTPS</a> to avoid possible eavesdropping.<br/>';
 }

 
 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 
 $index->set("head", $head);
 $index->set("menu", $menu);
 $head->set("title", "Panel for registered users of We Sun Solve!");
 if (isset($error) && !empty($error)) {
   $content->set('error', $error);
 }
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();
?>
