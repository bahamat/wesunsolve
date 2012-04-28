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
 
 $table = "`pkg`";
 $where = "";

 if (isset($_GET['id']) && !strcmp($_GET['id'], 'any')) {
   $ips = null;
 } else if (isset($_GET['id']) && !empty($_GET['id'])) {
   $ips = new IPS($_GET['id']);
   if ($ips->fetchFromId()) {
     $content = new Template('./tpl/error.tpl');
     $content->set('error', 'Invalid IPS repository ID');
     goto screen;
   }
   $table .= ", `jt_pkg_ips` jt";
   $where .= " WHERE jt.id_pkg=pkg.id AND `jt`.`id_ips`='".$ips->id."'";
 } else {
   $ips = new IPS();
   $ips->name = $config['ipslist']['default'];
   if ($ips->fetchFromField('name')) {
     $content = new Template('./tpl/error.tpl');
     $content->set('error', 'Cannot find default IPS repository');
     goto screen;
   }
   $table .= ", `jt_pkg_ips` jt";
   $where .= " WHERE jt.id_pkg=pkg.id AND `jt`.`id_ips`='".$ips->id."'";
 }

 $pkgs = array();
 $index = "`id`";
 $icount = "count(`id`) as c";
 $where .= " ORDER BY `pkg`.`pstamp` DESC";

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
      $g->fetchIPS();
      array_push($pkgs, $g);
    }
  }

 $head_add = '';
 $head_add = '<link rel="alternate" type="application/rss+xml" title="Latest S11 Packages" href="http://wesunsolve.net/rss/s11pkg" />';

 $str = "/ips/id/".$ips->id;
 $content = new Template("./tpl/ips.tpl");

 if ($ips) {
   $ips->fetchPkgs(0);
   $title = 'Latest '.$ips->desc.' Packages - results from '.$start.' to '.($start+$rpp).' (of '.$nb.')';
   $content->set("ips", $ips);
 } else {
   $title = 'Latest Packages - results from '.$start.' to '.($start+$rpp).' (of '.$nb.')';
 }
 $content->set("pkgs", $pkgs);
 $content->set("start", $start);
 $content->set("nb", $nb);
 $content->set("rpp", $rpp);
 $content->set("title", $title);
 $content->set("str", $str);
 $content->set("pagination", HTTP::pagine($page, $nb_page, $str."/page/%d"));


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
