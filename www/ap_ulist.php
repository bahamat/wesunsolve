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

 $rpp = 100;

 $lo = $lm->o_login;
 if (!$lm->isLogged || !$lo->is_admin) {
   $content = new Template("./tpl/denied.tpl");
 } else {
   $content = new Template("./tpl/ap_ulist.tpl");
   $logins = array();

   if (isset($_POST['page']) && !empty($_POST['page'])) {
     $page = $_POST['page'];
   } else if (isset($_GET['page']) && !empty($_GET['page'])) {
     $page = $_GET['page'];
   } else {
     $page = 1;
   }
   $nb_page = 0;

   $patches = array();
   $table = "`login`";
   $index = "`id`";
   $icount = "count(`id`) as c";
   if (($idx = mysqlCM::getInstance()->fetchIndex($icount, $table, $where)))
   {
     $nb = 0;
     if (isset($idx[0]) && isset($idx[0]['c'])) {
       $nb = $idx[0]['c'];
     }
   }
   if($nb) {
     $nb_page = $nb / $rpp;
     $nb_page = round($nb_page,0);
   }
   /* check if url is saying where to start... */
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
 
   if ($start < 0) $start = 0;
   $where = " ORDER BY `username` ASC ";
   $where .= " LIMIT $start,$rpp";
 
   if ($nb && ($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
   {
     foreach($idx as $t) {
       $g = new Login($t['id']);
       $g->fetchFromId();
       array_push($logins, $g);
     }
   }

   $content->set('logins', $logins);
   $content->set("start", $start);
   $content->set("nb", $nb);
   $content->set("rpp", $rpp);
   $str = '/ap_ulist';
   $content->set('str', $str);
   $content->set("pagination", HTTP::pagine($page, $nb_page, $str."/page/%d"));



 }
 
 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 
 $index->set("head", $head);
 $index->set("menu", $menu);
 $head->set("title", "Admin panel of We Sun Solve!");
 if (isset($error) && !empty($error)) {
   $content->set('error', $error);
 }
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();
?>
