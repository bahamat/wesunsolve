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
  // $lo->fetchUCLists();
   if ($lo) {
     $lo->fetchData();
     $val = $lo->data('patchPerPage');
     if ($val) $rpp = $val;
   }
 }

 if (isset($_POST['page']) && !empty($_POST['page'])) {
   $page = $_POST['page'];
 } else if (isset($_GET['page']) && !empty($_GET['page'])) {
   $page = $_GET['page'];
 } else {
   $page = 1;
 }
 $nb_page = 0;

 $where = " ORDER BY `pkg`.`pstamp` DESC";
 $bad = 0;
 $sec = 0;


  $pkgs = array();
  $table = "`pkg`";
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
  $where .= " LIMIT $start,$rpp";

  if ($nb && ($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new Pkg($t['id']);
      $g->fetchFromId();
      array_push($pkgs, $g);
    }
  }

 $head_add = '';
 if (!$bad) {
   $head_add = '<link rel="alternate" type="application/rss+xml" title="Last S11 Packages" href="http://wesunsolve.net/rss/s11pkg" />';
 }

 $title = 'Last released Solaris 11 Packages - results from '.$start.' to '.($start+$rpp).' (of '.$nb.')';

  $index = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $head->set("title", $title);
  $head->set("head_add", $head_add);
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $foot->set("start_time", $start_time);
  $content = new Template("./tpl/lpkg.tpl");
  $str = "/lpkg";
  $content->set("pkgs", $pkgs);
  $content->set("start", $start);
  $content->set("nb", $nb);
  $content->set("rpp", $rpp);
  $content->set("title", $title);
  $content->set("str", $str);
  $content->set("pagination", HTTP::pagine($page, $nb_page, $str."/page/%d"));
//  if (isset($lo) && $lo) {
 //   $content->set("l", $lo);
  //  $head_add = "<script type=\"text/javascript\" src=\"/js/ax_main.js\"></script>";
   // $head_add .= "<script type=\"text/javascript\" src=\"/js/ax_patch.js\"></script>";
//    $head->set("head_add", $head_add);
 // }

  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  $index->set("content", $content);
  echo $index->fetch();
?>
