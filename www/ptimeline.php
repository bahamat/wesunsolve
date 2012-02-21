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

 if (isset($_GET['id']) && !empty($_GET['id'])) {
   $id = $_GET['id'];
 } else if (isset($_GET['pid']) && !empty($_GET['pid'])) {
   $pid = $_GET['pid'];
   if (preg_match('/([0-9]{6})-([0-9]{2})/', $pid, $matches)) {
     $po = new Patch($matches[1],$matches[2]);
     if ($po->fetchFromId()) {
       $content = new Template('./tpl/error.tpl');
       $content->set("error", 'No argument provided...');
       goto screen;
     }
   } else if (preg_match('/([0-9]{6})/', $pid, $matches)) {
     $pid = $matches[1];
     $pl = Patch::pLatest($pid);
     if (!$pl) {
       $content = new Template('./tpl/error.tpl');
       $content->set("error", 'Patch ID not found...');
       goto screen;
     }
     if (isset($_GET['r'])) {
       $pl->fetchObsolated();
       $pl->fetchObsby();
       $pids = "";
       if ($pl->o_obsby) {
         $pids .= "'".$pl->o_obsby->patch."'";
       }
       foreach($pl->a_obso as $op) {
         if (!empty($pids)) $pids.= ',';
         $pids .= "'".$op->patch."'";
       }
     }
   } else {
     $content = new Template('./tpl/error.tpl');
     $content->set("error", 'Wrong argument provided...');
     goto screen;
   }
 } else {
   $content = new Template('./tpl/error.tpl');
   $content->set("error", 'No argument provided...');
   goto screen;
 }
 
  $table = "`p_timeline`";
  $index = "`id`";
  $cindex = "COUNT(`id`)";
  $where = "";
  if (isset($id)) {
    $where .= " WHERE `id_patchdiag`='$id'";
  } else if (isset($po)) {
    $where .= " WHERE `id_patch`='".$po->patch."' AND `id_revision`='".$po->revision."'";
  } else if (isset($pid)) {
    $where .= " WHERE `id_patch`='".$pid."'";
  } else {
   $content = new Template('./tpl/error.tpl');
   $content->set("error", 'No argument provided...');
   goto screen;
  }
  if (isset($pids) && !empty($pids)) {
    $where .= " OR `id_patch` IN (".$pids.")";
  }
  $where .= " ORDER BY `when` DESC, `id_patch` ASC, `id_revision` ASC";
  $it = new mIterator("pTimeline", $index, $table, $where, $cindex);

  $title = 'Patches timeline of patchdiag.xref file';
  $head_add = "";

  $content = new Template("./tpl/ptimeline.tpl");
  $content->set("pts", $it);

screen:
  $index = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $head->set("title", $title);
  $head->set("head_add", $head_add);
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $foot->set("start_time", $start_time);
  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  $index->set("content", $content);
  echo $index->fetch();
?>
