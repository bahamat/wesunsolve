<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

  $h = HTTP::getInstance();
  $h->parseUrl();
  $h->sanitizeArray($_POST);
  $h->sanitizeArray($_GET);

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $index->set("head", $head);
 $index->set("foot", $foot);
 $index->set("menu", $menu);

 if (!isset($lm->o_login) || !$lm->o_login) {
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 }

 if (isset($_POST['diff'])) {

   $content = new Template("./tpl/srvDiffResult.tpl");
   if (!isset($_POST['is']) || empty($_POST['is'])) {
     $content = new Template("./tpl/error.tpl");
     $error = "No id of list specified for patch level source.";
     $content->set("error", $error);
     goto screen;
   }
  
   $pd = new PLevel();
   $pd->id = $_POST['id'];
   if ($pd->fetchFromId()) {
     $content = new Template("./tpl/error.tpl");
     $error = "Patch level destination not found in database";
     $content->set("error", $error);
     goto screen;
   }
   $pd->fetchServer();
  
   if ($pd->o_server->id_owner != $lm->o_login->id) {
     $content = new Template("./tpl/error.tpl");
     $error = "You have no rights to view this patch level source!";
     $content->set("error", $error);
     goto screen;
   }
   $pd->fetchFromId();
   $pd->fetchPatches(2);
  
   $ps = new PLevel();
   $ps->id = $_POST['is'];
   if ($ps->fetchFromId()) {
     $content = new Template("./tpl/error.tpl");
     $error = "Patch level source not found in database";
     $content->set("error", $error);
     goto screen;
   }
   $ps->fetchServer();
  
   if ($ps->o_server->id_owner != $lm->o_login->id) {
     $content = new Template("./tpl/error.tpl");
     $error = "You have no rights to view this patch level source!";
     $content->set("error", $error);
     goto screen;
   }
   $ps->fetchFromId();
   $ps->fetchPatches(2);

   $plist1 = &$ps->a_patches;
   $plist2 = &$pd->a_patches;

   $curr = 0;
   $delta = array();

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
   $content->set("ps", $ps);
   $content->set("pd", $pd);
   $content->set("delta", $delta);


 } else {

   $lm->o_login->fetchServers();
   $content = new Template("./tpl/srvDiff.tpl");
   $content->set("as", $lm->o_login->a_servers);
 }

screen:
  $index->set("content", $content);
  echo $index->fetch();
?>
