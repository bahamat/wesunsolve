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
 $head->set("title", "Feedback form for We Sun Solve!");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);

 if (isset($_GET['form']) && $_GET['form'] == 1) {
   if (!empty($_POST['email']) && !empty($_POST['details'])) {
     $email = addslashes($_POST['email']);
     $name = addslashes($_POST['nom']);
     $details = addslashes($_POST['details']);
     mail("tgo@ians.be", "[SUNSOLVE] Error report", "From: $email\nName: $name\n\nFree text:\n\n".$details."\n\n--\n");
   } else {
     die("You must complete every field");
   }
   $content = new Template("./tpl/thanks.tpl");
 } else {
   $content = new Template("./tpl/notify.tpl");
 }
 $index->set("head", $head);
 $index->set("menu", $menu);
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();

?>
