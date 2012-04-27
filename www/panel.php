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
   $lo->fetchData();
   $lo->fetchUCLists();
   $lo->fetchUReports();
   $rpp = $config['serversPerPage'];
   $val = $lo->data('serversPerPage');
   if ($val) $rpp = $val;
   $nb = $lo->countServers();
   if (isset($_POST['page']) && !empty($_POST['page'])) {
   $page = $_POST['page'];
   } else if (isset($_GET['page']) && !empty($_GET['page'])) {
     $page = $_GET['page'];
   } else {
     $page = 1;
   }
   $nb_page = 0;
   if($nb) {
     $nb_page = $nb / $rpp;
     $nb_page = round($nb_page,0);
   }
   if(isset($page) && !empty($page)) {
     if (preg_match("/[0-9]*/", $page)) {
       $start = ($page - 1) * $rpp;
       if ($start >= $nb) { /* could not start after the number of results... */
         $start = 0;
       }
     } else {
       $start = 0;
     }
   } else { /* otherwise start from scratch */
     $start = 0;
   }
   $lo->fetchServers($start, $rpp);
   $str = '/panel';

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

   $content->set("start", $start);
   $content->set("nb", $nb);
   $content->set("rpp", $rpp);
   $content->set("str", $str);
   $content->set("pagination", HTTP::pagine($page, $nb_page, $str."/page/%d"));

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
