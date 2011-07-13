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
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $foot->set("start_time", $start_time);

  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);

 if (isset($_GET['form']) && $_GET['form'] == 1) {
   $plist = Patch::parseList($_POST['plist'], $_POST['format']);
   if (!$plist) die("Error in format submitted");
   $curr = 0;
   foreach($plist as $p) {
    $p->fetchFromId();
    $p->fetchAll(3);
   }
   $content = new Template("./tpl/plist_report.tpl");
   $content->set("plist", $plist);
   $content->set("curr", $curr);
 } else {
   $content = new Template("./tpl/plist_form.tpl");
 }
  $index->set("content", $content);
  echo $index->fetch();
?>
