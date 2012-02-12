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

 if (!$lm->isLogged) {
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 }
 $lo = $lm->o_login;
 $lo->fetchMList();
 $lo->fetchData();
 $mlist = array();
 $table = "`mlist`";
 $index = "`id`";
 $where = "";

 if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
 {
   foreach($idx as $t) {
     $g = new MList($t['id']);
     $g->fetchFromId();
     array_push($mlist, $g);
   }
 }

 if (isset($_POST['save'])) { 
   $msg = "";
   if (isset($_POST['ml'])) {
     foreach ($mlist as $m) {
       if (isset($_POST['ml'][$m->id]) && $_POST['ml'][$m->id]) {
         if (!$lo->isMList($m)) {
           $lo->addMList($m);
	   IrcMsg::add("[WWW] ".$lo->username." subscribed to mail report ".$m->name, MSG_ADM);
	   $msg .= "Subscribed to ".$m->name."<br/>\n";
 	 }
       } else {
	 if ($lo->isMList($m)) {
	   $lo->delMList($m);
	   IrcMsg::add("[WWW] ".$lo->username." unsubscribed to mail report ".$m->name, MSG_ADM);
	   $msg .= "Unsubscribed to ".$m->name."<br/>\n";
 	 }
       }
     }
   } else { /* unsubscribe from everything */
     foreach ($lo->a_mlists as $m) {
       $lo->delMList($m);
       $msg .= "Unsubscribed to ".$m->name."<br/>\n";
     }
   }
 }
 $content = new Template("./tpl/mlist.tpl");
 $content->set("login", $lo->username);
 $content->set("lo", $lo);
 $content->set("mlists", $mlist);
 if (isset($msg)) $content->set("msg", $msg);

screen:
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
