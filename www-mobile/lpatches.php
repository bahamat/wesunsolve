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
 
 $rpp = $config['patchPerPage'];
 if ($lm->isLogged) {
   $lo = $lm->o_login;
   if ($lo) {
     $lo->fetchData();
     $val = $lo->data('patchPerPage');
     if ($val) $rpp = $val;
   }
 }

 if (isset($_POST['start']) && !empty($_POST['start'])) {
   $start = $_POST['start'];
 } else if (isset($_GET['start']) && !empty($_GET['start'])) {
   $start = $_GET['start'];
 }

 $where = " WHERE `releasedate`!='' ORDER BY `patches`.`releasedate` DESC,`patches`.`patch` DESC,`patches`.`revision` DESC";
 $bad = 0;
 $sec = 0;

 if ((isset($_POST['sec']) && !empty($_POST['sec'])) ||
     (isset($_GET['sec']) && !empty($_GET['sec']))) {
   $sec = 1;
   $where = " WHERE `pca_sec`='1' AND `releasedate`!='' ORDER BY `releasedate` DESC,`patches`.`patch` DESC,`patches`.`revision` DESC";
 }

 if ((isset($_POST['bad']) && !empty($_POST['bad'])) ||
     (isset($_GET['bad']) && !empty($_GET['bad']))) {
   $where = " WHERE `releasedate`!='' AND (`pca_bad`='1' OR status='OBSOLETE') ORDER BY `releasedate` DESC,`patches`.`patch` DESC,`patches`.`revision` DESC";
   $bad = 1;
 }

  $patches = array();
  $table = "`patches`";
  $index = "`patch`, `revision`";
  $icount = "count(`patch`) as c";

  if (($idx = mysqlCM::getInstance()->fetchIndex($icount, $table, $where)))
  {
    $nb = 0;
    if (isset($idx[0]) && isset($idx[0]['c'])) {
      $nb = $idx[0]['c'];
    }
  }

  /* check if url is saying where to start... */
  if(isset($start) && !empty($start)) {

    if (preg_match("/[0-9]*/", $start)) {
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
  $where .= " LIMIT $start,$rpp";

  if ($nb && ($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new Patch($t['patch'], $t['revision']);
      $g->fetchFromId();
      array_push($patches, $g);
    }
  }

 $title = 'Latest Solaris released patches - results from '.$start.' to '.($start+$rpp);
 if ($bad) {
   $title = 'Latest Solaris invalidated patches - results from '.$start.' to '.($start+$rpp);
 }
 if ($sec) {
   $title = 'Latest Solaris security released patches - results from '.$start.' to '.($start+$rpp);
 }

  $index = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $head->set("title", $title);
  $head->set("paget", "Last patches");
  $foot = new Template("./tpl/foot.tpl");
  $content = new Template("./tpl/lpatches.tpl");
  $str = "/lpatches";
  if ($bad) {
    $str.="/bad/1";
  }
  if ($sec) {
    $str.="/sec/1";
  }
  $content->set("patches", $patches);
  $content->set("start", $start);
  $content->set("nb", $nb);
  $content->set("rpp", $rpp);
  $content->set("str", $str);
  $content->set("title", $title);

  $index->set("head", $head);
  $index->set("foot", $foot);
  $index->set("content", $content);
  echo $index->fetch();
?>
