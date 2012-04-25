<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

  $h = HTTP::getInstance();
  $h->parseUrl();
//  $h->sanitizeArray($_POST);
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

 if (!isset($lm->o_login) || !$lm->o_login) {
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 }
 
 if (isset($_GET['form']) && $_GET['form'] == 1) {
   if (!isset ($_POST['pl']) || empty($_POST['pl'])) {
     $content = new Template("./tpl/add_report.tpl");
     $content->set("error", "Patch level not selected.");
     $lm->o_login->fetchServers();
     $content->set("as", $lm->o_login->a_servers);
     goto screen;
   }
   $pl = new PLevel($_POST['pl']);
   if ($pl->fetchFromId()) {
     $content = new Template("./tpl/add_report.tpl");
     $content->set("error", "Patch level not found in database.");
     $lm->o_login->fetchServers();
     $content->set("as", $lm->o_login->a_servers);
     goto screen;
   }
   $pl->fetchServer();
   if ($lm->o_login->id != $pl->o_server->id_owner) {
     $content = new Template("./tpl/add_report.tpl");
     $content->set("error", "You don't own this patch level, you little hacker!");
     $lm->o_login->fetchServers();
     $content->set("as", $lm->o_login->a_servers);
     goto screen;
   }
   if (!isset($_POST['pd'])) {
     $content = new Template("./tpl/add_report.tpl");
     $content->set("error", "Patchdiag delay not specified.");
     $lm->o_login->fetchServers();
     $content->set("as", $lm->o_login->a_servers);
     goto screen;
   }
   if (!isset($_POST['f']) || empty($_POST['f'])) {
     $content = new Template("./tpl/add_report.tpl");
     $content->set("error", "Frequency not specified.");
     $lm->o_login->fetchServers();
     $content->set("as", $lm->o_login->a_servers);
     goto screen;
   }
   $pdiag_delay = $_POST['pd'];
   if (empty($pdiag_delay)) $pdiag_delay = "0";
   if (!preg_match('/[0-9]*/', $pdiag_delay)) {
     $content = new Template("./tpl/add_report.tpl");
     $content->set("error", "Incorrect patchdiag delay.");
     $lm->o_login->fetchServers();
     $content->set("as", $lm->o_login->a_servers);
     goto screen;
   }
   $frequency = $_POST['f'];
   $ur = new UReport();
   $ur->id_owner = $lm->o_login->id;
   $ur->id_plevel = $pl->id;
   $ur->lastrun = 0;
   switch($frequency) {
     case "1d":
       $frequency = 86400;
       break;
     case "1w":
       $frequency = 604800;
       break;
     case "1m":
       $frequency = 2678400;
       break;
     default:
       $content = new Template("./tpl/add_report.tpl");
       $content->set("error", "Incorrect frequency specified.");
       $lm->o_login->fetchServers();
       $content->set("as", $lm->o_login->a_servers);
       goto screen;
       break;
   }
   $ur->frequency = $frequency;
   $ur->pdiag_delay = $pdiag_delay;
   if (!$ur->fetchFromFields(array('id_owner', 'id_plevel', 'frequency', 'pdiag_delay'))) {
     $content = new Template("./tpl/add_report.tpl");
     $content->set("error", "You already created the same rapport.");
     $lm->o_login->fetchServers();
     $content->set("as", $lm->o_login->a_servers);
     goto screen;
   }
   $ur->insert();
   IrcMsg::add("[WWW] User added rapport to his account (".$lm->o_login->username.")", MSG_ADM);
   $content = new Template("./tpl/message.tpl");
   $content->set("msg", "Report added for server ".$pl->o_server."/$pl to run every $frequency seconds with a patchdiag delay of $pdiag_delay days.");
   $back = array('name' => 'Panel', 'href' => '/panel');
   $content->set('back', $back);

 } else {
   $lm->o_login->fetchServers();
   $content = new Template("./tpl/add_report.tpl");
   $content->set("as", $lm->o_login->a_servers);
 }

screen:
  $index->set("content", $content);
  if (isset($s)) $content->set("s", $s);
  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  echo $index->fetch();
?>
