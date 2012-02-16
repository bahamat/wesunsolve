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

  $ips = array();
  $table = "`ips`";
  $index = "`id`";

  if ($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where))
  {
    foreach($idx as $t) {
      $g = new IPS($t['id']);
      $g->fetchFromId();
      $g->fetchPkgs(0);
      array_push($ips, $g);
    }
  }

 $head_add = '';
 $title = 'Monitored IPS Repositories';

  $index = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $head->set("title", $title);
  $head->set("head_add", $head_add);
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $foot->set("start_time", $start_time);
  $content = new Template("./tpl/lips.tpl");
  $str = "/lips";
  $content->set("ips", $ips);
  $content->set("title", $title);
  $content->set("str", $str);
  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  $index->set("content", $content);
  echo $index->fetch();
?>
