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

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 //$head->set("title", $title);
 $head->set("title", 'Tool for comparing two patches level');
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $content = new Template("./tpl/compare.tpl");

  if (isset($_GET['form']) && $_GET['form'] == 1) {
   $plist1 = Patch::parseList($_POST['plist1'], $_POST['format1']);
   $plist2 = Patch::parseList($_POST['plist2'], $_POST['format2']);
   $curr = 0;
   $delta = array();
   foreach($plist1 as $p1) { $p1->fetchFromId(); }
   foreach($plist2 as $p2) { $p2->fetchFromId(); }

   foreach($plist1 as $p1) {
     if (!isset($delta[$p1->patch])) {
       $delta[$p1->patch] = array();
       $delta[$p1->patch][1] = null;
       $delta[$p1->patch][2] = null;
     } else {
       if ($delta[$p1->patch][1]->revision >= $p1->revision) {
	 continue;
       } else {
         $delta[$p1->patch][1] = $p1;
       }
     }
     if ($delta[$p1->patch][1] == null) {
       $delta[$p1->patch][1] = $p1;
     }
     foreach($plist2 as $p2) {
       if ($p1->patch == $p2->patch) {
         if (isset($delta[$p1->patch][2])) {
           if ($delta[$p1->patch][2]->revision >= $p2->revision) {
	     continue;
	   } else {
             $delta[$p1->patch][2] = $p2;
	   }
	 }
         $delta[$p1->patch][2] = $p2;
       }
     }
   }
   foreach($plist2 as $p2) {
     if (!isset($delta[$p2->patch])) {
       $delta[$p2->patch] = array();
       $delta[$p2->patch][1] = null;
       $delta[$p2->patch][2] = null;
     } else {
       if ($delta[$p2->patch][2]->revision >= $p2->revision) {
         continue;
       } else {
         $delta[$p2->patch][2] = $p2;
       }
     }
     if ($delta[$p2->patch][2] == null) {
       $delta[$p2->patch][2] = $p2;
     }
     foreach($plist1 as $p1) {
       if ($p1->patch == $p2->patch) {
         if (isset($delta[$p1->patch][1])) {
           if ($delta[$p1->patch][1]->revision >= $p1->revision) {
	     continue;
	   } else {
             $delta[$p2->patch][1] = $p1;
	   }
         }
         $delta[$p2->patch][1] = $p1;
       }
     }
   } 
   foreach($delta as $k => $p) {
     $delta[$k]['latest'] = Patch::pLatest($k);
     if($delta[$k]['latest'])
       $delta[$k]['latest']->fetchFromId();
   }
   $content->set("p", $p);
   $content->set("delta", $delta);
 }
 $index->set("head", $head);
 $index->set("menu", $menu);
 $index->set("foot", $foot);
 $index->set("content", $content);
 echo $index->fetch();
?>
