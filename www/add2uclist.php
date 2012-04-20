<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $h = HTTP::getInstance();
 $h->parseUrl();
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

  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);

  if (isset($_GET['uid'])) {
   $ulist = new UCList($_GET['uid']);
   if ($ulist->fetchFromId() || $ulist->id_login != $lm->o_login->id) {
     $content = new Template("./tpl/error.tpl");
     $content->set('error', 'Specified user list not found or you are not owner');
     goto screen;
   }
   $ulist->fetchPatches(0);
  } else {
    $content = new Template("./tpl/error.tpl");
    $content->set('error', 'No user list specified');
    goto screen;
  }

 if (isset($_GET['form']) && $_GET['form'] == 1 &&
     isset($_GET['uid']) &&
     isset($_POST['plist']) && !empty($_POST['plist']) &&
     isset($_POST['format']) && !empty($_POST['format'])) {

   $plist = Patch::parseList($_POST['plist'], $_POST['format']);
   if (!$plist) HTTP::errWWW("Error in format submitted");

   $curr = 0;
   $msg = '';
   foreach($plist as $p) {
    if ($p->fetchFromId()) {
      $msg .= "<br/>$p not found,";
      continue;
    }
    if (!$ulist->isPatch($p)) {
      $ulist->addPatch($p);
      $msg .= "<br/>$p added";
    } else {
      $msg .= "<br/>$p was already there,";
    }
   }
   $content = new Template("./tpl/message.tpl");
   $content->set('msg', $msg.'<br/>Specified patches correctly added to user list');
   
 } else {
   $content = new Template("./tpl/add2uclist.tpl");
   if (isset($_GET['form']) && $_GET['form'] == 1) {
     $content->set('error', 'Please check specified fields, you didn\'t filled everything!');
   }
 }
screen:
  if (isset($ulist)) $content->set('ulist', $ulist);
  $index->set("content", $content);
  echo $index->fetch();
?>
