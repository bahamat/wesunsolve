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

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $head_add = "<script type=\"text/javascript\" src=\"/js/ax_main.js\"></script>";
 $head_add .= "<script type=\"text/javascript\" src=\"/js/ax_patch.js\"></script>";
 $head->set("head_add", $head_add);
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $index->set("head", $head);
 $index->set("foot", $foot);
 $index->set("menu", $menu);

 if (!isset($lm->o_login) || !$lm->o_login) {
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 }

 if (!isset($_GET['i']) || empty($_GET['i'])) {
   $content = new Template("./tpl/error.tpl");
   $error = "No id of list specified.";
   $content->set("error", $error);
   goto screen;
 }
 $s = new UCList();
 $s->id = $_GET['i'];
 if ($s->fetchFromId()) {
   $content = new Template("./tpl/error.tpl");
   $error = "List not found in database";
   $content->set("error", $error);
   goto screen;
 }

 if ($lm->o_login->id != $s->id_login) {
   $content = new Template("./tpl/error.tpl");
   $error = "You have no rights to view this list!";
   $content->set("error", $error);
   goto screen;
 }

 $s->fetchFromId();
 $s->fetchPatches(0);
 IrcMsg::add("[WWW] User ".$lm->o_login->username." requested download of ".count($s->a_patches)." readmes", MSG_ADM);

 $ctype = 'application/zip';
 $size = 0;
 $fn = 'wesunsolve-ulist-'.$s->id.'.zip';

 HTTP::piwikDownload($fn);

 $tmpfile = tempnam($config['tmppath'], 'ucl');
 $zip = new ZipArchive;
 $zip->open($tmpfile, ZipArchive::OVERWRITE);
 foreach($s->a_patches as $p) {
   if (!file_exists($p->readmePath())) {
     $zip->addFromString('MISSING-README.'.$p, '');
   } else {
     $zip->addFromString('README.'.$p, file_get_contents($p->readmePath()));
   }
 }
 $zip->close();

 header('Content-Type: '.$ctype);
 header("Content-Disposition: attachment; filename=\"$fn\"");
 header('Content-Transfer-Encoding: binary');
 header('Content-Length: '.(filesize($tmpfile)));
 header('Pragma: no-cache');
 readfile($tmpfile);
 unlink($tmpfile);
 exit(0);

screen:
  $index->set("content", $content);
  echo $index->fetch();
?>
