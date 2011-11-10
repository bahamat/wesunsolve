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

 $rls = array();
 $table = "`osrelease`";
 $index = "`id`";
 $where = " ORDER BY `major`,`u_number` ASC";

 if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
 {
   foreach($idx as $t) {
     $g = new OSRelease($t['id']);
     $g->fetchFromId();
     array_push($rls, $g);
   }
 }

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $head->set("head_add", $head_add);
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $index->set("head", $head);
 $index->set("foot", $foot);
 $index->set("menu", $menu);
 $content = new Template("./tpl/releases.tpl");
 $content->set("rls", $rls);

screen:
  $index->set("content", $content);
  echo $index->fetch();
?>
